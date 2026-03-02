@component('mail::message')

Hi {{$userName}},
<br><br>
{{$textContent}}
<br><br>
Sincerely,<br>
{{Auth::user()->employee->employee_name ?? 'Admin'}}

@endcomponent
