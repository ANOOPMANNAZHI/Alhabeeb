{{-- Advanced search of the legal lists (design-system/plms-legal). legal_search_js clones a condition `.row` into
     #search_form and swaps .add_search / .remove_search; the field classes .fieldName / .operation / .fieldValue / .logic
     are read by legal_case_search_js. Keep those hooks. --}}
@php
    $conditions = old('fieldName', null) != null ? count(old('fieldName')) : 1;
@endphp
<form autocomplete="off" action="@yield('search_url')" method="GET" id="leade_search" class="lg-adv">
  <div class="lg-adv__head">
    <span class="lg-adv__title">Conditions</span>
    <span class="lg-hint" style="margin:0;">Match agreement, tenant, building or unit. Add more conditions and join them with AND / OR.</span>
  </div>
  <div id="search_form" class="lg-adv__rows">
    @for($i = 0; $i < $conditions; $i++)
    @php
        $oldField = old('fieldName')[$i] ?? '';
        $inputType = in_array($oldField, ['sales_move_in_date', 'created_at'], true) ? 'date' : 'text';
    @endphp
    <div class="row lg-adv__row">
      <label class="lg-adv__cell">
        <span class="lg-label">Field</span>
        <select class="lg-select fieldName" required name="fieldName[]">
          <option value="">Select</option>
          @foreach($enquiry_fields as $key => $val)
            <option value="{{ $key }}" @if($oldField === $key) selected @endif>{{ $val }}</option>
          @endforeach
        </select>
      </label>
      <label class="lg-adv__cell">
        <span class="lg-label">Operation</span>
        <select class="lg-select operation" required name="operation[]">
          <option value="">Select</option>
          @foreach($operations as $operation_key => $operation_val)
            <option value="{{ $operation_key }}" @if((old('operation')[$i] ?? '') === $operation_key) selected @endif>{{ trim($operation_val) }}</option>
          @endforeach
        </select>
      </label>
      <label class="lg-adv__cell">
        <span class="lg-label">Value</span>
        <input autocomplete="off" required type="{{ $inputType }}" class="lg-input fieldValue" name="fieldValue[]" placeholder="Enter value" value="{{ old('fieldValue')[$i] ?? '' }}">
      </label>
      <label class="lg-adv__cell">
        <span class="lg-label">Join next with</span>
        <select class="lg-select and_search logic" name="logic[]">
          <option value="and" @if((old('logic')[$i] ?? 'and') === 'and') selected @endif>AND</option>
          <option value="or" @if((old('logic')[$i] ?? '') === 'or') selected @endif>OR</option>
        </select>
      </label>
      <div class="lg-adv__cell lg-adv__cell--btn">
        @if($i > 0)
          <button type="button" class="lg-btn lg-btn--secondary remove_search">Remove</button>
        @else
          <button type="button" class="lg-btn lg-btn--secondary add_search">Add</button>
        @endif
      </div>
    </div>
    @endfor
  </div>
  <div class="lg-adv__foot">
    <a href="@yield('search_reset')" class="lg-btn lg-btn--ghost">Reset</a>
    <button type="submit" class="lg-btn lg-btn--primary"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
  </div>
</form>
