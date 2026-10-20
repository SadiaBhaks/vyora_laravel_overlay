<x-mail::message>
# New contact message

**From:** {{ $contact->name }} ({{ $contact->email }})
**Subject:** {{ $contact->subject }}

{{ $contact->message }}
</x-mail::message>
