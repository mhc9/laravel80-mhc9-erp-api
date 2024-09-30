@component('mail::message')
# Verify Email

Thank you for signing up. 
Your six-digit code is {{$pin}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
