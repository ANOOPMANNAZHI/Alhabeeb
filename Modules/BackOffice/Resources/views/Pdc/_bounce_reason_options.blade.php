{{-- Bounce reason <option>s from Masters -> Bounce Reason. $selected: the PDC's pdc_bounce_reason (a switched-off reason still shows when selected). --}}
@php $brSelected = isset($selected) ? $selected : null; @endphp
@foreach(\Modules\Masters\Entities\PdcBounceReason::options($brSelected) as $brCode => $brLabel)
<option value="{{ $brCode }}" {{ (string) $brSelected !== '' && (int) $brSelected === (int) $brCode ? 'selected' : '' }}>{{ $brLabel }}</option>
@endforeach
