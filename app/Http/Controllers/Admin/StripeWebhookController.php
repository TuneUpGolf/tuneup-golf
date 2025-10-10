<?php

namespace App\Http\Controllers\Admin;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\StudentSubscription;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\StripeConnectedAccount;
use App\Models\StudentSubscriptionDetail;

// use Log;

class StripeWebhookController extends Controller
{
    // public function handleWebhook(Request $request)
    // {
    //     $payload = $request->getContent();

    //     // Log everything for now
    //     // Log::info($payload['account']);
    //     Log::info('🔔 Stripe Webhook received!');
    //     Log::info('Headers:', $request->headers->all());
    //     Log::info('Body:', $request->all());
    //     return response()->json(['status' => 'success']);


    //     try {
    //         $event = Webhook::constructEvent(
    //             $payload,
    //             $sigHeader,
    //             $endpointSecret
    //         );
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Handle the event type
    //     switch ($event->type) {
    //         case 'invoice.payment_succeeded':
    //             Log::info('✅ Payment succeeded: ' . $event->data->object->id);
    //             break;

    //         case 'invoice.payment_failed':
    //             Log::warning('❌ Payment failed: ' . $event->data->object->id);
    //             break;

    //         case 'customer.subscription.updated':
    //         case 'customer.subscription.deleted':
    //             Log::info('🔁 Subscription update/delete: ' . $event->type);
    //             break;

    //         default:
    //             Log::info('Unhandled event type ' . $event->type);
    //     }

    //     return response()->json(['status' => 'success']);
    // }

    public function handleWebhook(Request $request)
    {
        Log::info('🔔 Stripe Webhook received!');
        // \Log::info('Headers: ' . json_encode($request->headers->all()));

        $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->all();
        $accountId = $payload['account'] ?? null;
        $eventType = $payload['type'] ?? 'unknown';

        // ✅ Find which instructor this webhook belongs to
        // $instructor = null;
        // if ($accountId) {
        //     $instructor = User::where('stripe_account_id', $accountId)->first();
        // }
        $stripe_account_id = StripeConnectedAccount::where('stripe_account_id', $request->account)->first();
        $tenant = Tenant::find($stripe_account_id->tenant_id); // or however you store tenant IDs

        tenancy()->initialize($tenant); // 🔁 switch context to this tenant

        // Now you're “inside” the tenant’s DB
        $users = User::where('stripe_account_id', $stripe_account_id->stripe_account_id)->first();

        // Do your work in this tenant's DB...
        // Log::info($users);

        tenancy()->end();

        Log::info("🎯 Event Type: {$eventType}");
        // \Log::info($payload);
        // \Log::info("👤 Connected Account: {$accountId}");
        // \Log::info("Instructor: " . ($instructor ? $instructor->name : 'Unknown'));

        // $webhook_id = $instructor->stripe_webhook_id;

        //     try {
        //     $event = Webhook::constructEvent(
        //         $payload,
        //         $sigHeader,
        //         'whsec_175c33ae2e5355210f3c4fd783f4c20c2729e7ccc9dc2701107cd5ab0ba2e42a'
        //     );
        // } catch (\Exception $e) {
        //     return response()->json(['error' => 'Invalid signature'], 400);
        // }

        // Log::info($event);

        // Handle events you care about
        switch ($eventType) {
            // case 'checkout.session.completed':
            //     $session = $payload['data']['object'];
            //     $subscriptionId = $session['subscription'] ?? null;
            //     $customerId = $session['customer'] ?? null;

            //     // metadata from checkout session
            //     $planId = $session['metadata']['plan_id'] ?? null;
            //     $studentId = $session['metadata']['student_id'] ?? null;
            //     $tenantId = $session['metadata']['tenant_id'] ?? null;
            //     $instructorId = $session['metadata']['instructor_id'] ?? null;

            //     // store in tenant's DB
            //     tenancy()->initialize(Tenant::find($tenantId));

            //     StudentSubscription::create([
            //         'student_id'        => $studentId,
            //         'plan_id'           => $planId,
            //         'stripe_customer_id' => $customerId,
            //         'stripe_subscription_id' => $subscriptionId,
            //         'status'            => 'active',
            //     ]);

            //     tenancy()->end();
            //     break;

            case 'invoice.payment_succeeded':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

                tenancy()->initialize($tenant);

                $student_subscription = StudentSubscription::where('stripe_subscription_id', $subscriptionId)
                    ->update(['status' => 'active']);

                if ($student_subscription) {
                    $student_subscription->update(['status' => 'active']);

                    // Create a subscription detail record
                    StudentSubscriptionDetail::create([
                        'student_subscription_id' => $student_subscription->id,
                        'invoice_id' => $invoiceId,
                        'payment_intent_id' => $paymentIntentId,
                    ]);

                    Log::info('Invoice payment succeeded', [
                        'subscription_id' => $subscriptionId,
                        'invoice_id' => $invoiceId,
                        'payment_intent_id' => $paymentIntentId,
                        'tenant_id' => $tenant->id,
                    ]);
                } else {
                    Log::warning('Student subscription not found for successful payment', [
                        'subscription_id' => $subscriptionId,
                        'invoice_id' => $invoiceId,
                    ]);
                }

                tenancy()->end();
                Log::info('invoice payment');
                break;

            case 'invoice.payment_failed':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

                tenancy()->initialize($tenant);

                $subscription = StudentSubscription::where('stripe_subscription_id', $subscriptionId)->first();
                if ($subscription && $subscription->status !== 'past_due') {
                    $subscription->update(['status' => 'past_due']);
                    Log::info('Subscription updated to past_due due to payment failure', [
                        'subscription_id' => $subscriptionId,
                        'invoice_id' => $invoiceId,
                        'payment_intent_id' => $paymentIntentId,
                        'tenant_id' => $tenant->id,
                    ]);


                    // Optional: Notify the user or take other actions
                    // Example: Send email to the user to update their payment method
                    // $user = User::find($subscription->student_id);
                    // \Mail::to($user->email)->send(new PaymentFailedNotification($subscription));
                } else {
                    Log::info('Subscription already past_due or not found', [
                        'subscription_id' => $subscriptionId,
                        'invoice_id' => $invoiceId,
                        'payment_intent_id' => $paymentIntentId,
                        'tenant_id' => $tenant->id,
                    ]);
                }
                tenancy()->end();

                break;

            case 'customer.subscription.deleted':
                $subscriptionId = $payload['data']['object']['id'];
                tenancy()->initialize($tenant);

                StudentSubscription::where('stripe_subscription_id', $subscriptionId)
                    ->update(['status' => 'canceled']);

                tenancy()->end();
                break;
        }

        return response()->json(['status' => 'success']);
    }

    public function handleWebhooktest(Request $request)
    {
        Log::info('🔔 Stripe Webhook received!', [
            'event_type' => $request->input('type', 'unknown'),
            'account_id' => $request->input('account', 'N/A'),
        ]);

        $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->getContent(); // Use raw payload for signature verification
        $eventType = $request->input('type', 'unknown');
        $accountId = $request->input('account', null);

        // Verify webhook signature
        try {
            $webhookSecret = env('STRIPE_WEBHOOK_SECRET'); // Replace with your webhook secret
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret,
                null, // Tolerance for timestamp verification (optional)
                ['stripe_account' => $accountId] // Specify connected account
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook Signature Verification Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }

        // Find tenant and initialize tenancy
        try {
            $stripeAccount = StripeConnectedAccount::where('stripe_account_id', $accountId)->first();
            if (!$stripeAccount) {
                Log::error('No StripeConnectedAccount found for account_id: ' . $accountId);
                return response()->json(['error' => 'Invalid connected account'], 400);
            }

            $tenant = Tenant::find($stripeAccount->tenant_id);
            if (!$tenant) {
                Log::error('No Tenant found for tenant_id: ' . $stripeAccount->tenant_id);
                return response()->json(['error' => 'Invalid tenant'], 400);
            }

            tenancy()->initialize($tenant);
            Log::info('Tenancy initialized for tenant_id: ' . $tenant->id);

            // Handle events
            switch ($eventType) {
                case 'invoice.payment_succeeded':
                    $subscriptionId = $event->data->object->subscription;
                    $invoiceId = $event->data->object->id;

                    $subscription = StudentSubscription::where('stripe_subscription_id', $subscriptionId)->first();
                    if ($subscription && $subscription->status !== 'active') {
                        $subscription->update(['status' => 'active']);
                        Log::info('Subscription updated to active', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        Log::info('Subscription already active or not found', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                    break;

                case 'customer.subscription.deleted':
                    $subscriptionId = $event->data->object->id;
                    $subscription = StudentSubscription::where('stripe_subscription_id', $subscriptionId)->first();
                    if ($subscription && $subscription->status !== 'canceled') {
                        $subscription->update(['status' => 'canceled']);
                        Log::info('Subscription canceled', [
                            'subscription_id' => $subscriptionId,
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        Log::info('Subscription already canceled or not found', [
                            'subscription_id' => $subscriptionId,
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                    break;

                default:
                    Log::info('Unhandled webhook event: ' . $eventType);
                    break;
            }

            tenancy()->end();
            Log::info('Tenancy ended for tenant_id: ' . $tenant->id);
        } catch (\Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage(), [
                'account_id' => $accountId,
                'event_type' => $eventType,
                'trace' => $e->getTraceAsString(),
            ]);
            tenancy()->end();
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }

        return response()->json(['status' => 'success']);
    }
}
