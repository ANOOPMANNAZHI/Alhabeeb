@component('mail::message')

Hi {{$userName}},
</br></br><br>

{{$textContent}} with {{$tenantContract}}
</br></br>

Sincerely,<br>
{{Auth::user()->employee->employee_name ?? 'Admin'}}

@endcomponent