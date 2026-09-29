@component('mail::message')

Hi {{$areName}},
</br></br><br>

This is to inform you that the cheque <b>( {{$pdcCheckNo}} )</b> has been Bounced under the Tenant ( {{ $tenantName }}) with the Building ( {{$buildingName}}).
</br></br>
@if(!empty($bounceReason))
<b>Bounce reason:</b> {{ $bounceReason }}
</br>
@endif
@if(!empty($bounceRemark))
<b>Remarks:</b> {{ $bounceRemark }}
</br>
@endif
</br>

Sincerely,</br>
Admin

@endcomponent