{{-- Errors of a case page modal: shown inside the modal that was submitted (old('_modal') === $modalId).
     Validation goes to the 'modal' error bag, other messages to the 'modal_error' flash (LegalCaseController). --}}
@php
    $mine = old('_modal') === $modalId;
    $bag = $errors->getBag('modal');
@endphp
@if($mine && ($bag->any() || session('modal_error')))
  <div class="lg-alert lg-alert--danger" role="alert" tabindex="-1">
    <div>
      @if(session('modal_error'))<div>{{ session('modal_error') }}</div>@endif
      @if($bag->any())
        <ul style="margin:0;padding-left:18px;">
          @foreach($bag->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
      @endif
    </div>
  </div>
@endif
