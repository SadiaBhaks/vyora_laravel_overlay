<?php

namespace App\Livewire;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\OrderService;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\StripeService;
use Illuminate\Support\Str;
use Livewire\Component;

class CheckoutWizard extends Component
{
    public int $step = 1; // 1 contact, 2 shipping address, 3 delivery, 4 payment, 5 review

    public string $email = '';
    public ?int $addressId = null;
    public bool $saveAddress = true;
    public array $address = ['full_name' => '', 'phone' => '', 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'postal_code' => '', 'country' => 'US'];
    public string $shippingMethod = 'standard';
    public string $coupon = '';
    public ?string $couponMessage = null;
    public string $idempotencyKey = '';
    public ?string $error = null;

    public function mount(): void
    {
        if (app(CartService::class)->count() === 0) {
            $this->redirectRoute('cart');

            return;
        }
        $user = auth()->user();
        $this->email = $user?->email ?? '';
        $this->coupon = (string) session('vyora.coupon', '');
        $this->idempotencyKey = (string) Str::uuid();
        if ($user) {
            $default = $user->addresses()->first();
            $this->addressId = $default?->id;
            $this->address['full_name'] = $user->name;
        }
    }

    public function goTo(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step; // going back is always allowed; going forward needs validation via next()
        }
    }

    public function next(): void
    {
        $this->error = null;
        match ($this->step) {
            1 => $this->validate(['email' => ['required', 'email:rfc', 'max:190']]),
            2 => $this->validateAddress(),
            3 => $this->validate(['shippingMethod' => ['required', 'in:'.implode(',', array_keys(config('vyora.shipping_methods')))]]),
            4 => $this->assertPaymentAvailable(),
            default => null,
        };
        if ($this->getErrorBag()->isNotEmpty() || $this->error) {
            return;
        }
        $this->step = min(5, $this->step + 1);
    }

    private function validateAddress(): void
    {
        if ($this->addressId && auth()->check()) {
            if (! auth()->user()->addresses()->whereKey($this->addressId)->exists()) {
                $this->addError('addressId', 'Please choose one of your saved addresses.');
            }

            return;
        }
        $this->validate(AddressRequest::ruleSet('address.'));
    }

    private function assertPaymentAvailable(): void
    {
        if (PaymentGateway::provider() === null) {
            $this->error = 'Payments are not available right now. Please try again later.';
        }
    }

    public function useNewAddress(): void { $this->addressId = null; }

    public function applyCoupon(): void
    {
        $summary = app(CartService::class)->summary(trim($this->coupon) ?: null, $this->shippingMethod);
        if (trim($this->coupon) === '') {
            session()->forget('vyora.coupon');
            $this->couponMessage = null;
        } elseif ($summary['coupon']) {
            session(['vyora.coupon' => $summary['coupon']->code]);
            $this->coupon = $summary['coupon']->code;
            $this->couponMessage = 'Coupon applied.';
        } else {
            session()->forget('vyora.coupon');
            $this->couponMessage = $summary['coupon_error'];
        }
    }

    public function placeOrder(CheckoutService $checkout, StripeService $stripe, OrderService $orders)
    {
        $this->error = null;
        $this->validate(['email' => ['required', 'email:rfc', 'max:190']]);
        $this->validateAddress();
        $this->validate(['shippingMethod' => ['required', 'in:'.implode(',', array_keys(config('vyora.shipping_methods')))]]);
        if ($this->getErrorBag()->isNotEmpty()) {
            $this->step = 2;

            return;
        }

        $provider = PaymentGateway::provider();
        if ($provider === null) {
            $this->error = 'Payments are not available right now. Please try again later.';

            return;
        }

        $user = auth()->user();
        $address = $this->addressId && $user
            ? $user->addresses()->whereKey($this->addressId)->firstOrFail()->toSnapshot()
            : $this->address;

        try {
            $order = $checkout->placeOrder([
                'email' => mb_strtolower($this->email),
                'address' => $address,
                'shipping_method' => $this->shippingMethod,
                'coupon' => session('vyora.coupon'),
                'idempotency_key' => $this->idempotencyKey,
                'payment_provider' => $provider,
            ], $user);
        } catch (CheckoutException $e) {
            $this->error = $e->getMessage();

            return;
        }

        if ($user && ! $this->addressId && $this->saveAddress) {
            $user->addresses()->create($this->address + ['label' => 'Home', 'is_default' => $user->addresses()->count() === 0]);
        }
        if (! $user) {
            session()->push('vyora.guest_orders', $order->number);
        }
        app(CartService::class)->clear();
        session()->forget('vyora.coupon');

        if ($provider === 'demo') {
            return $this->redirectRoute('checkout.demo', ['order' => $order->number]);
        }

        try {
            return $this->redirect($stripe->createCheckoutSession($order->load('items')));
        } catch (\Throwable $e) {
            report($e);
            $orders->markPaymentFailed($order, 'Could not start Stripe checkout.');
            foreach ($order->items()->whereNotNull('product_variant_id')->get() as $item) {
                app(CartService::class)->add($item->product_variant_id, $item->quantity);
            }
            $this->error = 'We could not start the secure payment. Nothing was charged and your cart is intact.';
            $this->idempotencyKey = (string) Str::uuid();
        }
    }

    public function render()
    {
        $cart = app(CartService::class);
        $user = auth()->user();

        return view('livewire.checkout-wizard', [
            'summary' => $cart->summary(session('vyora.coupon'), $this->shippingMethod),
            'saved' => $user ? $user->addresses : collect(),
            'methods' => config('vyora.shipping_methods'),
            'provider' => PaymentGateway::provider(),
        ]);
    }
}
