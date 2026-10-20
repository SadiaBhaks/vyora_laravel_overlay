<?php

// Demo catalogue. Brands and products are fictional. Prices/specs are illustrative demo data, not real offers.
// Row: [name, brand, price, compare_at|null, short description, [tag slugs], [colors name=>hex], [sizes], [attribute pairs], flags(new,featured)]
$C = fn (array $c) => $c;
$black = ['Black' => '#17181a']; $white = ['White' => '#f1efe9']; $sand = ['Sand' => '#d8c7a6']; $sage = ['Sage' => '#9aa88a']; $navy = ['Navy' => '#1f2a44']; $clay = ['Clay' => '#b5694a'];
$apparel = ['S', 'M', 'L', 'XL'];

return [
    'fashion' => [
        ['Aerolite Shell Jacket', 'Northline', 148, 189, 'Packable water-resistant shell with taped seams and a hidden hood.', ['minimal', 'waterproof', 'travel', 'sporty'], $black + $sage, $apparel, ['Fit' => 'Regular', 'Material' => 'Recycled nylon'], 'nf'],
        ['Everyday Heavyweight Tee', 'Common Thread', 38, null, '260gsm organic cotton tee with a relaxed boxy cut.', ['minimal', 'sustainable', 'practical'], $white + $black + $sand, $apparel, ['Fit' => 'Relaxed', 'Material' => 'Organic cotton'], ''],
        ['Merino Crew Sweater', 'Common Thread', 96, 120, 'Fine-gauge merino crew that layers cleanly and resists odor.', ['premium', 'minimal', 'sustainable'], $navy + $sand, $apparel, ['Fit' => 'Regular', 'Material' => 'Merino wool'], ''],
        ['Studio Tapered Trousers', 'Northline', 112, null, 'Stretch-twill trousers with a clean taper and hidden zip pocket.', ['minimal', 'practical', 'travel'], $black + $navy, $apparel, ['Fit' => 'Tapered', 'Material' => 'Stretch twill'], 'n'],
        ['Court Leather Sneaker', 'Atelier Quay', 129, 158, 'Full-grain leather low-top with a cushioned recycled-rubber sole.', ['premium', 'minimal', 'sustainable'], $white + $black, ['40', '41', '42', '43', '44', '45'], ['Fit' => 'True to size', 'Material' => 'Leather'], 'f'],
        ['Trail Runner Knit', 'Vantra', 119, null, 'Breathable knit upper with a grippy lug sole for mixed terrain.', ['sporty', 'practical', 'waterproof'], $clay + $black, ['40', '41', '42', '43', '44', '45'], ['Fit' => 'True to size', 'Material' => 'Engineered knit'], 'n'],
    ],
    'electronics' => [
        ['Halo Wireless Headphones', 'Soniq', 189, 229, 'Over-ear Bluetooth headphones with active noise cancelling and 40-hour battery.', ['wireless', 'long-battery', 'premium', 'portable'], $black + $sand, [], ['Battery' => '40 hours', 'Connectivity' => 'Bluetooth 5.3'], 'f'],
        ['Pulse Buds Mini', 'Soniq', 79, null, 'Compact true-wireless earbuds with a pocketable case.', ['wireless', 'portable', 'sporty', 'waterproof'], $white + $black, [], ['Battery' => '8h + 24h case', 'Connectivity' => 'Bluetooth 5.3'], 'n'],
        ['Orbit 65W GaN Charger', 'Voltix', 49, null, 'Three-port 65W GaN charger small enough for a jacket pocket.', ['portable', 'travel', 'practical', 'futuristic'], $black + $white, [], ['Output' => '65W', 'Ports' => '2×USB-C, 1×USB-A'], ''],
        ['Slate 10" Reading Tablet', 'Lumen Labs', 229, 259, 'Paper-like 10" display tablet built for reading and notes.', ['long-battery', 'portable', 'minimal'], $black, [], ['Battery' => '3 weeks', 'Display' => '10" e-ink'], ''],
        ['Arc Mechanical Keyboard', 'Keyform', 139, null, 'Low-profile 75% wireless keyboard with hot-swappable switches.', ['wireless', 'ergonomic', 'premium', 'futuristic'], $white + $black, [], ['Layout' => '75%', 'Connectivity' => 'Bluetooth / USB-C'], 'n'],
        ['Drift Portable Speaker', 'Soniq', 99, 119, 'Water-resistant 360° speaker with 20-hour battery and a carry loop.', ['wireless', 'portable', 'waterproof', 'long-battery'], $black + $clay, [], ['Battery' => '20 hours', 'Rating' => 'IP67'], ''],
    ],
    'accessories' => [
        ['Daily Carry Backpack 20L', 'Northline', 109, 135, 'Water-resistant 20L pack with a padded laptop sleeve and quick-access pocket.', ['practical', 'travel', 'waterproof', 'minimal'], $black + $navy, [], ['Capacity' => '20L', 'Laptop' => 'Up to 16"'], 'f'],
        ['Slim Bifold Wallet', 'Atelier Quay', 59, null, 'Vegetable-tanned leather wallet that holds eight cards without bulk.', ['premium', 'minimal'], $black + $clay, [], ['Material' => 'Leather', 'Cards' => '8'], ''],
        ['Field Watch 38mm', 'Meridian', 199, 240, 'Sapphire-crystal quartz watch with a legible dial and 100m water resistance.', ['premium', 'minimal', 'waterproof'], $black + $navy, [], ['Case' => '38mm', 'Water resistance' => '100m'], ''],
        ['Merino Beanie', 'Common Thread', 34, null, 'Ribbed merino beanie, soft against skin and warm in wet weather.', ['sustainable', 'practical'], $black + $sand + $sage, [], ['Material' => 'Merino wool'], ''],
        ['Polarized Aviator Sunglasses', 'Meridian', 89, null, 'Lightweight polarized lenses with a flexible titanium-blend frame.', ['premium', 'sporty'], $black + $sand, [], ['Lens' => 'Polarized', 'UV' => 'UV400'], 'n'],
        ['Tech Organizer Pouch', 'Northline', 29, null, 'Zip pouch with elastic loops for cables, drives and adapters.', ['practical', 'travel', 'portable'], $black + $sage, [], ['Material' => 'Recycled polyester'], ''],
    ],
    'beauty' => [
        ['Daily Mineral SPF 50', 'Solenne', 28, null, 'Lightweight mineral sunscreen with no white cast.', ['practical', 'sustainable'], [], [], ['Skin type' => 'All', 'Size' => '50ml'], ''],
        ['Barrier Repair Moisturizer', 'Solenne', 36, 42, 'Fragrance-free ceramide moisturizer for dry and sensitive skin.', ['practical', 'minimal'], [], [], ['Skin type' => 'Dry', 'Size' => '50ml'], ''],
        ['Vitamin C Brightening Serum', 'Aura Lab', 44, null, 'Stable 15% vitamin C serum in a light, fast-absorbing base.', ['premium'], [], [], ['Skin type' => 'Normal', 'Size' => '30ml'], 'n'],
        ['Clay Pore Cleanser', 'Aura Lab', 24, null, 'Gentle kaolin clay cleanser that clears without stripping.', ['practical'], [], [], ['Skin type' => 'Oily', 'Size' => '120ml'], ''],
        ['Hydra Lip Balm Trio', 'Solenne', 18, null, 'Three tinted-neutral balms in recyclable tubes.', ['sustainable', 'travel', 'portable'], [], [], ['Size' => '3 × 4g'], ''],
        ['Sonic Cleansing Brush', 'Aura Lab', 69, 84, 'Waterproof silicone cleansing brush with two intensities.', ['waterproof', 'portable', 'futuristic'], $white + $sage, [], ['Battery' => '30 days', 'Rating' => 'IPX7'], ''],
    ],
    'home' => [
        ['Stoneware Pour-Over Set', 'Hearth & Co', 64, null, 'Hand-glazed stoneware dripper and server for slow mornings.', ['minimal', 'premium', 'sustainable'], $white + $sand, [], ['Capacity' => '600ml'], 'f'],
        ['Linen Duvet Cover', 'Hearth & Co', 148, 180, 'Stonewashed European linen that softens with every wash.', ['premium', 'sustainable'], $sand + $sage + $white, ['Double', 'Queen', 'King'], ['Material' => 'Linen'], ''],
        ['Ceramic Table Lamp', 'Lumen Labs', 119, null, 'Sculptural ceramic base with a warm linen shade.', ['minimal', 'premium'], $white + $clay, [], ['Bulb' => 'E27, warm white'], ''],
        ['Cast Iron Skillet 26cm', 'Hearth & Co', 74, null, 'Pre-seasoned cast iron skillet for sear, bake and oven-to-table.', ['practical', 'sustainable'], $black, [], ['Diameter' => '26cm'], ''],
        ['Woven Storage Basket Set', 'Common Thread', 52, null, 'Set of three handwoven baskets for tidy shelves.', ['sustainable', 'practical', 'minimal'], $sand + $black, [], ['Set' => '3 pieces'], 'n'],
        ['Soy Candle — Cedar & Fig', 'Hearth & Co', 26, null, 'Hand-poured soy wax candle with a 45-hour burn time.', ['sustainable', 'minimal'], [], [], ['Burn time' => '45 hours'], ''],
    ],
    'fitness' => [
        ['Pro Grip Yoga Mat 5mm', 'Vantra', 68, 80, 'Dense natural-rubber mat with a non-slip, easy-wipe surface.', ['sporty', 'sustainable', 'practical'], $sage + $black, [], ['Thickness' => '5mm'], ''],
        ['Adjustable Dumbbell Pair', 'Vantra', 249, 299, 'Quick-select dumbbells from 2 to 24kg per hand.', ['sporty', 'practical', 'futuristic'], $black, [], ['Range' => '2–24kg'], 'f'],
        ['Insulated Steel Bottle 750ml', 'Northline', 32, null, 'Double-wall steel bottle that keeps drinks cold for 24 hours.', ['sporty', 'travel', 'sustainable', 'portable'], $black + $sage + $white, [], ['Capacity' => '750ml'], ''],
        ['Resistance Band Set', 'Vantra', 29, null, 'Five fabric-wrapped bands from light to extra heavy.', ['sporty', 'portable', 'travel'], $black, [], ['Bands' => '5'], ''],
        ['Pulse Fitness Tracker', 'Lumen Labs', 89, null, 'Slim tracker with heart-rate, sleep and 14-day battery.', ['wireless', 'long-battery', 'sporty', 'waterproof'], $black + $sand, [], ['Battery' => '14 days', 'Rating' => '5 ATM'], 'n'],
        ['Foam Roller Pro', 'Vantra', 36, null, 'Textured high-density roller for post-session recovery.', ['sporty', 'ergonomic', 'portable'], $black, [], ['Length' => '33cm'], ''],
    ],
    'lifestyle' => [
        ['Weekender Duffel 35L', 'Northline', 139, 165, 'Carry-on-sized duffel with a shoe compartment and wet pocket.', ['travel', 'practical', 'waterproof'], $black + $sage, [], ['Capacity' => '35L'], ''],
        ['Compact Travel Pillow', 'Atelier Quay', 34, null, 'Memory-foam neck pillow that packs to the size of a grapefruit.', ['travel', 'ergonomic', 'portable'], $navy + $black, [], ['Packed size' => 'Small'], ''],
        ['Notebook Set — Dot Grid', 'Common Thread', 22, null, 'Two lay-flat dot-grid notebooks in recycled paper.', ['sustainable', 'minimal'], $black + $sand, [], ['Pages' => '2 × 160'], ''],
        ['Pour-Over Kettle', 'Hearth & Co', 79, null, 'Gooseneck kettle with variable temperature and hold.', ['premium', 'practical'], $black + $white, [], ['Capacity' => '0.9L'], ''],
        ['Picnic Throw Blanket', 'Common Thread', 58, null, 'Water-resistant-backed throw for parks and beaches.', ['travel', 'sustainable', 'portable'], $sage + $clay, [], ['Size' => '150 × 180cm'], ''],
        ['Instant Film Camera', 'Meridian', 119, 139, 'Point-and-shoot instant camera with a built-in flash.', ['futuristic', 'portable', 'premium'], $white + $black, [], ['Film' => 'Instant'], 'n'],
    ],
    'smart-living' => [
        ['Beacon Smart Speaker', 'Lumen Labs', 89, null, 'Compact smart speaker with far-field mics and room-filling sound.', ['smart-home', 'wireless', 'futuristic'], $black + $white, [], ['Assistant' => 'Built-in', 'Connectivity' => 'Wi-Fi / Bluetooth'], 'f'],
        ['Glow Smart Bulb 4-Pack', 'Lumen Labs', 39, 49, 'Tunable white-to-colour smart bulbs, no hub required.', ['smart-home', 'wireless', 'practical'], [], [], ['Pack' => '4 bulbs', 'Base' => 'E27'], ''],
        ['Aura Air Purifier', 'Solenne', 179, 219, 'Quiet HEPA purifier for rooms up to 35 m² with a live air-quality ring.', ['smart-home', 'futuristic', 'practical'], $white + $black, [], ['Coverage' => '35 m²'], ''],
        ['Lock Pro Smart Deadbolt', 'Keyform', 199, null, 'Keypad smart deadbolt with app control and auto-lock.', ['smart-home', 'wireless', 'futuristic'], $black, [], ['Power' => '4 × AA'], ''],
        ['Pulse Smart Plug 2-Pack', 'Lumen Labs', 24, null, 'Wi-Fi smart plugs with scheduling and energy monitoring.', ['smart-home', 'wireless', 'practical', 'eco-friendly'], $white, [], ['Pack' => '2 plugs'], 'n'],
        ['Sensor Hub Climate Monitor', 'Solenne', 59, null, 'Tracks temperature, humidity and CO₂ with a clear e-ink display.', ['smart-home', 'long-battery', 'eco-friendly', 'minimal'], $white + $black, [], ['Battery' => '12 months'], ''],
    ],
];
