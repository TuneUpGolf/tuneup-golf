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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StripeConnectedAccount;
use App\Services\StripeWebhookService;
use App\DataTables\Admin\PlanDataTable;
use App\Models\Student;
use App\Models\StudentSubscription;
use Stripe\Subscription as StripeSubscription;

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
        // dd(tenant());
        if (Auth::user()->can('create-plan')) {
            if (Auth::user()->is_stripe_connected == 0) {
                return back()->with('failed', 'Stripe account not connected');
            }
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
                'max_users'     => 'required',
                'lesson_limit' => 'required|integer',
            ]);

            // dd($request->all());

            // $paymentTypes = UtilityFacades::getpaymenttypes();
            // if (!$paymentTypes) {
            //     return redirect()->route('plans.index')->with('failed', __('Please on at list one payment type.'));
            // }

            $currency = UtilityFacades::getsettings('currency') ?? 'usd';

            $instructorId = Auth::user()->type === Role::ROLE_INSTRUCTOR ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INSTRUCTOR ? tenant()->id : null;

            // if ($instructorId) {
            //     $exists = Plan::where('instructor_id', $instructorId)
            //         ->where('is_chat_enabled', $request->chat == '1' ? 1 : 0)
            //         ->where('is_feed_enabled', $request->feed == '1' ? 1 : 0)
            //         ->exists();

            //     if ($exists) {
            //         return redirect()->route('plans.myplan')->with('failed', __('You already have a plan with the same chat and feed settings.'));
            //     }
            // }

            $instructor = $instructorId ? User::find($instructorId) : null;

            $duration = strtolower($request->durationtype) == 'month' ? $request->duration : ($request->duration * 12);
            // $totalPrice = $request->price;
            // $intervalCount = (int) $duration;
            // if ($intervalCount <= 0) {
            //     $intervalCount = 1;
            // }
            // $perIntervalPrice = $totalPrice / $intervalCount;

            $stripeAccountId = $instructor->stripe_account_id ?? null;

            Stripe::setApiKey(config('services.stripe.secret'));

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
            ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

            // 2️⃣ Create a Recurring Price
            $price = Price::create([
                'unit_amount' => round($request->price * 100), // Stripe expects cents
                'currency' => $currency,
                'recurring' => [
                    // 'interval' =>  strtolower($request->durationtype), // "month" or "year"
                    'interval' =>  'month', // "month" or "year"
                ],
                'product' => $product->id,
            ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);


            // ---- 4️⃣ Create Webhook (only if teacher connected Stripe) ----
            // if ($stripeAccountId && isset(tenant()->domains->first()->domain)) {
            // Use the service you defined earlier
            // $webhookId = StripeWebhookService::ensureWebhookForConnectedAccount(
            //     $stripeAccountId,
            //     tenant()->domains->first()->domain,
            //     null
            // );
            // Log::info($webhookId);
            // Store webhook ID if new
            // if ($webhookId && empty($instructor->stripe_webhook_id)) {
            //     $instructor->update(['stripe_webhook_id' => $webhookId]);
            // }
            // }
            // Everything here runs in the CENTRAL database context
            $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);



            Plan::create([
                'name'            => $request->name,
                'price'           => $request->price,
                'duration'        => $request->duration,
                'durationtype'    => $request->durationtype,
                'tenant_id'       => $tenantId,
                'max_users'       => $request->max_users,
                'description'     => $request->description,
                'is_chat_enabled' => $request->chat == '1' ? 1 : 0,
                'is_feed_enabled' => $request->feed == '1' ? 1 : 0,
                'instructor_id'   => $instructorId,
                'stripe_product_id' => $product->id, // store Stripe IDs!
                'stripe_price_id'   => $price->id,
                'lesson_limit' => $request->lesson_limit,
            ]);



            return redirect()->route('plans.myplan')->with('success', __('Plan created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    private function saveCentralizedStripeData($stripeAccountId, $tenant_id)
    {
        tenancy()->central(function () use ($stripeAccountId, $tenant_id) {
            $exists = StripeConnectedAccount::where('stripe_account_id', $stripeAccountId)
                ->where('tenant_id', $tenant_id)
                ->exists();

            if (! $exists) {
                StripeConnectedAccount::create([
                    'tenant_id' => $tenant_id,
                    'stripe_account_id' => $stripeAccountId,
                ]);
            }
        });
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
                'lesson_limit' => 'required|integer',
            ]);

            $plan = Plan::findOrFail($id);

            $instructorId = Auth::user()->type === Role::ROLE_INSTRUCTOR ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INSTRUCTOR ? tenant()->id : null;

            // 🔹 Calculate price per interval
            // $duration = strtolower($request->durationtype) == 'month' ? $request->duration : ($request->duration * 12);
            // $totalPrice = $request->price;
            // $intervalCount = (int) $duration;
            // if ($intervalCount <= 0) {
            //     $intervalCount = 1;
            // }
            // $perIntervalPrice = $totalPrice / $intervalCount;




            $instructor = $instructorId ? User::find($instructorId) : null;
            $stripeAccountId = $instructor->stripe_account_id ?? null;

            // 🔹 Initialize Stripe
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                /**
                 * 1️⃣ Update or Create Stripe Product
                 */
                if ($plan->stripe_product_id) {
                    $product = Product::update(
                        $plan->stripe_product_id,
                        [
                            'name' => $request->name,
                            'description' => $request->description,
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                } else {
                    $product = Product::create(
                        [
                            'name' => $request->name,
                            'description' => $request->description,
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                    $plan->stripe_product_id = $product->id;
                }

                $price = Price::create(
                    [
                        'unit_amount' => round($request->price * 100),
                        'currency' => 'usd',
                        'recurring' => [
                            // 'interval' => strtolower($request->durationtype),
                            'interval' => 'month',

                        ],
                        'product' => $plan->stripe_product_id,
                    ],
                    $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                );

                $plan->stripe_price_id = $price->id;
            } catch (\Exception $e) {
                return redirect()->back()->with('failed', __('Stripe Error: ') . $e->getMessage());
            }

            /**
             * 3️⃣ Update Local Plan Data
             */
            $plan->name            = $request->name;
            $plan->price           = $request->price;
            $plan->duration        = $request->duration;
            $plan->durationtype    = $request->durationtype;
            $plan->max_users       = $request->max_users;
            $plan->description     = $request->description;
            $plan->is_chat_enabled = $request->chat == '1' ? 1 : 0;
            $plan->is_feed_enabled = $request->feed == '1' ? 1 : 0;
            $plan->tenant_id       = $tenantId;
            $plan->instructor_id   = $instructorId;
            $plan->lesson_limit    = $request->lesson_limit;

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

    public function cancelPlan($encrptedPlanid)
    {
        // Plan id
        $plan_id  = \Illuminate\Support\Facades\Crypt::decrypt($encrptedPlanid);

        // Student can only cancel at the moment
        if (!(auth('student')->user())) {
            return redirect()->back()->with('failed', 'Unauthorized');
        }

        // user id
        $user_id = auth('student')->user()->id;

        // Student Subscription
        $student_subscription = StudentSubscription::where('plan_id', $plan_id)->where('student_id', $user_id)->latest()->first();

        // dd($student_subscription);
        // Subscription Check
        if (!$student_subscription) {
            Log::error("Plan id: " . $plan_id . " User id: " . $user_id . " subscription not found");
            return redirect()->back()->with('failed', 'Something went wrong');
        }

        // 🔹 Initialize Stripe for the connected account
        Stripe::setApiKey(config('services.stripe.secret')); // your platform secret key

        // tenant_id likely corresponds to the connected account ID
        $instructor_id = User::find($student_subscription->instructor_id);
        // dd($instructor_id, $student_subscription->instructor_id, );
        $connectedAccountId = $instructor_id->stripe_account_id;

        try {
            $stripeSubscription = StripeSubscription::retrieve(
                $student_subscription->stripe_subscription_id,
                ['stripe_account' => $connectedAccountId]
            );

            // Cancel immediately (no waiting for end of period)
            $stripeSubscription->cancel(
                ['invoice_now' => true, 'prorate' => false],
                ['stripe_account' => $connectedAccountId]
            );
        } catch (\Exception $stripeError) {
            Log::error("Stripe cancellation failed for connected account {$connectedAccountId}: " . $stripeError->getMessage());
            return redirect()->back()->with('failed', 'Unable to cancel subscription on Stripe.');
        }

        // 🔹 Update your local database
        $student_subscription->update([
            'status' => 'cancelled',
        ]);

        Student::find($user_id)->update(['plan_id' => null]);

        return redirect()->back()->with('success', 'Subscription cancelled successfully.');
    }
}
