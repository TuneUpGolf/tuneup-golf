<?php

namespace App\Traits;

use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\Slots;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Stripe\Account;

trait PurchaseTrait
{

    function sendSlotNotification(Slots $slot, string $notificationType, ?string $studentMessageTemplate = null, ?string $instructorMessageTemplate = null, ?Student $specificStudent = null)
    {
        $slot->load(['student', 'lesson']);
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot->date_time)->toDayDateTimeString();

        if ($specificStudent) {
            $personalizedMessage = str_replace(
                [':name'],
                [$slot->lesson->user->name],
                $studentMessageTemplate
            );

            if (isset($specificStudent->pushToken->token)) {
                SendPushNotification::dispatch($specificStudent->pushToken->token, $notificationType, $personalizedMessage);
            }

            $studentPhone = Str::of($specificStudent->dial_code)->append($specificStudent->phone)->value();
            SendSMS::dispatch($studentPhone, $personalizedMessage);
        } else {

            $instructor = $slot->lesson->user;

            // Format messages for instructor
            $messageInstructor = __($instructorMessageTemplate, [
                'date' => $date,
            ]);

            // Notify all students who booked the slot
            if (isset($studentMessageTemplate))
                foreach ($slot->student as $student) {
                    $messageStudent = __($studentMessageTemplate, [
                        'instructor' => $instructor?->name,
                        'lesson' => $slot->lesson->lesson_name,
                        'date' => $date
                    ]);

                    // Send push notification to students
                    if (!empty($student->pushToken?->token) && !$student->pivot->isFriend) {
                        SendPushNotification::dispatch($student->pushToken->token, $notificationType, $messageStudent);
                    }

                    // Send SMS to students (if they have valid phone numbers)
                    if (!empty($student->dial_code) && !empty($student->phone) && !$student->pivot->isFriend) {
                        $userPhone = Str::of($student->dial_code)->append($student->phone)->value();
                        $userPhone = str_replace(['(', ')'], '', $userPhone);
                        SendSMS::dispatch($userPhone, $messageStudent);
                    }
                }

            if (isset($instructorMessageTemplate)) {
                // Send push notification to instructor
                if (!empty($instructor->pushToken?->token)) {
                    SendPushNotification::dispatch($instructor->pushToken->token, $notificationType, $messageInstructor);
                }

                // Send SMS to instructor (if they have a valid phone number)
                if (!empty($instructor->dial_code) && !empty($instructor->phone)) {
                    $instructorPhone = Str::of($instructor->dial_code)->append($instructor->phone)->value();
                    $instructorPhone = str_replace(['(', ')'], '', $instructorPhone);
                    SendSMS::dispatch($instructorPhone, $messageInstructor);
                }
            }
        }
    }

    function createSessionForPayment(Purchase $purchase, $redirect, $slot_id = null)
    {
        try {
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $instructor = $purchase?->instructor;
            $isInstructorUSA = $instructor?->country == 'United States';

            Stripe::setApiKey(config('services.stripe.secret'));

            $accountId = $instructor?->stripe_account_id;
            $account = Account::retrieve($accountId);

            $instructorCurrency = $account?->default_currency ?? 'usd';
            $convertedAmount = $purchase?->total_amount * 100;
            if ($instructorCurrency !== $application_currency) {
                $exchangeRates = \Stripe\ExchangeRate::retrieve($instructorCurrency);
                $conversionRate = $exchangeRates['rates'][$application_currency] ?? 1;
                $convertedAmount = round($convertedAmount / $conversionRate);
            }




            $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);

            $success_params = [
                'purchase_id' => $purchase->id,
                'redirect'    => $redirect,
                'user_id'     => Auth::user()->id,
            ];

            $cancel_params = [
                'purchase_id' => $purchase->id,
                'redirect'    => $redirect,
                'user_id'     => Auth::user()->id,
            ];

            if ($slot_id) {
                $success_params['slot_id'] = $slot_id;
            }


            $sessionData = [
                'line_items' => [[
                    'price_data' => [
                        'currency' => $instructorCurrency,
                        'product_data' => [
                            'name' => "$purchase->id " . "$purchase->instructor_id" . "$purchase->lesson_id",
                        ],
                        'unit_amount' => $convertedAmount,
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    'transfer_data' => ['destination' => $accountId],
                ],
                'mode' => 'payment',
                'customer' => Auth::user()?->stripe_cus_id ?? null,
                'success_url' => route(
                    $purchase->lesson->is_package_lesson ||
                        $purchase->lesson->type == Lesson::LESSON_TYPE_ONLINE ? 'home' : 'purchase-success',
                    $success_params
                ),
                'cancel_url' => route('purchase-cancel', $cancel_params),
            ];

            if (!$isInstructorUSA) {
                $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            }
            // dd($instructor?->active_status, !empty($account->id), $account->charges_enabled, !empty($account->capabilities['card_payments']),$account->capabilities['card_payments'] === 'active');

            // Want to test on local comment next if and uncomment session below
            // if (
            //     $instructor?->active_status &&
            //     !empty($account->id) &&
            //     // $account->charges_enabled &&
            //     !empty($account->capabilities['card_payments']) 
            //     // $account->capabilities['card_payments'] === 'active'
            // ) {
            if (
                $instructor?->active_status &&
                !empty($account->id) &&
                $account->charges_enabled &&
                !empty($account->capabilities['card_payments']) &&
                $account->capabilities['card_payments'] === 'active'
            ) {
                $session = Session::create($sessionData);
            } else {
                throw new Exception('There is a problem with booking lessons for this instructor. Kindly contact admin.');
            }
            // $session = Session::create($sessionData);

            if (!empty($session?->id)) {
                $purchase->session_id = $session->id;
                $purchase->save();
            }

            return $session;
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }


    public function createSessionForPaymentNew($lesson_id)
    {
        try {
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $lesson = Lesson::find($lesson_id);
            $instructor = $lesson?->user;
            $isInstructorUSA = $instructor?->country == 'United States';

            Stripe::setApiKey(config('services.stripe.secret'));

            $accountId = $instructor?->stripe_account_id;
            $account = Account::retrieve($accountId);
            $instructorCurrency = $account?->default_currency ?? 'usd';
            $convertedAmount = $lesson?->lesson_price * 100;

            // Convert currency if needed
            if ($instructorCurrency !== $application_currency) {
                $exchangeRates = \Stripe\ExchangeRate::retrieve($instructorCurrency);
                $conversionRate = $exchangeRates['rates'][$application_currency] ?? 1;
                $convertedAmount = round($convertedAmount / $conversionRate);
            }

            $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);
            $success_url = route('purchase.checkout', [
                'lesson_id' => $lesson_id,
                'user_id' => Auth::id(),
            ]) . '&session_id={CHECKOUT_SESSION_ID}';
            // Create session first (without success/cancel URL)
            $session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => $instructorCurrency,
                        'product_data' => [
                            'name' => "{$instructor->id}-{$lesson->id}",
                        ],
                        'unit_amount' => $convertedAmount,
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    'transfer_data' => ['destination' => $accountId],
                ],
                'mode' => 'payment',
                'customer' => Auth::user()?->stripe_cus_id ?? null,
                'success_url' => $success_url,
                'cancel_url' => route('purchase-cancel', [
                    'lesson_id' => $lesson_id,
                    'user_id' => Auth::id(),
                ]),
            ]);

            return $session;
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }


    public function confirmPurchaseWithRedirect(Request $request, bool $returnJson = false)
    {
        try {
            $request->validate([
                'purchase_id'  => 'required',
            ]);

            $purchase = Purchase::find($request?->purchase_id);
            if ($purchase && Auth::user()->can('create-purchases') && !!$purchase->instructor->is_stripe_connected) {
                $session = $this->createSessionForPayment($purchase, true);
                if (empty($session->url)) {
                    throw new \Exception('Failed to generate payment link');
                }
                // dd($session);


                return $returnJson
                    ? response()->json(['payment_url' => $session->url], 200)
                    : redirect($session->url);
            }

            throw new \Exception('Failed to generate payment link');
        } catch (\Exception $e) {

            \Log::error('Payment link generation failed: ' . $e->getMessage());

            if ($returnJson) {
                return response()->json(['error' => 'Failed to generate payment link, please try again later.'], 500);
            }

            return redirect()->back()->withErrors(['failed' => 'Failed to generate payment link, please try again later.' . $e->getMessage()]);
        }
    }
}
