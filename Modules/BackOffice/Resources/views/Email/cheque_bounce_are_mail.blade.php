@component('mail::message')

Hi {{$areName}},
</br></br><br>

This is to inform you that the cheque <b>( {{$pdcCheckNo}} )</b> has been Bounced under the Tenant ( {{ $tenantName }}) with the Building ( {{$buildingName}}).
</br></br>

Sincerely,</br>
Admin

@endcomponent