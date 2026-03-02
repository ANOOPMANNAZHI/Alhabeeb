@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Renewal Views</div>
        </div>
         {{ Breadcrumbs::render('landlordRenewal.show',$landlordRenewal) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
        
            <div class="card-head">
              <h4>
                
                
              <input type="hidden" name="request_id" id="request_id" value="{{$landlordRenewal->id}}">
              <button type="button" class="btn btn-circle btn-primary align-right accept" data-id="1" datas-id="ACPT" data-flow-id="{{$landlordRenewal->work_flow_processes_code}}" >Accept</button>
              <button type="button" class="btn btn-circle btn-danger align-right terminate" data-id="5" datas-id="TMT" data-flow-id="{{$landlordRenewal->work_flow_processes_code}}">Terminate</button>
              </h4>
            
            </div>

            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 

               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Contract  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$landlordRenewal->landlordContract->landlord_contract_no}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($landlordRenewal->status==0){{ 'Request' }}@else{{ 'New Contract' }}@endif</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Renewal Type  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$landlordRenewal->renewal_or_termination_request_type}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$landlordRenewal->renewal_or_termination_request_note}}</span></div>
                </div>
              </div>
            </div>
            

            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
@section('scripts')
<script>
$(document).ready(function() {

    $('.accept').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
        //var new_contract_id = $(this).attr('data-renew-id');
        if (confirm('Do you want to Approval Accept?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'request_id' : request_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                //$("#myModal").html(response);
               window.location.href = response;
            },
        });
        return true;
        }else {
            return false;
        }        
         
    });
    $('.terminate').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var new_contract_id = $(this).attr('data-renew-id');
        var process_flow = $(this).attr('data-flow-id');
         if (confirm('Do you want to Terminate?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'new_contract_id' : new_contract_id,'action_key' : action_key,'request_id' : request_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                //$("#myModal").html(response);
               window.location.href = response;
            },
        });
        return true;
        }else {
            return false;
        }        
         
    });
});
</script>
@endsection