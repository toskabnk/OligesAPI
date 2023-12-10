<x-mail::message>

A new receipt from {{ $name }} has been registered to your account.

The receipt details are as follows:

<x-mail::panel>
- **Campaign:** {{ $receipt->campaign }}
- **Albaran Nº:** {{ $receipt->albaran_number }}
- **Date:** {{ $receipt->date }}
</x-mail::panel>

You can log in to your account to view more details from the receipt in Receipts section.
@php
    $url = config('app.url')
@endphp

<x-mail::button :url="$url">
Login
</x-mail::button>

If this is an error, please contact us at {{ config('mail.from.address') }}.

@component('mail::footer')
Thank you for being a part of {{ config('app.name') }}.
@endcomponent

</x-mail::message>
