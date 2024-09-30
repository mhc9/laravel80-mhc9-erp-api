@component('mail::message')
# Verify Email

Thank you for signing up. 
Your six-digit code is {{$pin}}

<!-- @component('mail::button', ['url' => ''])
Button Text
@endcomponent -->

Thanks,<br>
{{ config('app.name') }}
@endcomponent
