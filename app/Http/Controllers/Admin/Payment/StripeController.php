<?php

namespace App\Http\Controllers\Admin\Payment;

use Exception;
use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Plan;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Student;
use App\Facades\Utility;
use Stripe\StripeClient;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use App\Services\ChatService;
use App\Facades\UtilityFacades;
use App\Models\StudentSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class StripeController extends Controller
{
    protected $chatService;
    protected $utility;
    public function __construct(ChatService $chatService,  Utility $utility)
    {
        $this->utility = $utility;
        $this->chatService = $chatService;
    }

    public function stripe()
    {
        $view =  view('payment.PaymentStripe');
        return ['html' => $view->render()];
    }

    public function connectStripe(Request $request)
    {
        try {
            $request->validate([
                'instructor_id' => 'required'
            ]);

            $instructor = User::find($request->instructor_id);

            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($instructor->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type' => 'standard',
                    'email' => $instructor->email,
                ]);
                $instructor->stripe_account_id = $account->id;
                $instructor->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account' => $instructor->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['instructor_id' => $instructor->id,]),
                'return_url' => route('stripe-redirect-create', ['account_id' => $instructor->stripe_account_id, 'instructor_id' => $instructor->id]),
                'type' => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function refreshAccountLink(Request $request)
    {
        try {
            $request->validate([
                'instructor_id' => 'required',
            ]);
            $instructor = User::find($request->instructor_id);
            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($instructor->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type' => 'standard',
                    'email' => $instructor->email,
                ]);
                $instructor->stripe_account_id = $account->id;
                $instructor->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account' => $instructor->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['instructor_id' => $instructor->id]),
                'return_url' => route('stripe-redirect-create', ['account_id' => $instructor->stripe_account_id, 'instructor_id' => $instructor->id]),
                'type' => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function redirectFromCreate(Request $request)
    {
        try {
            $request->validate([
                'account_id' => 'required',
                'instructor_id' => 'required'
            ]);
            $instructor = User::where('id', $request->get('instructor_id'))->first();
            if (!empty($instructor->stripe_account_id)) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
                $account = $stripeClient->accounts->retrieve($instructor->stripe_account_id);

                if ($account && $account->id) {
                    $isVerified = false;

                    if (isset($account->charges_enabled) && $account->charges_enabled) {
                        $isVerified = true;
                    }

                    if (isset($account->payouts_enabled) && $account->payouts_enabled) {
                        $isVerified = true;
                    }

                    // Save the account ID and verification status
                    $instructor->stripe_account_id = $instructor->stripe_account_id;
                    $instructor->is_stripe_connected = $isVerified;
                }
                $instructor->save();
            }
            return redirect()->route('home')->with('success', __('Stripe Connect Integrated Successfully'));
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function stripePostPending(Request $request)
    {
        // dd('$stop');
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if ($authUser->type == 'Admin') {
            $plan   = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData =  tenancy()->central(function ($tenant) use ($plan, $request) {
                $couponId = '0';
                $price = $plan->price;
                $couponCode = null;
                $discountValue = null;
                $coupons = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
                if ($coupons) {
                    $couponCode     = $coupons->code;
                    $usedCoupun     = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        $resData['errors'] = __('This coupon code has expired.');
                    } else {
                        $discount       = $coupons->discount;
                        $discount_type  = $coupons->discount_type;
                        $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                        $price          = $price - $discountValue;
                        if ($price < 0) {
                            $price      = $plan->price;
                        }
                        $couponId       = $coupons->id;
                    }
                }
                $data = Order::create([
                    'plan_id'           => $plan->id,
                    'user_id'           => $tenant->id,
                    'amount'            => $price,
                    'discount_amount'   => $discountValue,
                    'coupon_code'       => $couponCode,
                    'status'            => 0,
                ]);

                $resData['total_price'] = $price;
                $resData['plan_id']     = $plan->id;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
            return $resData;
        } else {
            if ($authUser->type == 'Student') {
                $authUserId = 0;
                $studentId = $authUser->id;
            } else {
                $authUserId = $authUser->id;
                $studentId = null;
            }

            $studentId    = $authUser->type == 'Student' ? $authUserId : null;
            $plan           =  Plan::find($planID);

            if ($plan->is_chat_enabled && is_null($authUser->chat_user_id)) {
                $this->utility->ensureChatUserId($authUser, $this->chatService);
            }

            if ($plan->is_chat_enabled && is_null($authUser->chat_user_id)) {
                return response()->json([
                    'error' => 'Chat user ID is required to proceed with the payment.'
                ]);
            }

            $couponId       = '0';
            $price          = $plan->price;
            $couponCode     = null;
            $discountValue  = null;
            $coupons        = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();

            if ($coupons) {
                $couponCode     = $coupons->code;
                $usedCoupun     = $coupons->used_coupon();
                if ($coupons->limit == $usedCoupun) {
                    $resData['errors'] = __('This coupon code has expired.');
                } else {
                    $discount       = $coupons->discount;
                    $discount_type  = $coupons->discount_type;
                    $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                    $price          = $price - $discountValue;
                    if ($price < 0) {
                        $price      = $plan->price;
                    }
                    $couponId       = $coupons->id;
                }
            }


            $data = Order::create([
                'plan_id'           => $plan->id,
                'user_id'           => $authUserId,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
                'student_id'     => $studentId,
            ]);

            $resData['total_price'] = $price;
            $resData['plan_id']     = $plan->id;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            // dd($resData);
            return $resData;
        }
    }


    public function stripeSession(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->type != 'Admin') {
            Stripe::setApiKey(UtilityFacades::getsettings('stripe_secret'));
            $currency       = UtilityFacades::getsettings('currency');
        } else {
            $currency       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $stripe_secret  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('stripe_secret');
            });
            Stripe::setApiKey($stripe_secret);
        }

        if (!empty($request->createCheckoutSession)) {
            if (Auth::user()->type == 'Admin') {
                $planDetails   = tenancy()->central(function ($tenant) use ($request) {
                    return Plan::find($request->plan_id);
                });
            } else {
                $planDetails   =  Plan::find($request->plan_id);
            }
            try {
                // $checkout_session = \Stripe\Checkout\Session::create([
                //     'payment_method_types'  => ['card'],
                //     'line_items'            => [[
                //         'price_data'    => [
                //             'currency'      => $currency,
                //             'product_data'  => [
                //                 'name'      => $planDetails->name,
                //                 'metadata'  => [
                //                     'plan_id'           => $request->plan_id,
                //                     'domainrequest_id'  => $request->domainrequest_id
                //                 ]
                //             ],
                //             'unit_amount'   => $request->amount * 100,
                //         ],
                //         'quantity'      => 1,
                //     ]],
                //     'mode'          => 'payment',
                //     'success_url'   => route('stripe.success.pay', Crypt::encrypt([
                //         'coupon'    => $request->coupon,
                //         'plan_id'   => $planDetails->id,
                //         'price'     => $request->amount,
                //         'user_id'   => Auth::user()->id,
                //         'order_id'  => $request->order_id,
                //         'type'      => 'stripe'
                //     ])),
                //     'cancel_url'    => route('stripe.cancel.pay', Crypt::encrypt([
                //         'coupon'    => $request->coupon,
                //         'plan_id'   => $planDetails->id,
                //         'price'     => $request->amount,
                //         'user_id'   => Auth::user()->id,
                //         'order_id'  => $request->order_id,
                //         'type'      => 'stripe'
                //     ])),
                // ]);

                // dd(UtilityFacades::getsettings('stripe_secret'), $planDetails->instructor->stripe_account_id, $planDetails->stripe_price_id);

                $checkout_session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'line_items' => [[
                        'price' => $planDetails->stripe_price_id,
                        'quantity' => 1,
                    ]],
                    'customer_email' => Auth::user()->email,
                    // 'metadata' => [
                    //     'plan_id' => $planDetails->id,
                    //     'student_id' => Auth::user()->id,
                    //     'tenant_id' => tenant()->id,
                    //     'instructor_id' => $planDetails->instructor_id,
                    // ],
                    'success_url' => route('stripe.success.pay', Crypt::encrypt([
                        'coupon' => $request->coupon,
                        'plan_id' => $planDetails->id,
                        'price' => $request->amount,
                        'user_id' => Auth::user()->id,
                        'order_id' => $request->order_id,
                        'type' => 'stripe',
                    ])) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('stripe.cancel.pay', Crypt::encrypt([
                        'coupon' => $request->coupon,
                        'plan_id' => $planDetails->id,
                        'price' => $request->amount,
                        'user_id' => Auth::user()->id,
                        'order_id' => $request->order_id,
                        'type' => 'stripe',
                    ])),
                ], [
                    // ✅ options go here (second argument)
                    'stripe_account' => $planDetails->instructor->stripe_account_id,
                ]);
            } catch (Exception $e) {
                $api_error  = $e->getMessage();
            }
            if (empty($api_error) && $checkout_session) {
                $response   = [
                    'status'    => 1,
                    'message'   => 'Checkout session created successfully.',
                    'sessionId' => $checkout_session->id
                ];
            } else {
                $response = [
                    'status'    => 0,
                    'error'     => [
                        'message' => 'Checkout session creation failed. ' . $api_error
                    ]
                ];
            }
        }
        return response()->json($response);
    }

    function paymentPending(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $user   = User::find(Auth::user()->id);
            $order  = tenancy()->central(function ($tenant) use ($request, $user) {
                $data['plan_details']   = Plan::find($request->plan_id);
                $user                   = User::where('email', $user->email)->first();
                $data['order']  = Order::create([
                    'plan_id'   => $request->plan_id,
                    'user_id'   => $user->id,
                    'amount'    => $data['plan_details']->price,
                    'status'    => 0,
                ]);
                return $data;
            });
            $response = array(
                'status'            => 0,
                'order_id'          => $order['order']->id,
                'amount'            => $order['order']->amount,
                'plan_name'         => $order['plan_details']->name,
                'currency'          => $request->currency,
                'currency_symbol'   => $request->currency_symbol,
            );
            echo json_encode($response);
            die;
        } else {
            $user = User::find(Auth::user()->id); {
                $planDetails    = Plan::find($request->plan_id);
                $user           = User::where('email', $user->email)->first();
                $data           = Order::create([
                    'plan_id'   => $request->plan_id,
                    'user_id'   => Auth::user()->id,
                    'amount'    => $planDetails->price,
                    'status'    => 0,
                ]);
            }
            $response = array(
                'status'            => 0,
                'order_id'          => $data->id,
                'amount'            => $planDetails->price,
                'plan_name'         => $planDetails->name,
                'currency'          => $request->currency,
                'currency_symbol'   => $request->currency_symbol,
            );
            echo json_encode($response);
            die;
        }
    }

    function paymentCancel($data)
    {

        $data = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 2;
                $datas->payment_type    = 'stripe';
                $datas->update();
            });
        } else {
            $datas                  = Order::find($data['order_id']);
            $datas->status          = 2;
            $datas->payment_type    = 'stripe';
            $datas->update();
        }
        return redirect()->route('plans.index')->with('errors', __('Payment canceled.'));
    }

    function paymentSuccess($data)
    {
        // Session id Stripe
        $session_id = request('session_id');

        // ✅ Remove session id as other data is encrypted
        if (strpos($data, '?') !== false) {
            $data = explode('?', $data)[0];
        }

        // Then decrypt encrypted data
        $data = Crypt::decrypt($data);

        // Login User
        $user = Auth::user();

        // if admin case
        if ($user->type == 'Admin') {
            // Get user instance
            $user = User::find($user->id);

            // centralized database  
            $order = tenancy()->central(function ($tenant) use ($data) {
                // Order updated
                $datas = Order::find($data['order_id']);
                $datas->status = 1;
                $datas->payment_type = 'stripe';
                $datas->update();

                //  coupon if any
                $coupons = Coupon::find($data['coupon']);

                // tenant user (if any)
                $user = User::find($tenant->id);

                // if coupon 
                if (!empty($coupons)) {
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $datas->id;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }

                // fetching plan
                $plan = Plan::find($data['plan_id']);
                $user->plan_id  = $plan->id;
                if ($plan->durationtype == 'Month' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                } else {
                    $user->plan_expired_date = null;
                }
                $user->save();
            });
        } else {

            // Find student
            $user = $user->type == 'Student' ? Student::find($user->id) : User::find($user->id);

            // order status update
            $datas = Order::find($data['order_id']);
            $datas->status = 1;
            $datas->payment_type = 'stripe';
            $datas->update();

            // Coupons if any
            $coupons    = Coupon::find($data['coupon']);
            if (!empty($coupons)) {
                $userCoupon         = new UserCoupon();
                $userCoupon->user   = $user->id;
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $datas->id;
                $userCoupon->save();
                $usedCoupun         = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }

            // find plan
            $plan = Plan::find($data['plan_id']);

            // User plan_id/subscription update
            $user->plan_id = $plan->id;

            if ($plan->durationtype == 'Month' && $plan->id != '1') {
                $planExpiredDate = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                $user->plan_expired_date = $planExpiredDate;
            } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                $planExpiredDate = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                $user->plan_expired_date = $planExpiredDate;
            } else {
                $user->plan_expired_date = null;
            }


            if ($plan->is_chat_enabled) {
                $this->chatService->updateUser($user->chat_user_id, 'plan_expired_date', $planExpiredDate, $user->email);
                $user->chat_status = true;
            }


            $user->save();
        }

        /**
         * 🆕 NEW STRIPE LOGIC STARTS HERE
         * --------------------------------
         * After successful payment, find the checkout session and
         * update the subscription to auto-cancel after plan duration.
         */
        // \Log::info($session_id);
        try {
            if ($session_id) {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                // $session = \Stripe\Checkout\Session::retrieve($session_id);
                $session = \Stripe\Checkout\Session::retrieve($session_id, [
                    'stripe_account' => $plan->instructor->stripe_account_id // Use the connected account ID
                ]);

                if (!empty($session->subscription)) {
                    // \Log::info("2");

                    $subscription_id = $session->subscription;
                    $customer_id = $session->customer ?? null;

                    // 🆕 Create Student Subscription record
                    StudentSubscription::create([
                        'student_id' => $user->id,
                        'plan_id' => $plan->id,
                        'instructor_id' => $plan->instructor_id ?? null,
                        'tenant_id' => tenant()->id,
                        'stripe_customer_id' => $customer_id,
                        'stripe_subscription_id' => $subscription_id,
                        'status' => 'active',
                    ]);

                    // Auto-cancel logic
                    if (strtolower($plan->durationtype) === 'month') {
                        $cancelAt = now()->addMonths($plan->duration)->timestamp;
                    } elseif (strtolower($plan->durationtype) === 'day') {
                        $cancelAt = now()->addDays($plan->duration)->timestamp;
                    } elseif (strtolower($plan->durationtype) === 'year') {
                        $cancelAt = now()->addYears($plan->duration)->timestamp;
                    } else {
                        // fallback (optional) - e.g., default to months or handle error
                        $cancelAt = now()->addMonths($plan->duration)->timestamp;
                    }

                    // \Log::info($cancelAt);

                    \Stripe\Subscription::update($subscription_id, [
                        'cancel_at' => $cancelAt,
                    ], [
                        'stripe_account' => $plan->instructor->stripe_account_id // Use the connected account ID
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Stripe cancel_at update failed: ' . $e->getMessage());
        }
        /** 🆕 END STRIPE LOGIC */

        if ($user->type == 'Student') {
            return redirect()->route('home', ['view' => 'subscriptions'])->with('status', __('Payment successfully!'));
        } else {
            return redirect()->route('plans.index')->with('status', __('Payment successfully!'));
        }
    }
}
