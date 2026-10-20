<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderReceipt implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        Mail::to($event->order->email)->send(new OrderPlaced($event->order->load('items')));
    }
}
