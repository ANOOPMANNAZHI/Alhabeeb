{{-- One row of a legal list. $c = Legal; $kind = plm | lawyer | active | closed | referback | case;
     $viewRoute = route name of the old detail screen; $lgStatusColumn shows the workflow status. Buttons keep the classes /
     data-* the list scripts bind to. One row per tenant + building + unit (Legal::latestPerGroup);
     the group's other cases ($c->groupSiblings) follow as hidden rows ($lgGroupOf = id of the row they belong to). --}}
@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $viewUrl = !empty($viewRoute) ? route($viewRoute, $c->id) : null;
    $lgSiblings = empty($lgGroupOf) && $c->relationLoaded('groupSiblings') ? $c->groupSiblings : collect();
    $tc = $c->tenantContract;
    $active = $tc && (int) $tc->tenant_contract_status === 1;
    $hasCase = $LC::hasCasePage($c);   // the case page exists once the lawyer accepted
    $openUrl = $hasCase ? route('legal.case', $c->id) : ($viewUrl ?? $LC::screenUrl($c));
@endphp
<tr @if(!empty($lgGroupOf)) class="lg-group-row" data-group-of="{{ $lgGroupOf }}" hidden @endif>
  <td><a class="lg-row-link" href="{{ $openUrl }}">{{ $tc->tenant_contract_no ?? '' }}</a>
    <div class="muted" style="font-size:12px;">#{{ $c->id }}</div>
    @if($lgSiblings->count())
    <button type="button" class="lg-group-toggle" data-group="{{ $c->id }}" aria-expanded="false" title="Other cases of this tenant and unit">+{{ $lgSiblings->count() }} more</button>
    @endif</td>
  <td class="strong">{{ $tc->tenant->tenant_name ?? '' }}</td>
  <td>{{ $c->building->building_name ?? '' }}</td>
  <td>{{ $c->Unit->unit_code ?? '' }}</td>
  <td class="tnum">{{ $tc && $tc->tenant_contract_valid_to_date ? $tc->tenant_contract_valid_to_date->format('d/m/Y') : '' }}</td>
  <td><span class="lg-badge lg-badge--{{ $active ? 'success' : 'neutral' }}">{{ $active ? 'Active' : 'Inactive' }}</span></td>
  <td class="num">{{ $tc ? numberFormat($tc->tenant_contract_rent) : '' }}</td>
  <td>@if($c->priority)<span class="lg-badge lg-prio--{{ $c->priority }}">{{ $LC::PRIORITIES[$c->priority] }}</span>@else <span class="muted">-</span> @endif</td>
  @if(!empty($lgStatusColumn))
  <td><span class="lg-badge lg-badge--{{ $c->closed_at ? 'neutral' : ((int) $c->work_flow_processes_code === 803 ? 'success' : ((int) $c->work_flow_processes_code === 804 ? 'danger' : 'warning')) }}">
    {{ $c->closed_at ? 'Closed' : ($c->workFlowProcess->work_flow_processes_name ?? '') }}</span></td>
  @endif
  @if(!empty($lgActiveCols))
  <td>{{ $c->stage_id && isset($lcStageNames[$c->stage_id]) ? $lcStageNames[$c->stage_id] : '-' }}</td>
  <td class="tnum">{{ $c->next_hearing_date ? \Carbon\Carbon::parse($c->next_hearing_date)->format('d/m/Y') : '' }}</td>
  @endif
  <td class="actions">
    @if($kind === 'plm')
      @can('plms_approve')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon approve" data-id="{{ $c->id }}" data-key="APRV" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" title="Approve" aria-label="Approve case {{ $c->id }}"><i class="fa fa-check" aria-hidden="true"></i></button>
      @endcan
      @can('plms_refer_back')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon referback" data-id="{{ $c->id }}" data-key="RFRBK" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" title="Refer back" aria-label="Refer back case {{ $c->id }}"><i class="fa fa-reply" aria-hidden="true"></i></button>
      @endcan
      @can('plms_close')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon close_type" data-id="{{ $c->id }}" data-flow-id="801" title="Close" aria-label="Close case {{ $c->id }}"><i class="fa fa-times-circle" aria-hidden="true"></i></button>
      @endcan
    @elseif($kind === 'lawyer')
      @can('lawyer_approve')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon approve" data-id="{{ $c->id }}" data-key="APRV" data-flow-id="802" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" title="Accept" aria-label="Accept case {{ $c->id }}"><i class="fa fa-check" aria-hidden="true"></i></button>
      @endcan
      @can('lawyer_refer_back')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon referback" data-id="{{ $c->id }}" data-key="RFRBK" data-flow-id="801" data-status="1" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" title="Reject" aria-label="Reject case {{ $c->id }}"><i class="fa fa-reply" aria-hidden="true"></i></button>
      @endcan
    @elseif($kind === 'case' && (int) $c->work_flow_processes_code === 804)
      @can('legal_are_stage')
      <button type="button" class="lg-btn lg-btn--ghost lg-btn--icon are_stage" data-id="{{ $c->id }}" data-flow-id="{{ (int) $c->are_status === 1 ? 802 : 801 }}" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" title="Send for verification" aria-label="Send case {{ $c->id }} for verification"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
      @endcan
    @endif
    @if($hasCase)
      @if(!empty($viewUrl))
      <a href="{{ $viewUrl }}" class="lg-btn lg-btn--ghost lg-btn--icon" title="Contract details" aria-label="Contract details for case {{ $c->id }}"><i class="fa fa-file-text-o" aria-hidden="true"></i></a>
      @endif
      <a href="{{ route('legal.case', $c->id) }}" class="lg-btn lg-btn--secondary lg-btn--sm">Open case</a>
    @else
      <a href="{{ $openUrl }}" class="lg-btn lg-btn--secondary lg-btn--sm">View</a>
    @endif
  </td>
</tr>
@foreach($lgSiblings as $sib)
  @include('masters::Legal._list_row', ['c' => $sib, 'lgGroupOf' => $c->id])
@endforeach
