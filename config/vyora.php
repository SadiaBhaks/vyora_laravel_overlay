<?php

return [
    'currency' => 'USD',
    'currency_symbol' => '$',

    // Fractional tax rate applied to the discounted subtotal (0.08 = 8%).
    'tax_rate' => (float) env('VYORA_TAX_RATE', 0.08),

    'free_shipping_threshold' => (float) env('VYORA_FREE_SHIPPING_THRESHOLD', 100),

    'shipping_methods' => [
        'standard' => ['label' => 'Standard', 'price' => 6.00, 'days_min' => 4, 'days_max' => 7, 'free_over_threshold' => true],
        'express' => ['label' => 'Express', 'price' => 16.00, 'days_min' => 1, 'days_max' => 3, 'free_over_threshold' => false],
    ],

    // Demo checkout never charges anything. Enabled by default outside production.
    'demo_checkout' => (bool) env('VYORA_DEMO_CHECKOUT', env('APP_ENV', 'production') !== 'production'),

    'require_email_verification' => (bool) env('VYORA_REQUIRE_EMAIL_VERIFICATION', false),

    'max_cart_quantity' => 10,
    'recently_viewed_limit' => 12,
    'low_stock_threshold' => 5,
    'contact_email' => env('VYORA_CONTACT_EMAIL', 'hello@vyora.test'),

    // Footer social links. Defaults are the platforms' public home pages; set your own handles in .env.
    'social' => [
        'instagram' => env('VYORA_INSTAGRAM_URL', 'https://www.instagram.com/'),
        'youtube' => env('VYORA_YOUTUBE_URL', 'https://www.youtube.com/'),
        'x' => env('VYORA_X_URL', 'https://x.com/'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    // Defaults for store policies; editable in Admin > Settings (stored in the DB).
    'settings_defaults' => [
        'store_name' => 'VYORA',
        'returns_days' => 30,
        'return_shipping' => 'Free return labels for faulty items; otherwise the customer covers return postage.',
        'processing_days' => 2,
        'shipping_regions' => 'We currently ship within the United States.',
        'support_hours' => 'Mon–Fri, 9:00–17:00 (UTC)',
        'demo_data' => 0, // set to 1 by the demo seeder so the storefront labels seeded figures honestly
    ],
];
