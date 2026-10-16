@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $timeline = isset($timeline) ? $timeline : \Modules\Masters\Services\LegalCase::timeline($lawyerApproval->id); @endphp
<div class="lg">
  @component('masters::Legal._detail_head', ['legal' => $lawyerApproval, 'crumb' => 'lawyerApprovalShow', 'crumbParam' => $lawyerApproval, 'screen' => 'Lawyer approval'])
    @slot('actions')
      @if(!$lawyerApproval->closed_at)
      @can('lawyer_close')
      <button type="button" class="lg-btn lg-btn--danger close_type" title="Close" data-id="{{ $lawyerApproval->id }}" data-flow-id="801">Close</button>
      @endcan
      @can('lawyer_refer_back')
      <button type="button" class="lg-btn lg-btn--warning referback" data-id="{{ $lawyerApproval->id }}" title="Reject and send back to PLM" data-key="RFRBK" data-flow-id="801" data-status="1" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Reject</button>
      @endcan
      @can('lawyer_approve')
      <button type="button" class="lg-btn lg-btn--primary approve" data-id="{{ $lawyerApproval->id }}" title="Approve" data-key="APRV" data-flow-id="802" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Accept case</button>
      @endcan
      @endif
    @endslot
  @endcomponent
  <div class="lg-grid">
    <div>
      <section class="lg-card" aria-label="Overview"><div class="lg-card__head"><h2 class="lg-card__title">Overview</h2></div>
        <div class="lg-card__body">@include('masters::Legal._case_summary', ['legal' => $lawyerApproval])</div></section>
      @include('masters::Legal._contract_details', ['legal' => $lawyerApproval, 'oldContractList' => $oldContractList, 'dmy' => $dmy, 'paidDays' => $paidDays, 'overDueFrom' => $overDueFrom, 'rentAmount' => $rentAmount])
      @include('masters::Legal._documents', ['legal' => $lawyerApproval])
    </div>
    <aside>
      <section class="lg-card" aria-label="Case timeline">@include('masters::Legal._timeline')</section>
      @include('masters::Legal._stage_notes', ['notes' => $notes])
    </aside>
  </div>
</div>
<div class="modal lg lg-modal" id="myModal"></div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.approve').on('click', function(e) {        

      var action_key =  $(this).attr('data-key');
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      var are_status = $(this).attr('data-status');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       

        });
    /**********************************************************************************/
    $('.referback').on('click', function(e) {        

      var action_key =  $(this).attr('data-key');
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      var are_status = $(this).attr('data-status');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       
          
        });
    /**********************************************************************************/
    $('.close_type').on('click', function(e) {        
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('legalCaseClose')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'legal_id' : legal_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
               // $("#myModal").html(response);
               // alert(response);
               // window.location.href = response;
               location.reload();
             },
           });
        return true;
        /*}else {
            return false;
          } */       

        });
    /**********************************************************************************/
  });
</script>
@endsection
