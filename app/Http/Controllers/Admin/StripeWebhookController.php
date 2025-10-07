<?php

namespace App\Http\Controllers\Admin;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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
        \Log::info('🔔 Stripe Webhook received!');
        // \Log::info('Headers: ' . json_encode($request->headers->all()));

         $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->all();
        $accountId = $payload['account'] ?? null;
        $eventType = $payload['type'] ?? 'unknown';

        // ✅ Find which instructor this webhook belongs to
        $instructor = null;
        if ($accountId) {
            $instructor = User::where('stripe_account_id', $accountId)->first();
        }

        \Log::info("🎯 Event Type: {$eventType}");
        \Log::info("👤 Connected Account: {$accountId}");
        \Log::info("Instructor: " . ($instructor ? $instructor->name : 'Unknown'));

        $webhook_id = $instructor->stripe_webhook_id;

            try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhook_id
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info($event);

        // Handle events you care about
        switch ($eventType) {
            case 'checkout.session.completed':
                // handle payment or subscription completion
                break;

            case 'invoice.payment_succeeded':
                // handle successful recurring payment
                Log::info('invoice payment');
                break;

            case 'customer.subscription.deleted':
                // handle subscription cancellation
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
