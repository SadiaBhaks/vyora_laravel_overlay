<?php

namespace App\Services\Payments;

use App\Models\Order;

class PaymentGateway
{
    public static function stripeConfigured(): bool
    {
        return filled(config('vyora.stripe.secret'));
    }

    public static function demoEnabled(): bool
    {
        return (bool) config('vyora.demo_checkout');
    }

    /** 'stripe' when keys exist, 'demo' when explicitly enabled, otherwise null (checkout is blocked, never faked). */
    public static function provider(): ?string
    {
        if (self::stripeConfigured()) {
            return 'stripe';
        }

        return self::demoEnabled() ? 'demo' : null;
    }
}
