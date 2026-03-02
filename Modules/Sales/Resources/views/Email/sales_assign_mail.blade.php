@component('mail::message')

Hi {{$userName}},
</br></br><br>

New {{ $salesTypeName }} Enquiry ({{$salesEnqNo}}) has been Assigned.
</br></br>

Sincerely,<br/>
{{Auth::user()->employee->employee_name ?? 'Admin'}}

@endcomponent