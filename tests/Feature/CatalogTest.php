<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Services\ReviewService;
use App\Services\SearchService;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_public_pages_render(): void
    {
        $v = $this->makeProduct(['name' => 'Quiet Kettle']);
        foreach (['/shop', '/categories', '/new-arrivals', '/deals', '/trending', '/collections', '/match', '/cart', '/contact', '/about', '/faq', '/shipping', '/returns', '/privacy', '/terms', '/sitemap.xml', '/robots.txt', '/product/'.$v->product->slug, '/search?q=kettle'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/product/does-not-exist')->assertNotFound();
    }

    public function test_search_finds_by_name_and_escapes_wildcards(): void
    {
        $this->makeProduct(['name' => 'Quiet Kettle']);
        $this->makeProduct(['name' => 'Loud Speaker']);
        $s = app(SearchService::class);
        $this->assertSame(['Quiet Kettle'], $s->query('kettle')->pluck('name')->all());
        $this->assertCount(0, $s->query('%')->get());
        $this->assertCount(0, $s->query('_')->get());
    }

    public function test_listing_filters_and_sorts(): void
    {
        $this->makeProduct(['name' => 'Cheap Thing', 'price' => 10]);
        $this->makeProduct(['name' => 'Pricey Thing', 'price' => 90]);
        Livewire::test(\App\Livewire\ProductListing::class)
            ->set('max', '50')->assertSee('Cheap Thing')->assertDontSee('Pricey Thing')
            ->set('max', '')->set('sort', 'price_desc')->assertSeeInOrder(['Pricey Thing', 'Cheap Thing']);
    }

    public function test_wishlist_persists_for_logged_in_user(): void
    {
        $v = $this->makeProduct();
        $user = $this->makeUser();
        $this->actingAs($user)->post(route('wishlist.toggle', $v->product))->assertOk();
        $this->assertDatabaseHas('wishlist_items', ['user_id' => $user->id, 'product_id' => $v->product_id]);
        $this->actingAs($user)->post(route('wishlist.toggle', $v->product))->assertOk();
        $this->assertDatabaseMissing('wishlist_items', ['user_id' => $user->id, 'product_id' => $v->product_id]);
    }

    public function test_review_requires_a_paid_shipped_purchase(): void
    {
        $v = $this->makeProduct();
        $user = $this->makeUser();
        $svc = app(ReviewService::class);
        $this->assertNull($svc->eligibleOrder($user, $v->product));

        $order = $this->placeOrderFor($user, $v);
        $this->assertNull($svc->eligibleOrder($user, $v->product));           // unpaid
        $this->actingAs($user)->post(route('product.reviews.store', $v->product), ['rating' => 5, 'body' => 'Looks great, would buy again.'])->assertForbidden();

        app(\App\Services\OrderService::class)->markPaid($order, null, 'demo');
        $this->assertNull($svc->eligibleOrder($user, $v->product));           // paid but not shipped
        app(\App\Services\OrderService::class)->advance($order->fresh(), 'shipped');
        $this->assertNotNull($svc->eligibleOrder($user, $v->product));

        $this->actingAs($user)->post(route('product.reviews.store', $v->product), ['rating' => 5, 'body' => 'Looks great, would buy again.'])->assertRedirect();
        $review = Review::first();
        $this->assertTrue($review->is_verified);
        $this->assertSame('pending', $review->status);                         // moderated before it counts
        $this->assertSame(0, $v->product->fresh()->rating_count);
    }
}
