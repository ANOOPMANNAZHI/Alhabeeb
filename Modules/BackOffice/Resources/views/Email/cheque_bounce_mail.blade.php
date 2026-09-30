@component('mail::message')

Dear {{$tenantName}},
</br></br><br>

This is to inform you that your cheque <b>( {{$pdcCheckNo}} )</b> has been returned by the bank.
</br></br>
<b>Amount:</b> {{ $pdcAmount }}
</br>
@if(!empty($pdcDate))
<b>Cheque date:</b> {{ $pdcDate }}
</br>
@endif
@if(!empty($unitName))
<b>Unit:</b> {{ $unitName }}
</br>
@endif
@if(!empty($bounceReason))
<b>Reason:</b> {{ $bounceReason }}
</br>
@endif
</br>
Please contact us to replace the cheque.
</br></br>

Sincerely,</br>
Admin

@endcomponent
