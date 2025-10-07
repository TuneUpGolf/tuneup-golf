<?php

namespace App\Services;

use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeWebhookService
{
    /**
     * Create (or ensure) a webhook exists for a connected Stripe account.
     *
     * @param  string  $stripeAccountId  The teacher's connected Stripe account ID (acct_xxx)
     * @param  string  $tenantDomain     The tenant/subdomain domain (e.g. tenant1.tuneup-golf.test)
     * @param  string|null  $existingWebhookId  The existing webhook ID (we_xxx), if any
     * @return string|null  The webhook ID, or null on failure
     */
    public static function ensureWebhookForConnectedAccount(string $stripeAccountId, string $tenantDomain, ?string $existingWebhookId = null): ?string
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $webhookUrl = "https://{$tenantDomain}/stripe/webhook";

        try {
            // 1️⃣ If webhook already exists, verify it still exists in Stripe
            if ($existingWebhookId) {
                $existing = $stripe->webhookEndpoints->retrieve(
                    $existingWebhookId,
                    [],
                    ['stripe_account' => $stripeAccountId]
                );

                if ($existing && $existing->url === $webhookUrl) {
                    // Already valid, nothing to do
                    return $existingWebhookId;
                }
            }

            // 2️⃣ Otherwise, create a new one
            $webhook = $stripe->webhookEndpoints->create(
                [
                    'url' => $webhookUrl,
                    'enabled_events' => [
                        'invoice.payment_succeeded',
                        'customer.subscription.created',
                        'customer.subscription.updated',
                        'customer.subscription.deleted',
                        'checkout.session.completed',
                    ],
                ],
                [
                    'stripe_account' => $stripeAccountId,
                ]
            );

            Log::info("Created Stripe webhook for account {$stripeAccountId} at {$webhookUrl}");

            return $webhook->id;
        } catch (\Exception $e) {
            Log::error("Failed to create Stripe webhook for {$stripeAccountId}: " . $e->getMessage());
            return null;
        }
    }
}
