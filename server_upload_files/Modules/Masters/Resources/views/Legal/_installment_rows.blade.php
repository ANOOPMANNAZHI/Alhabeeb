{{-- Installment rows editor. $prefix makes ids unique; $rows = objects with due_date / amount. Inside a .lg page. --}}
@php $rows = collect($rows)->count() ? collect($rows) : collect([(object) ['due_date' => '', 'amount' => '']]); @endphp
<div class="lcs-inst" id="{{ $prefix }}_rows" data-prefix="{{ $prefix }}">
  <div class="lg-table-wrap" style="border:1px solid var(--lg-border);border-radius:var(--lg-radius-sm);">
    <table class="lg-table">
      <thead><tr><th scope="col">Due date</th><th scope="col">Amount</th><th scope="col"><span class="sr-only">Remove</span></th></tr></thead>
      <tbody>
        @foreach($rows as $r)
        <tr>
          <td><input type="date" name="due_date[]" class="lg-input" aria-label="Due date" value="{{ $r->due_date ? substr($r->due_date, 0, 10) : '' }}"></td>
          <td><input type="number" step="0.001" min="0.001" name="due_amount[]" class="lg-input tnum lcs-inst-amount" aria-label="Amount" value="{{ $r->amount }}"></td>
          <td class="actions"><button type="button" class="lg-btn lg-btn--ghost lg-btn--icon lcs-inst-del" aria-label="Remove installment"><i class="fa fa-times" aria-hidden="true"></i></button></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
    <button type="button" class="lg-btn lg-btn--secondary lg-btn--sm lcs-inst-add"><i class="fa fa-plus" aria-hidden="true"></i> Add installment</button>
    <span class="lcs-inst-sum tnum" style="font-size:12px;" aria-live="polite"></span>
  </div>
</div>
