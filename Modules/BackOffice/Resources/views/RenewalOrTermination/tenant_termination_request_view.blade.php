@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Termination View</div>
        </div>
         {{ Breadcrumbs::render('tenantTermination.show',$tenantTermination) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
        
            <div class="card-head">
              <h4>
                
                
              <input type="hidden" name="request_id" id="request_id" value="{{$tenantTermination->id}}">
              <button type="button" class="btn btn-circle btn-primary align-right accept" data-id="1" datas-id="" >Accept</button>
              <button type="button" class="btn btn-circle btn-warning align-right renewal" data-id="5" datas-id="renewal" >Renewal</button>
              </h4>
            
            </div>

            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 

               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Contract  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenantTermination->tenantContract->tenant_contract_no}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($tenantTermination->status==0){{ 'Request' }}@else{{ 'New Contract' }}@endif</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Renewal Type  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenantTermination->renewal_or_termination_request_type}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenantTermination->renewal_or_termination_request_note}}</span></div>
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
        //var new_contract_id = $(this).attr('data-renew-id');
        if (confirm('Do you want to Approval Accept?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'request_id' : request_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
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
    $('.renewal').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var new_contract_id = $(this).attr('data-renew-id');
         if (confirm('Do you want to Renewal?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'new_contract_id' : new_contract_id,'action_key' : action_key,'request_id' : request_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
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