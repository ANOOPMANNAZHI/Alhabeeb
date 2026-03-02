@component('mail::message')

Hi {{$salesName}},
</br></br><br>

New {{ $salesTypeName }} Enquiry ({{$salesEnqNo}}) has been Created.
</br></br>

Sincerely,<br/>
{{Auth::user()->employee->employee_name}}

@endcomponent