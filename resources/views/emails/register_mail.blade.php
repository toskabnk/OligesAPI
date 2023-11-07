@component('mail::message')
{{-- Email Header --}}
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent

# Welcome to Oliges!

This email has been sent to you you because you have registered on our website throught our registration form or from a cooperative.

Your login credentials are as follows:

**Email:** {{ $email }}
**Password:** {{ $password }}

You can log in to our site using this information. Remember to change your password as soon as you log in.

@component('mail::button', ['url' => '#', 'color' => 'primary'])
Log In
@endcomponent

If you have any questions or need assistance, please don't hesitate to contact us. Welcome back!

{{-- Email Footer --}}
@component('mail::footer')
Thank you for being a part of {{ config('app.name') }}.
@endcomponent
@endcomponent