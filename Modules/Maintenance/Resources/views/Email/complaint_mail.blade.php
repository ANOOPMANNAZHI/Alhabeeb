@component('mail::message')

Dear {{$tenantName}},
</br></br>

 Dear Customer, We have received your complaint, our maintenance team will get back to you at the earliest. Your complaint No is {{$compNo}}
</br></br>

Sincerely,<br>
{{Auth::user()->employee->employee_name ?? 'Admin'}}

@endcomponent

