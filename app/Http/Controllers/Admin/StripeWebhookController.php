<?php

namespace App\Http\Controllers\Admin;

use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
// use Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        Log::info($payload);
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = "whsec_175c33ae2e5355210f3c4fd783f4c20c2729e7ccc9dc2701107cd5ab0ba2e42a";

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event type
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                Log::info('✅ Payment succeeded: ' . $event->data->object->id);
                break;

            case 'invoice.payment_failed':
                Log::warning('❌ Payment failed: ' . $event->data->object->id);
                break;

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                Log::info('🔁 Subscription update/delete: ' . $event->type);
                break;

            default:
                Log::info('Unhandled event type ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }
}
