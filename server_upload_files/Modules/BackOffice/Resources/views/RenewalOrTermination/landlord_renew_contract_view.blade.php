@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Renewal Contract View</div>
        </div>
        @if($status == 1)
            {{ Breadcrumbs::render('landlordNewContractShow',$renewContract,$req_id,$status) }}
        @else 
            {{ Breadcrumbs::render('landlordNewContractApprovalShow',$renewContract,$req_id,$status) }}
        @endif
         
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
        
            <div class="card-head">
              <h4>
                <input type="hidden" name="request_id" id="request_id" value="{{$req_id}}">
                <input type="hidden" name="work_flow_processes_code" id="work_flow_processes_code" value="{{$requestDetails->work_flow_processes_code}}">
              @if($status == '1')                               
                  <button type="button" class="btn btn-circle btn-primary align-right move_to_approval" data-id="2" datas-id="MVN">Move To Approval</button>                
              @elseif($status == '2') 
                <button type="button" class="btn btn-circle btn-primary align-right accept" data-id="3" datas-id="ACPT" data-renew-id="{{$renewContract->id}}">Accept</button>
                <button type="button" class="btn btn-circle btn-primary align-right reject" data-id="1" datas-id="RJCT" data-renew-id="{{$renewContract->id}}">Reject</button>
                <button type="button" class="btn btn-circle btn-danger align-right terminate" data-id="5" datas-id="TMT" data-renew-id="{{$renewContract->id}}">Terminate</button>
              @endif
              </h4>
            
            </div>

            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Old Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_old_no}}</span></div>
                </div>
              </div>
               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_no}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Building Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->buildingInfo->building_name}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Vendor Name   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->vendorInfo->vendor_name}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Agreement Amount   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_amt}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Contact Address   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_address}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Duration </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_duration}} @if($renewContract->landlord_contract_duration_type==1){{'Month'}}@else 'Year'@endif</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Management Fee </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_management_fee}}</span></div>
                </div>
              </div>
              @if($renewContract->landlord_contract_cleaning_charge)
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Cleaning Charges </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{ ($renewContract->cleaning_charge_method == 1) ? $renewContract->landlord_contract_cleaning_charge.' %' : numberFormat($renewContract->landlord_contract_cleaning_charge).' OMR' }}</span></div>
                </div>
              </div>
              @endif
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Contract Percentage  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_percentage}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Muncipal Agreement No   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_amt}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Payment Type </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->paymentMethodInfo->payment_method_code}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Start Date</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($renewContract->start_date){{$renewContract->start_date->format('Y-m-d')}}@endif</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Note</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$renewContract->landlord_contract_note}}</span></div>
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

    $('.move_to_approval').on('click', function(e) {        

        var action_key = $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var process_flow = $("#work_flow_processes_code").val();
        if (confirm('Do you want to Move Next Stage?')) {
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
    $('.accept').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var new_contract_id = $(this).attr('data-renew-id');
        var process_flow = $("#work_flow_processes_code").val();
        if (confirm('Do you want to Approval Accept?')) {
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
    $('.reject').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status =$(this).attr('data-id');
        var new_contract_id = $(this).attr('data-renew-id');
        var process_flow = $("#work_flow_processes_code").val();
        if (confirm('Do you want to Reject?')) {
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
    $('.terminate').on('click', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var request_id = $("#request_id").val();
        var status = $(this).attr('data-id');
        var new_contract_id = $(this).attr('data-renew-id');
        var process_flow = $("#work_flow_processes_code").val();
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