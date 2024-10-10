@component('mail::message')
# Reset Password

รหัส PIN 6 หลัก ของคุณคือ <h4>{{$pin}}</h4>
<p>กรุณาอย่าเปิดเผย รหัส PIN แก่ผู้อื่นๆ หรือ ผู้ไม่เกี่ยวข้อง</p>
<p>You made a request to reset your password. Please discard if this wasn't you.</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
