{{-- Municipal tax / processing / penalty. $municipality = MunicipalityRegistrationLedger::modalData(). --}}
@php
  $m = $municipality;
  $fmt = function ($v) { return number_format((float) $v, 3); };
  $rateLabel = rtrim(rtrim(number_format($m['rate'], 2, '.', ''), '0'), '.') . '%';
  $taxVal = old('tenant_contract_municipality_tax_amt', number_format($m['tax'], 3, '.', ''));
  $penVal = old('tenant_contract_municipality_penalty_amt', number_format($m['penalty'], 3, '.', ''));
@endphp
<div class="col-sm-12 municipality-charges" data-processing="{{ $m['processing'] }}">
  <div class="row">
    <div class="col-md-6 col-sm-12">
      <div class="form-group">
        <label for="tenant_contract_municipality_tax_amt">Municipality Tax Amount<small class="textRed">*</small></label>
        <input type="number" step="0.001" min="0" class="form-control mc-tax" id="tenant_contract_municipality_tax_amt"
               name="tenant_contract_municipality_tax_amt" value="{{ $taxVal }}" required {{ $m['tax_locked'] ? 'readonly' : '' }}>
        <small class="text-muted">Contract value {{ $fmt($m['contract_value']) }} &times; {{ $rateLabel }} = {{ $fmt($m['contract_value'] * $m['rate'] / 100) }}</small>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="form-group">
        <label>Processing Charge</label>
        <input type="text" class="form-control" value="{{ $fmt($m['processing']) }}" readonly>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="form-group">
        <label>Total (Tax + Processing)</label>
        <input type="text" class="form-control mc-total" value="{{ $fmt((float) $taxVal + $m['processing']) }}" readonly>
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="form-group">
        <label for="tenant_contract_municipality_penalty_amt">Penalty Amount</label>
        <input type="number" step="0.001" min="0" class="form-control" id="tenant_contract_municipality_penalty_amt"
               name="tenant_contract_municipality_penalty_amt" value="{{ $penVal }}" {{ $m['penalty_locked'] ? 'readonly' : '' }}>
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      @foreach (['Tax voucher' => $m['tax_voucher'], 'Penalty voucher' => $m['penalty_voucher']] as $label => $v)
        @if ($v)
          <div><strong>{{ $label }}:</strong> {{ $v['voucher_no'] }} &mdash; {{ $v['status'] }}@if($v['posted']) <span class="text-muted">(posted to AX, amount locked)</span>@endif</div>
        @endif
      @endforeach
    </div>
  </div>
</div>
<script>
  $(document).on('input', '.municipality-charges .mc-tax', function () {
    var box = $(this).closest('.municipality-charges');
    var tax = parseFloat($(this).val()) || 0;
    var processing = parseFloat(box.data('processing')) || 0;
    box.find('.mc-total').val((tax + processing).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}));
  });
</script>
