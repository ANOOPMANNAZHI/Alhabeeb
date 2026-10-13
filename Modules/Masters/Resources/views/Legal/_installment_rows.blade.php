{{-- Installment rows editor. $prefix makes ids unique; $rows = objects with due_date / amount. --}}
@php $rows = collect($rows)->count() ? collect($rows) : collect([(object) ['due_date' => '', 'amount' => '']]); @endphp
<div class="lcs-inst" id="{{ $prefix }}_rows" data-prefix="{{ $prefix }}">
  <table class="table lcs-rows" style="margin-bottom:6px;">
    <thead><tr><th>Due date</th><th>Amount</th><th></th></tr></thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td><input type="date" name="due_date[]" class="form-control" aria-label="Due date" value="{{ $r->due_date ? substr($r->due_date, 0, 10) : '' }}"></td>
        <td><input type="number" step="0.001" min="0.001" name="due_amount[]" class="form-control lcs-inst-amount" aria-label="Amount" value="{{ $r->amount }}"></td>
        <td><button type="button" class="btn btn-xs btn-default lcs-inst-del" aria-label="Remove installment">&times;</button></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <button type="button" class="btn btn-xs btn-default lcs-inst-add">+ Add installment</button>
  <span class="text-muted lcs-inst-sum" style="margin-left:10px;font-size:12px;"></span>
</div>
