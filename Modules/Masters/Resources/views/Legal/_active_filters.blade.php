{{-- Active cases filter bar (priority / type / stage / lawyer / building ownership / hearing soon). Needs $lcStages, $lcLawyers. --}}
@php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
<form method="GET" action="{{ route('activeCases') }}" class="lg-filters" aria-label="Filter active cases">
  <div class="lg-field"><label for="af_priority" class="lg-label">Priority</label>
    <select id="af_priority" name="lc_priority" class="lg-select"><option value="">All</option>
      @foreach($LC::PRIORITIES as $k => $v)<option value="{{ $k }}" @if((string) request('lc_priority') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
  <div class="lg-field"><label for="af_type" class="lg-label">Case type</label>
    <select id="af_type" name="lc_type" class="lg-select"><option value="">All</option>
      @foreach($LC::TYPES as $k => $v)<option value="{{ $k }}" @if((string) request('lc_type') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
  <div class="lg-field"><label for="af_stage" class="lg-label">Stage</label>
    <select id="af_stage" name="lc_stage" class="lg-select"><option value="">All</option>
      <option value="none" @if(request('lc_stage') === 'none') selected @endif>Stage not set</option>
      @foreach($lcStages as $s)<option value="{{ $s->id }}" @if((string) request('lc_stage') === (string) $s->id) selected @endif>{{ $s->name }}</option>@endforeach</select></div>
  <div class="lg-field"><label for="af_lawyer" class="lg-label">Lawyer</label>
    <select id="af_lawyer" name="lc_lawyer" class="lg-select"><option value="">All</option>
      <option value="none" @if(request('lc_lawyer') === 'none') selected @endif>Not assigned</option>
      @foreach($lcLawyers as $id => $nm)<option value="{{ $id }}" @if((string) request('lc_lawyer') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
  <div class="lg-field"><label for="af_ownership" class="lg-label">Building ownership</label>
    <select id="af_ownership" name="lc_ownership" class="lg-select"><option value="">All</option>
      @foreach(\Modules\Masters\Entities\Building::ownershipTypes() as $k => $v)<option value="{{ $k }}" @if((string) request('lc_ownership') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
  <label class="lg-check"><input type="checkbox" name="lc_hearing" value="7" @if(request('lc_hearing')) checked @endif> Hearing in next 7 days</label>
  <div class="lg-filters__actions">
    <a class="lg-btn lg-btn--ghost" href="{{ route('activeCases') }}">Reset</a>
    <button class="lg-btn lg-btn--primary" type="submit">Apply</button>
  </div>
</form>
