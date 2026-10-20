<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Collections, deals, coupons and CLEARLY-FLAGGED demonstration reviews/metrics.
 * Everything created here carries is_demo = true (reviews/orders) or the global "demo_data" setting,
 * and the storefront shows a demo-data notice while that setting is on.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::active()->with('tags', 'category')->get();
        $byTag = fn (string $tag) => $products->filter(fn ($p) => $p->tags->contains('slug', $tag))->pluck('id')->all();
        $byCat = fn (string $c) => $products->where('category.slug', $c)->pluck('id')->all();

        $collections = [
            ['Future Tech', 'future-tech', 'Tomorrow, available today.', 'Connected, efficient and a little bit ahead of the curve.', array_merge($byTag('futuristic'), $byTag('wireless')), true],
            ['Quiet Luxury', 'quiet-luxury', 'Understated. Well made.', 'Premium materials and restrained design, no logos required.', $byTag('premium'), true],
            ['Everyday Essentials', 'everyday-essentials', 'The pieces you reach for daily.', 'Practical, durable and easy to live with.', $byTag('practical'), true],
            ['Work & Create', 'work-and-create', 'Tools for focus.', 'Gear for desks, studios and long days of making things.', array_merge($byCat('electronics'), $byTag('ergonomic')), false],
            ['Weekend Mode', 'weekend-mode', 'Out of office.', 'Light bags, good sound and gear for getting outside.', array_merge($byTag('travel'), $byTag('sporty')), false],
            ['Smart Living', 'smart-living-collection', 'A home that listens.', 'Smart-home essentials that work without a hub.', array_merge($byCat('smart-living'), $byTag('smart-home')), false],
            ['Under-Budget Finds', 'under-budget-finds', 'Under $50.', 'Genuinely good products at gentle prices.', $products->filter(fn ($p) => (float) $p->price < 50)->pluck('id')->all(), true],
            ["Editor's Choice", 'editors-choice', 'Our team\'s shortlist.', 'A hand-picked set from the VYORA demo catalogue.', $products->where('is_featured', true)->pluck('id')->all(), true],
        ];
        foreach ($collections as $i => [$name, $slug, $tagline, $desc, $ids, $featured]) {
            $c = Collection::updateOrCreate(['slug' => $slug], ['name' => $name, 'tagline' => $tagline, 'description' => $desc, 'art_seed' => 'col-'.$slug, 'sort_order' => $i + 1, 'is_featured' => $featured, 'is_active' => true]);
            $sync = [];
            foreach (array_values(array_unique($ids)) as $pos => $id) {
                $sync[$id] = ['sort_order' => $pos];
            }
            $c->products()->sync($sync);
        }

        // Real, dated promotions that actually drive the price calculation (server-side).
        $promo = Promotion::updateOrCreate(['slug' => 'autumn-edit'], [
            'name' => 'The Autumn Edit', 'description' => '15% off selected fashion, accessories and home pieces.',
            'discount_percent' => 15, 'starts_at' => now()->startOfDay(), 'ends_at' => now()->addDays(14)->endOfDay(), 'is_active' => true,
        ]);
        $promo->products()->sync(collect($byCat('fashion'))->take(3)->merge(collect($byCat('accessories'))->take(2))->merge(collect($byCat('home'))->take(2))->all());
        $tech = Promotion::updateOrCreate(['slug' => 'tech-week'], [
            'name' => 'Tech Week', 'description' => '10% off selected electronics and smart-living products.',
            'discount_percent' => 10, 'starts_at' => now()->startOfDay(), 'ends_at' => now()->addDays(7)->endOfDay(), 'is_active' => true,
        ]);
        $tech->products()->sync(collect($byCat('electronics'))->take(3)->merge(collect($byCat('smart-living'))->take(2))->all());

        Coupon::updateOrCreate(['code' => 'WELCOME10'], ['description' => '10% off your first order over $40.', 'type' => 'percent', 'value' => 10, 'min_subtotal' => 40, 'is_active' => true, 'starts_at' => null, 'ends_at' => null]);
        Coupon::updateOrCreate(['code' => 'FREESHIP'], ['description' => null, 'type' => 'fixed', 'value' => 6, 'min_subtotal' => 30, 'is_active' => true]);

        // --- Demonstration reviews: flagged is_demo, written by clearly-labelled demo accounts. ---
        $names = ['Demo Reviewer A', 'Demo Reviewer B', 'Demo Reviewer C', 'Demo Reviewer D'];
        $reviewers = collect($names)->map(fn ($n, $i) => User::firstOrCreate(['email' => 'demo-reviewer-'.($i + 1).'@example.invalid'], ['name' => $n, 'password' => Hash::make(bin2hex(random_bytes(16)))]));
        $bodies = [
            5 => ['Great quality', 'Demo review text: well made and exactly as described in the listing.'],
            4 => ['Solid choice', 'Demo review text: does the job well, with small room for improvement.'],
            3 => ['Decent', 'Demo review text: fine for the price, though not exceptional.'],
        ];
        foreach ($products->take(36) as $pi => $product) {
            foreach ($reviewers->take(2 + $pi % 3) as $ri => $user) {
                $rating = [5, 4, 5, 3][($pi + $ri) % 4];
                Review::updateOrCreate(['product_id' => $product->id, 'user_id' => $user->id], [
                    'rating' => $rating, 'title' => $bodies[$rating][0], 'body' => $bodies[$rating][1],
                    'is_verified' => false, 'is_demo' => true, 'status' => 'approved',
                ]);
            }
        }

        // Illustrative popularity so Trending has an ordering on a fresh install (labelled via demo_data).
        foreach ($products as $i => $p) {
            $p->forceFill(['view_count' => 40 + (($i * 37) % 400), 'sales_count' => ($i * 13) % 60])->saveQuietly();
        }

        Setting::updateOrCreate(['key' => 'demo_data'], ['value' => '1']);
    }
}
