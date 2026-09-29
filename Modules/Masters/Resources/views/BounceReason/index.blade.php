@extends('layouts.plms-app')

@section('css')
@include('masters::BounceReason._styles')
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Bounce Reason</div></div>
    {{ Breadcrumbs::render('bounce-reason.index') }}
  </div>
</div>

<div class="br">
<div class="card card-box">
  <div class="card-body">
    <div class="br-bar">
      <p class="br-hint">Reasons offered when a PDC is marked bounced. <b>Bank sheet words</b> let the PDC bank sheet upload pre-select a reason from the bank's rejection text.
        Reasons cannot be deleted (bounced PDCs keep them): switch one off to stop offering it.</p>
      @can('add_bounce_reason')
        <a href="{{ route('bounce-reason.create') }}" class="br-btn br-btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Add reason</a>
      @endcan
    </div>
    <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th style="width:60px">Code</th><th>Reason</th><th>Bank sheet words</th><th style="width:70px">Order</th>
          <th style="width:160px">Status</th><th class="num" style="width:110px">Bounced PDCs</th><th style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($reasons as $r)
          <tr>
            <td>{{ $r->id }}</td>
            <td><b>{{ $r->name }}</b></td>
            <td class="br-words">{{ $r->bank_keywords ?: '—' }}</td>
            <td>{{ $r->sort_order }}</td>
            <td>
              @if($r->is_active)
                <span class="br-badge br-badge-on"><i class="fa fa-check" aria-hidden="true"></i> Active</span>
              @else
                <span class="br-badge"><i class="fa fa-pause" aria-hidden="true"></i> Off</span>
              @endif
              @if($r->is_fallback)<span class="br-badge br-badge-fb" title="Used when the bank's text matches no reason">Default</span>@endif
            </td>
            <td class="num">{{ number_format(isset($usage[$r->id]) ? $usage[$r->id] : 0) }}</td>
            <td>
              @can('edit_bounce_reason')
                <a href="{{ route('bounce-reason.edit', $r->id) }}" class="br-btn br-btn-sm"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="7" align="center">No bounce reasons yet.</td></tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>
@endsection
