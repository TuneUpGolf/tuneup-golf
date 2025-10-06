<?php

namespace App\Http\Controllers\Admin;

use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTables\Admin\PlanDataTable;

class PlanController extends Controller
{
    public function index(PlanDataTable $dataTable)
    {
        return redirect()->route(
            Auth::user()->type == 'Student' ? 'home'
                : 'slot.manage'
        );
        if (Auth::user()->can('manage-plan')) {
            if (Auth::user()->type == 'Admin') {
                $plans  = tenancy()->central(function ($tenant) {
                    return Plan::all();
                });
                $user   = tenancy()->central(function ($tenant) {
                    return User::find($tenant->id);
                });
                return view('admin.plans.index', compact('user', 'plans'));
            } else {
                $plans  =  Plan::all();
                $user   = User::find(Auth::user()->id);
                return view('admin.plans.index', compact('user', 'plans'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function myPlan(PlanDataTable $dataTable)
    {
        if (Auth::user()->can('manage-plan')) {
            if (Auth::user()->type == 'Instructor') {
                return $dataTable->render('admin.plans.my-plans');
            } else {
                $plans  = Plan::where('tenant_id', null)->get();
                $user   = User::where('tenant_id', tenant('id'))->where('type', 'Admin')->first();
                return view('admin.plans.index', compact('user', 'plans'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function createMyPlan()
    {
        if (Auth::user()->can('create-plan')) {
            return view('admin.plans.create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (Auth::user()->can('create-plan')) {
            request()->validate([
                'name'          => 'required|unique:plans,name|max:50',
                'price'         => 'required',
                'duration'      => 'required',
                'durationtype'  => 'required',
                'max_users'     => 'required'
            ]);

            // dd($request->all());

            $paymentTypes = UtilityFacades::getpaymenttypes();
            if (!$paymentTypes) {
                return redirect()->route('plans.index')->with('errors', __('Please on at list one payment type.'));
            }

            $instructorId = Auth::user()->type === Role::ROLE_INSTRUCTOR ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INSTRUCTOR ? tenant()->id : null;

            if ($instructorId) {
                $exists = Plan::where('instructor_id', $instructorId)
                    ->where('is_chat_enabled', $request->chat == '1' ? 1 : 0)
                    ->where('is_feed_enabled', $request->feed == '1' ? 1 : 0)
                    ->exists();

                if ($exists) {
                    return redirect()->route('plans.myplan')->with('failed', __('You already have a plan with the same chat and feed settings.'));
                }
            }

            $totalPrice = $request->price;
            $intervalCount = (int) $request->duration;
            if ($intervalCount <= 0) {
                $intervalCount = 1;
            }
            $perIntervalPrice = $totalPrice / $intervalCount;

            Stripe::setApiKey(config('services.stripe.secret'));

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // 2️⃣ Create a Recurring Price
            $price = Price::create([
                'unit_amount' => round($perIntervalPrice * 100), // Stripe expects cents
                'currency' => 'usd',
                'recurring' => [
                    'interval' =>  strtolower($request->durationtype), // "month" or "year"
                ],
                'product' => $product->id,
            ]);

            Plan::create([
                'name'            => $request->name,
                'price'           => $request->price,
                'duration'        => $request->duration,
                'durationtype'    => $request->durationtype,
                'tenant_id'       => $tenantId,
                'max_users'       => $request->max_users,
                'description'     => $_POST['description'],
                'is_chat_enabled' => $request->chat == '1' ? 1 : 0,
                'is_feed_enabled' => $request->feed == '1' ? 1 : 0,
                'instructor_id'   => $instructorId,
                'stripe_product_id' => $product->id, // store Stripe IDs!
                'stripe_price_id'   => $price->id,
            ]);
            return redirect()->route('plans.myplan')->with('success', __('Plan created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-plan')) {
            $plan   = Plan::find($id);
            return view('admin.plans.edit', compact('plan'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-plan')) {
            request()->validate([
                'name'          => 'required|max:50|unique:plans,name,' . $id,
                'price'         => 'required',
                'duration'      => 'required',
                'durationtype'  => 'required',
                'max_users'     => 'required',
            ]);

            $plan = Plan::findOrFail($id);

            $instructorId = Auth::user()->type === Role::ROLE_INSTRUCTOR ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INSTRUCTOR ? tenant()->id : null;

            // 🔹 Calculate price per interval
            $totalPrice = $request->price;
            $intervalCount = (int) $request->duration;
            if ($intervalCount <= 0) {
                $intervalCount = 1;
            }
            $perIntervalPrice = $totalPrice / $intervalCount;

            // 🔹 Initialize Stripe
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            try {
                /**
                 * 1️⃣ Update or Create Stripe Product
                 */
                if ($plan->stripe_product_id) {
                    // Update existing product in Stripe
                    $product = \Stripe\Product::update($plan->stripe_product_id, [
                        'name' => $request->name,
                        'description' => $request->description,
                    ]);
                } else {
                    // Create a new product in Stripe if missing
                    $product = \Stripe\Product::create([
                        'name' => $request->name,
                        'description' => $request->description,
                    ]);
                    $plan->stripe_product_id = $product->id;
                }

                /**
                 * 2️⃣ Create New Stripe Price (since prices cannot be edited)
                 */
                $price = \Stripe\Price::create([
                    'unit_amount' => round($perIntervalPrice * 100), // Stripe expects cents
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => strtolower($request->durationtype), // month or year
                    ],
                    'product' => $plan->stripe_product_id,
                ]);

                // ✅ Save the new price ID in the plan
                $plan->stripe_price_id = $price->id;
            } catch (\Exception $e) {
                return redirect()->back()->with('failed', __('Stripe Error: ') . $e->getMessage());
            }

            /**
             * 3️⃣ Update Local Plan Data
             */
            $plan->name            = $request->name;
            $plan->price           = $totalPrice;
            $plan->duration        = $request->duration;
            $plan->durationtype    = $request->durationtype;
            $plan->max_users       = $request->max_users;
            $plan->description     = $request->description;
            $plan->is_chat_enabled = $request->chat == '1' ? 1 : 0;
            $plan->is_feed_enabled = $request->feed == '1' ? 1 : 0;
            $plan->tenant_id       = $tenantId;
            $plan->instructor_id   = $instructorId;
            $plan->save();

            return redirect()->route('plans.myplan')->with('success', __('Plan updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (Auth::user()->can('delete-plan')) {
            $plan = Plan::find($id);
            if ($plan->id != 1) {
                $planExistInOrder = Order::where('plan_id', $plan->id)->first();
                if (empty($planExistInOrder)) {
                    $plan->delete();
                    return redirect()->route('plans.myplan')->with('success', __('Plan deleted successfully.'));
                } else {
                    return redirect()->back()->with('failed', __('Can not delete this plan because its purchased by users.'));
                }
            } else {
                return redirect()->back()->with('failed', __('Can not delete this plan because its free plan.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function planStatus(Request $request, $id)
    {
        $plan   = Plan::find($id);
        $planStatus  = ($request->value == "true") ? 1 : 0;
        if ($plan) {
            $plan->active_status = $planStatus;
            $plan->save();
        }
        return response()->json([
            'is_success'    => true,
            'message'       => __('Plan status changed successfully.')
        ]);
    }

    public function payment($code)
    {
        $plan_id  = \Illuminate\Support\Facades\Crypt::decrypt($code);
        if (Auth::user()->type == 'Admin') {
            $plan           = tenancy()->central(function ($tenant) use ($plan_id) {
                return Plan::find($plan_id);
            });
            $paymentTypes   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getpaymenttypes();
            });
            $adminPaymentSetting    = UtilityFacades::getadminplansetting();
        } else {
            $plan                   = Plan::find($plan_id);
            $paymentTypes           = UtilityFacades::getpaymenttypes();
            $adminPaymentSetting    = UtilityFacades::getplansetting();
        }
        if ($plan) {
            return view('admin.plans.payment', compact('plan', 'adminPaymentSetting', 'paymentTypes'));
        } else {
            return redirect()->back()->with('errors', __('Plan deleted successfully.'));
        }
    }
}
