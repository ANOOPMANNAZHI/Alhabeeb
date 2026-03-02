@component('mail::message')
Hi {{$username}},
</br></br>

Your new password below :

username		: {{$username}}

New Password  	: {{$password}}
</br>

You can login with this details </br>


Sincerely,</br>

Admin

@endcomponent
