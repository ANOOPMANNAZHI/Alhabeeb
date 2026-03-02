@component('mail::message')
Hi {{$username}},
</br></br>
Thank you sign up . Here are you account details :
</br>
Username : {{$username}}</br>
Password  : {{$password}}</br>
</br>
You can login with this details </br>


Sincerely,</br>
Admin

@endcomponent
