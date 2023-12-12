<x-mail::message>
This email has been sent to you you because you have been added to {{ $name }}.

This is the information of the cooperative you have been added to:

<x-mail::panel>
- **Name:** {{ $name }}
- **Email:** {{ $email }}
- **NIF:** {{ $nif }}
</x-mail::panel>


You can log in with your account to see the details in Cooperatives section.
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

