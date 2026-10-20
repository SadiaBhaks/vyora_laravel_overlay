<?php

namespace App\Http\Controllers;

use App\Services\Payments\StripeService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeService $stripe)
    {
        if (blank(config('vyora.stripe.webhook_secret'))) {
            return response('Webhook not configured.', 503);
        }

        try {
            $result = $stripe->handleWebhook($request->getContent(), $request->header('Stripe-Signature'));
        } catch (SignatureVerificationException|\UnexpectedValueException) {
            return response('Invalid signature.', 400);
        }

        return response($result, 200);
    }
}
