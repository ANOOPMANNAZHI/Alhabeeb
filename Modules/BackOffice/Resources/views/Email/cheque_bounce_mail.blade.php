@component('mail::message')

Hi {{$tenantName}},
</br></br><br>

This is to inform you that the cheque <b>( {{$pdcCheckNo}} )</b> has been Bounced.
</br></br>

Sincerely,</br>
Admin

@endcomponent