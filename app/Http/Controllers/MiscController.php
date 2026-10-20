<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MiscController extends Controller
{
    public function newsletter(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:190']]);
        $email = mb_strtolower($data['email']);

        $sub = NewsletterSubscriber::firstOrCreate(['email' => $email], ['subscribed_at' => now()]);

        return back()->with('toast', $sub->wasRecentlyCreated
            ? 'You\'re on the list. Welcome to VYORA.'
            : 'You\'re already subscribed — no need to sign up twice.');
    }

    public function contact() { return view('pages.contact'); }

    public function sendContact(ContactRequest $request)
    {
        $message = ContactMessage::create($request->safe()->only(['name', 'email', 'subject', 'message']));
        try {
            Mail::to(config('vyora.contact_email'))->send(new ContactReceived($message));
        } catch (\Throwable $e) {
            Log::warning('Contact email failed: '.$e->getMessage()); // the message is already saved
        }

        return redirect()->route('contact')->with('toast', 'Thanks — your message has been received. We\'ll reply within two working days.');
    }

    public function page(string $page)
    {
        abort_unless(in_array($page, ['about', 'faq', 'shipping', 'returns', 'privacy', 'terms'], true), 404);

        return view('pages.static.'.$page, ['settings' => app(\App\Services\SettingsService::class)->all()]);
    }
}
