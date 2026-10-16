@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $timeline = isset($timeline) ? $timeline : \Modules\Masters\Services\LegalCase::timeline($legalCase->id); @endphp
<div class="lg">
  @component('masters::Legal._detail_head', ['legal' => $legalCase, 'crumb' => 'legalCase.show', 'screen' => 'Legal case'])
    @slot('actions')
      @if((int) $legalCase->work_flow_processes_code === 804)
      @can('legal_are_stage')
      <button type="button" class="lg-btn lg-btn--primary are_stage" data-id="{{ $legalCase->id }}" data-flow-id="{{ (int) $legalCase->are_status === 1 ? 802 : 801 }}" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Send for verification</button>
      @endcan
      @endif
    @endslot
  @endcomponent
  <div class="lg-grid">
    <div>
      <section class="lg-card" aria-label="Overview"><div class="lg-card__head"><h2 class="lg-card__title">Overview</h2></div>
        <div class="lg-card__body">@include('masters::Legal._case_summary', ['legal' => $legalCase])</div></section>
      @include('masters::Legal._contract_details', ['legal' => $legalCase])
      @include('masters::Legal._documents', ['legal' => $legalCase])
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

    $('.are_stage').on('click', function(e) {        
        var legal_id = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
         var are_status = $(this).attr('data-status');
       /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('legalAreStage')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
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
 });
</script>
@endsection
