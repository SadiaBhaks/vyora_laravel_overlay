<x-mail::message>
# Payment received

Thanks for your order, {{ $order->shipping_address['full_name'] ?? '' }}.

@if($order->is_demo)
**This was a demo-mode order. No card was charged and nothing will ship.**
@endif

**Order {{ $order->number }}** · placed {{ $order->placed_at->format('M j, Y') }}

<x-mail::table>
| Item | Qty | Total |
|:-----|:---:|------:|
@foreach($order->items as $i)
| {{ $i->name }} | {{ $i->quantity }} | {{ $i->lineTotal() }} |
@endforeach
</x-mail::table>

Subtotal: {{ $order->money('subtotal') }}
@if($order->discount_cents)<br>Discount: −{{ $order->money('discount') }}@endif
<br>Shipping: {{ $order->shipping_cents ? $order->money('shipping') : 'Free' }}
<br>Estimated tax: {{ $order->money('tax') }}
<br>**Total: {{ $order->money('total') }}**

<x-mail::button :url="route('account.orders.show', $order)">Track your order</x-mail::button>

Discover differently,<br>VYORA
</x-mail::message>
