<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

trait PurchaseTrait
{

    function createSessionForPayment(Purchase $purchase, $redirect, $slot_id = null)
    {

        try {
            $application_fee_percentage = Setting::where('key', 'application_fee_percentage')->first()->value;
            if (empty($application_fee_percentage))
                $application_fee_percentage = 10;
            Stripe::setApiKey(config('services.stripe.secret'));
            $success_params = array(
                'purchase_id'   => $purchase->id,
                'redirect'      => $redirect,
                'user_id'       => Auth::user()->id,
            );
            if (isset($slot_id)) {
                $success_params['slot_id'] = $slot_id;
            }
            $purchase->load('instructor');
            $session = Session::create(
                [
                    'line_items'            => [[
                        'price_data'    => [
                            'currency'      => config('services.stripe.currency'),
                            'product_data'  => [
                                'name'      => "$purchase->id " . "$purchase->instructor_id" . "$purchase->lesson_id",
                            ],
                            'unit_amount'   => $purchase->total_amount * 100,
                        ],
                        'quantity'      => 1,
                    ]],
                    'payment_intent_data' => [
                        'application_fee_amount' => ($application_fee_percentage * $purchase->total_amount),
                        'transfer_data' => ['destination' => $purchase?->instructor?->stripe_account_id],
                    ],
                    'mode' => 'payment',
                    'customer' => Auth::user()?->stripe_cus_id ?? null,
                    'success_url' => route('purchase-success',  $success_params),
                    'cancel_url' => route('purchase-cancel'),
                ]
            );

            if (isset($session?->id)) {
                $purchase->session_id = $session?->id;
                $purchase->save();
            }
            return $session;
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        };
    }

    public function confirmPurchaseWithRedirect(Request $request)
    {
        $request->validate([
            'purchase_id'  => 'required',
        ]);

        $purchase = Purchase::find($request?->purchase_id);
        if ($purchase && Auth::user()->can('create-purchases')) {
            $session = $this->createSessionForPayment($purchase, true);
            return redirect($session->url);
        } else {
            return new Error("Purchase not found");
        }
    }
}
