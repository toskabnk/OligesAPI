<x-mail::message>
# Welcome to Oliges!

This email has been sent to you you because you have been registered on our website throught our registration form or from a cooperative.

Your login credentials are as follows:

<x-mail::panel>
**Email:** {{ $email }}
@if ($password != null)
    **Password:** {{ $password }}
@endif
</x-mail::panel>

You can log in to our site using this information. Remember to change your password as soon as you log in.
@php
    $url = config('app.url')
@endphp


<x-mail::button :url="$url">
Login
</x-mail::button>

If you have any questions or need assistance, please don't hesitate to contact us.

@component('mail::footer')
Thank you for being a part of {{ config('app.name') }}.
@endcomponent

</x-mail::message>

