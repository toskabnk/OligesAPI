<x-mail::message>

{{ $name }} have deleted a receipt from your account.

The receipt deleted is as follows:

<x-mail::panel>
- **Campaign:** {{ $receipt->campaign }}
- **Albaran Nº:** {{ $receipt->albaran_number }}
- **Date:** {{ $receipt->date }}
</x-mail::panel>

If this is an error, please contact us at {{ config('mail.from.address') }}.

@component('mail::footer')
Thank you for being a part of {{ config('app.name') }}.
@endcomponent

</x-mail::message>
