@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  /* Taken-over view: same tokens as the inspection screens. */
  .tov { --t-blue:#2563eb; --t-blue-soft:#eff6ff; --t-ink:#1f2937; --t-muted:#6b7280; --t-line:#e5e7eb; --t-bg:#f9fafb; --t-danger:#dc2626; --t-ok:#16a34a; --t-radius:8px; --t-tap:44px; color:var(--t-ink); }
  .tov .card-box { border-radius:var(--t-radius); }
  .tov .card-box:hover { transform:none; }
  .tov .t-head { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:14px 18px 6px; }
  .tov .t-head .t-title { font-size:18px; font-weight:800; margin:0; }
  .tov .t-head .t-sub { color:var(--t-muted); font-size:13px; }
  .tov .t-head .t-actions { margin-left:auto; display:flex; flex-wrap:wrap; gap:8px; }
  .tov .t-head .t-actions .btn { min-height:40px; display:inline-flex; align-items:center; gap:6px; margin:0; }
  .tov .t-status { display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:4px 10px; border-radius:999px; background:var(--t-line); color:var(--t-ink); }
  .tov .t-os-yes { color:#991b1b; font-weight:700; } .tov .t-os-no { color:#166534; font-weight:700; }

  .tov .t-snap { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:12px 24px; padding:6px 0 4px; }
  .tov .t-snap-item .t-lbl { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--t-muted); margin-bottom:2px; }
  .tov .t-snap-item .t-val { font-size:14px; font-weight:600; word-break:break-word; }
  .tov .t-group { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--t-blue); border-top:1px solid var(--t-line); padding-top:12px; margin:14px 0 8px; }
  .tov .t-group:first-child { border-top:0; margin-top:0; padding-top:0; }

  /* Inline termination-date edit (same ids/classes the existing JS uses) */
  .tov .t-edit { display:inline-flex; align-items:center; gap:6px; }
  .tov .t-edit button { border:0; background:var(--t-bg); color:var(--t-muted); width:32px; height:32px; border-radius:6px; cursor:pointer; }
  .tov .t-edit button:hover { color:var(--t-blue); background:var(--t-blue-soft); }
  .tov .t-edit input[type=date] { min-height:36px; border:1px solid var(--t-line); border-radius:6px; padding:0 8px; }

  .tov .t-section { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:700; border-left:4px solid var(--t-blue); padding:6px 12px; margin:0 0 10px; background:var(--t-blue-soft); border-radius:6px; }
  .tov .t-section .t-hint { margin-left:auto; font-size:12px; font-weight:500; color:var(--t-muted); }
  .tov .t-table-wrap { border:1px solid var(--t-line); border-radius:var(--t-radius); overflow:hidden; }
  .tov table.t-table { margin:0; }
  .tov table.t-table th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:var(--t-muted); background:var(--t-bg); border-top:0; border-bottom:2px solid var(--t-line); padding:8px 12px; }
  .tov table.t-table td { padding:8px 12px; vertical-align:middle; }
  .tov .t-empty { padding:14px; color:var(--t-muted); font-size:13px; border:1px dashed var(--t-line); border-radius:var(--t-radius); text-align:center; }

  /* Notes timeline */
  .tov .t-timeline { list-style:none; margin:0; padding:0 0 0 18px; border-left:2px solid var(--t-line); }
  .tov .t-timeline li { position:relative; padding:0 0 14px 14px; }
  .tov .t-timeline li::before { content:""; position:absolute; left:-24px; top:4px; width:10px; height:10px; border-radius:50%; background:var(--t-blue); border:2px solid #fff; box-shadow:0 0 0 2px var(--t-blue); }
  .tov .t-timeline .t-stage { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--t-blue); }
  .tov .t-timeline .t-meta { font-size:12px; color:var(--t-muted); margin-left:8px; }
  .tov .t-timeline .t-note { margin-top:4px; font-size:14px; white-space:pre-line; }
  .tov .t-two { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  @media (max-width: 900px) { .tov .t-two { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
@php
  $tc = $tenantContract;
  $lastTerm = $tc->terminationContract;
  $fmtDate = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : 'NA'; };
  $inspection = \Modules\BackOffice\Entities\Termination::where('contract_id', $tc->id)->whereNotNull('termination_total_amount')->orderBy('id', 'desc')->first();
@endphp
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left">
      <div class="page-title">Taken Over for Termination</div>
    </div>
    {{ Breadcrumbs::render('takeoverForTerminationView', $termination) }}
  </div>
</div>

<div class="row tov">
<div class="col-sm-12">

  {{-- ── Contract snapshot + actions ─────────────────────────────── --}}
  <div class="card card-box">
    <div class="t-head">
      <div>
        <h3 class="t-title">{{ $tc->tenant_contract_no }} <small class="t-sub">· {{ $tc->tenant->tenant_name }}</small></h3>
        <div class="t-sub">{{ $tc->building->building_name }} · Unit {{ $tc->unit->unit_no }} · <span class="t-status">{{ $tc->tenant_contract_status_name }}</span></div>
      </div>
      <div class="t-actions">
        @if($outstandingOs > 0)
          <span class="t-os-yes" title="Outstanding rent">Outstanding {{ numberFormat($outstandingOs) }} OMR</span>
        @else
          <span class="t-os-no">No outstanding</span>
        @endif
        @can('takenover_resubmit')
        <button type="button" class="btn btn-circle btn-default resubmit" data-toggle="modal" data-target="#myModal" data-id="RESUB" title="Resubmit" id="{{ $termination->tenantContract->id }}" datas-id="{{ $termination->id }}" datas-enid="{{ $termination->work_flow_processes_code }}" data-backdrop="static" data-keyboard="false">
          <i class="fa fa-undo" aria-hidden="true"></i> Resubmit
        </button>
        @endcan
        @can('tenant_terminate')
        <button type="button" class="btn btn-circle btn-primary terminate" data-toggle="modal" data-target="#myModal_terminate" data-id="TMT" title="Terminate" id="{{ $termination->tenantContract->id }}" datas-id="{{ $termination->id }}" datas-enid="{{ $termination->work_flow_processes_code }}" data-backdrop="static" data-keyboard="false">
          <i class="fa fa-check" aria-hidden="true"></i> Terminate
        </button>
        @endcan
      </div>
    </div>
    <div class="card-body">
      <div class="t-group">Tenant &amp; unit</div>
      <div class="t-snap">
        <div class="t-snap-item"><span class="t-lbl">Mobile</span><span class="t-val">{{ $tc->tenant->tenant_contact_no ?: 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Location</span><span class="t-val">{{ optional(optional($tc->building)->location)->locations_name ?? 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Way No</span><span class="t-val">{{ $tc->building->building_address ?? 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Tenant status</span><span class="t-val">{{ $tc->tenant->tenant_status_name ?? 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Unit status</span><span class="t-val">{{ $tc->unit->vacant_status_name ?? 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Key status</span><span class="t-val">{{ !empty($tc->unit->key) ? $tc->unit->key->status_name : 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Municipality registration</span><span class="t-val">{{ $tc->tenant_contract_is_reg_municipality == null ? 'No' : 'Yes' }}</span></div>
      </div>

      <div class="t-group">Rent</div>
      <div class="t-snap">
        <div class="t-snap-item"><span class="t-lbl">Rent paid up to</span><span class="t-val">{{ $fmtDate($tc->tenant_contract_last_paid_date) }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Last paid date</span><span class="t-val">{{ isset($tc->tenant->tenant_contract_last_paid_date) ? $tc->tenant->tenant_contract_last_paid_date->format('d/m/Y') : 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Last paid amount</span><span class="t-val">{{ isset($tc->tenant->tenant_contract_last_paid_amt) ? numberFormat($tc->tenant->tenant_contract_last_paid_amt) . ' OMR' : 'NA' }}</span></div>
        <div class="t-snap-item"><span class="t-lbl">Remaining days to expiry</span><span class="t-val">{{ $remainingDays }}</span></div>
      </div>

      <div class="t-group">Termination</div>
      <div class="t-snap">
        <div class="t-snap-item"><span class="t-lbl">Taken over date</span><span class="t-val closeTaken">{{ !empty($lastTerm->termination_takenover_date) ? $lastTerm->termination_takenover_date->format('d/m/Y') : 'NA' }}</span></div>
        <div class="t-snap-item">
          <span class="t-lbl">Termination date</span>
          <span class="t-val t-edit">
            <span class="closeTermination">{{ !empty($lastTerm->termination_date) ? $lastTerm->termination_date->format('d/m/Y') : 'NA' }}</span>
            <span class="openTermination" style="display:none"><input type="date" name="termination_date" id="termination_date" class="form-controll"></span>
            <button type="button" class="editTermination" title="Edit termination date"><i class="fa fa-pencil"></i></button>
            <button type="button" class="saveTerminationbtn" title="Save" style="display:none"><i class="fa fa-save"></i></button>
            <button type="button" class="closeTerminationbtn" title="Cancel" style="display:none"><i class="fa fa-close"></i></button>
          </span>
          <input type="hidden" name="termination_id" id="termination_id" value="{{ optional($lastTerm)->id }}">
          <span class="openTaken" style="display:none"><input type="date" name="termination_takenover_date" id="termination_takenover_date" class="form-controll"></span>
        </div>
        <div class="t-snap-item"><span class="t-lbl">Assigned to</span><span class="t-val">{{ isset($lastTerm->assignedTo) ? $lastTerm->assignedTo->employee->employee_name : 'NA' }}</span></div>
        <div class="t-snap-item" style="grid-column: span 2"><span class="t-lbl">Remark</span><span class="t-val">{{ optional($lastTerm)->termination_remark ?? 'NA' }}</span></div>
      </div>
    </div>
  </div>

  {{-- ── Inspection details (shared partial) ─────────────────────── --}}
  <div class="row">
    @include('backoffice::Termination._inspection_details')
  </div>

  {{-- ── Documents + notes ───────────────────────────────────────── --}}
  <div class="t-two">
    <div class="card card-box" style="margin:0">
      <div class="card-body">
        <div class="t-section">Open-for-termination documents <span class="t-hint">{{ count($openTerminationDocument) }}</span></div>
        @if(count($openTerminationDocument))
        <div class="t-table-wrap table-responsive">
          <table class="table t-table">
            <thead><tr><th style="width:56px">#</th><th>Document</th></tr></thead>
            <tbody>
            @foreach($openTerminationDocument as $document)
              <tr><td>{{ $loop->iteration }}</td><td><a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank"><i class="fa fa-file-o" aria-hidden="true"></i> {{ $document->termination_doc_name }}</a></td></tr>
            @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="t-empty">No documents.</div>
        @endif
      </div>
    </div>

    <div class="card card-box" style="margin:0">
      <div class="card-body">
        <div class="t-section">Termination notes <span class="t-hint">{{ count($terminationNotes) }}</span></div>
        @if(count($terminationNotes))
        <ul class="t-timeline">
          @foreach($terminationNotes->sortByDesc('created_at') as $note)
          <li>
            <span class="t-stage">{{ optional($note->WorkFlowProcessesCode)->work_flow_processes_name ?: 'Note' }}</span>
            <span class="t-meta">{{ optional(optional($note->createdBy)->employee)->employee_name ?? 'Admin' }} · {{ $note->created_at->format('d/m/Y') }}</span>
            <div class="t-note">{{ $note->termination_notes }}</div>
          </li>
          @endforeach
        </ul>
        @else
        <div class="t-empty">No notes recorded.</div>
        @endif
      </div>
    </div>
  </div>

</div>
</div>
<div class="modal" id="myModal"></div>
<div class="modal" id="myModal_terminate"></div>
@endsection
@section('scripts')
<script>
 $(document).ready(function() {
    $("#myModal").on("hidden.bs.modal", function(){
            $("#myModal").html("");
            $(this).removeData('bs.modal');
    });
    $("#myModal_terminate").on("hidden.bs.modal", function(){
            $("#myModal_terminate").html("");
            $(this).removeData('bs.modal');
    });
	
  });
 /****************************************************************************/
  $(".editTermination").on('click',function(e){
    var valText = $(".closeTermination").text();
    var datesplit = valText.split('/');
    var terminateDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

    var valText = $(".closeTaken").text();
    var datesplit = valText.split('/');

    var takeOverDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

    var dtToday = new Date();

    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
      month = '0' + month.toString();
    if(day < 10)
      day = '0' + day.toString();

    var maxDate = year + '-' + month + '-' + day;

    $("#termination_date").val(terminateDt).show();
    if(takeOverDt)

      $('#termination_date').attr('min', maxDate);
    else
      $('#termination_date').attr('min', maxDate);

    $(".openTermination").show();
    $(".closeTerminationbtn").show();
    $(".editTermination").hide();
    $(".closeTermination").hide();
    $(".saveTerminationbtn").show();

  });
  /****************************************************************************/

  $(".closeTerminationbtn").on('click',function(e){

    $(".openTermination").hide();
    $(".editTermination").show();
    $(".closeTermination").show();
    $(".saveTerminationbtn").hide();
    $(".closeTerminationbtn").hide();  


  });
  /****************************************************************************/

     $(".saveTerminationbtn").on('click',function(e){
      var termination_id              = $("#termination_id").val();
      var termination_takenover_date  = $("#termination_takenover_date").val();
      var termination_takenover_date_txt  = $(".closeTaken").text().split('/');
      var termination_date_txt            = $(".closeTermination").text().split('/');
      var termination_date            = $("#termination_date").val();
      var clsName                     = $(this).attr('class');
      var dtToday                     = new Date();

      if(termination_takenover_date_txt && clsName=='saveTerminationbtn'){
        termination_takenover_dt = termination_takenover_date_txt[2]+'-'+   termination_takenover_date_txt[1]+'-'+termination_takenover_date_txt[0];
        termination_takenover_date = termination_takenover_dt;
        termination_date_dt = termination_date_txt[2]+'-'+   termination_date_txt[1]+'-'+termination_date_txt[0];
      }


      if(termination_id && (termination_id || termination_date )){

        $.ajax
        ({
          type: "POST",
          url: "{{route('terminationUpdateExtraFields')}}",
          data: {"termination_id":termination_id,"termination_date":termination_date,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
          if(termination_date && clsName=='saveTerminationbtn'){

            var d = new Date(termination_date);  
            var day = d.getDate();
            var month_index = d.getMonth()+1;
            var year = d.getFullYear();  
            if(month_index < 10) month_index = '0'+month_index;
            $(".closeTermination").text( day + "/" + month_index + "/" + year).show();
            $(".openTermination").hide();
            $(".editTermination").show();
            $(".saveTerminationbtn").hide();
            $(".closeTerminationbtn").hide();  

          }
        }
      });
      }
      else{

        alert("Termination Value Is Incorrect");
        return false;
      }
    });

/****************************************************************************/
 $(document).on('click','.resubmit, .terminate', function(e) {        

            var action_key    = $(this).attr('data-id');
            var terminatedId  = $(this).attr('datas-id');
            var workflow_id   = $(this).attr('datas-enid');
            var contractId    = $(this).attr('id');
          
            if(action_key == 'TMT')
               var elementId =  'myModal_terminate';
            else
               var elementId =  'myModal';

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('terminateResubmitOrTerminateModal')}}", // This is the url we gave in the route
                data: {'terminatedId' : terminatedId,'action_key' : action_key,'contractId' : contractId,'workflow_id' : workflow_id,'action_key':action_key,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#"+elementId).html(response); 
                },
            }); 
            
            return true;
  });      
</script>
@endsection
