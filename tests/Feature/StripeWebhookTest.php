<?php

namespace Tests\Feature;

use App\Models\StripeEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    private string $secret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['vyora.stripe.webhook_secret' => $this->secret]);
    }

    private function sign(string $payload, ?string $secret = null): array
    {
        $t = time();

        return ['Stripe-Signature' => "t=$t,v1=".hash_hmac('sha256', "$t.$payload", $secret ?? $this->secret)];
    }

    private function event(string $id, string $number, string $type = 'checkout.session.completed', string $payStatus = 'paid'): string
    {
        return json_encode(['id' => $id, 'object' => 'event', 'type' => $type, 'data' => ['object' => ['id' => 'cs_test_1', 'object' => 'checkout.session', 'client_reference_id' => $number, 'payment_status' => $payStatus, 'payment_intent' => 'pi_test_1', 'metadata' => ['order_number' => $number]]]]);
    }

    public function test_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $order = $this->placeOrderFor($this->makeUser(), $this->makeProduct(), 1, 'stripe');
        $payload = $this->event('evt_1', $order->number);
        $this->call('POST', '/stripe/webhook', [], [], [], $this->transformHeadersToServerVars($this->sign($payload, 'wrong')), $payload)->assertStatus(400);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_valid_event_marks_order_paid_once_even_if_delivered_twice(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $order = $this->placeOrderFor($this->makeUser(), $this->makeProduct(), 1, 'stripe');
        $payload = $this->event('evt_dup', $order->number);
        $server = $this->transformHeadersToServerVars($this->sign($payload));

        $this->call('POST', '/stripe/webhook', [], [], [], $server, $payload)->assertOk()->assertSee('processed');
        $this->call('POST', '/stripe/webhook', [], [], [], $server, $payload)->assertOk()->assertSee('duplicate');

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(1, StripeEvent::where('event_id', 'evt_dup')->count());
        $this->assertSame(1, $order->histories()->where('status', 'processing')->count());
    }

    public function test_unpaid_completed_session_does_not_mark_paid(): void
    {
        $order = $this->placeOrderFor($this->makeUser(), $this->makeProduct(), 1, 'stripe');
        $payload = $this->event('evt_unpaid', $order->number, 'checkout.session.completed', 'unpaid');
        $this->call('POST', '/stripe/webhook', [], [], [], $this->transformHeadersToServerVars($this->sign($payload)), $payload)->assertOk();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_expired_session_releases_stock(): void
    {
        $v = $this->makeProduct(stock: 4);
        $order = $this->placeOrderFor($this->makeUser(), $v, 2, 'stripe');
        $this->assertSame(2, $v->fresh()->stock);
        $payload = $this->event('evt_exp', $order->number, 'checkout.session.expired', 'unpaid');
        $this->call('POST', '/stripe/webhook', [], [], [], $this->transformHeadersToServerVars($this->sign($payload)), $payload)->assertOk();
        $this->assertSame(4, $v->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
    }
}
