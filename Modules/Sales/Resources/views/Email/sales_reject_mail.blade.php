@component('mail::message')

Hi {{$salesName}},
</br></br><br>

{{$textContent}}
</br></br>

Sincerely,<br/>
{{Auth::user()->employee->employee_name ?? 'Admin'}}

@endcomponent