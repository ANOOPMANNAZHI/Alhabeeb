@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Tenant Renewal Contract Approval View
      </div>
    </div>
    {{ Breadcrumbs::render('newContractApprovalShow',$tenantContract,302) }} 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">

      <div class="card-head">
        <h4>
          @if($tenantContract->tenant_renewal_termination_status == 3)
          @can('renewal_contract_approve')
          <button title="Approve" type="button" class="btn btn-circle btn-primary align-right Approve" data-toggle="modal" data-target="#myModal" data-old-id="" data-new-id="{{$tenantContract->id}}" data-id="5" datas-id="301" data-act-key="ACPT" data-backdrop="static" data-keyboard="false">
           Approve
         </button>
         @endcan
         @can('renewal_contract_reject')
         <button title="Reject" type="button" class="btn btn-circle btn-danger align-right Reject" data-toggle="modal" data-target="#myModal" data-old-id="" data-new-id="{{$tenantContract->id}}" data-id="4" datas-id="300" data-act-key="RJCT" data-backdrop="static" data-keyboard="false">
           Reject
         </button>
         @endcan
         @endif 
       </h4>
     </div>




     <!--Agreement Section starts -->

 <div class="card-body"> 

<!----    Discussion Form                        ------ -->
@if(count($tenantContract->discussion) > 0)
<div class="row">  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-yellow">
              <div class="ribbon"><span>Discussion</span></div>
               Renewal/ Vacating Discussion Forum </header>
            <div class="panel-body light-green">
                <table class="table display product-overview mb-30">
                <thead class="background-red">
                    <tr>                       
                        <th> Discussion Type</th>
                        <th>Comment</th>
                        <th>Comment By </th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody class="background-white">
                    @foreach($tenantContract->discussion  as $discussion)
                     <tr>                       
                        <td>{{$discussion->category->category}}</td>
                        <td>{{$discussion->discussion}}</td>
                        <td>{{($discussion->user->user_type == 'admin') ? ucwords($discussion->user->username) : ucwords($discussion->user->employee->employee_name) }}</td>
                        <td>{{$discussion->created_at->format('d/m/Y')}}</td>              
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif


      

<!--------     -->
@if(count($arNotes) > 0)
<div class="row">
 <div class="col-lg-12  ">
   
    <div class="card-body">      
     <h4><strong>Suggested Contract Type </strong><div class="clr"></div></h4>
        <div class="table-responsive1" style="background: #f7f7f7;">
                <table class="table" id="note_datatable">
                  <thead>
                    <tr>
                      <th>Contract Type</th>
                      <th>Comment</th>
                      <th>Date & Time</th>
                      <th>Commented By</th>
                    </tr>
                  </thead>
                  <tbody>
                   @foreach ($arNotes as $arNote)
                   <tr>
                   <td>{{$arNote->renewalType->type}} </td>
                   <td>{{$arNote->renewal_notes_note}} </td>
                   <td class="d-t">{{$arNote->created_at->format('d/m/Y h:m A')}}</td>
                   <td>{{(!empty($arNote->createdBy->employee->employee_name))? $arNote->createdBy->employee->employee_name : $arNote->createdBy->username}} </td>
                 </tr>
                @endforeach
                </tbody>
            </table>  
    </div>
  </div>
</div> 
</div>
@endif


</div>

      
    </div>
  </div>
</div>



  
 
<!--Agreement Section ends -->
<div class="card-box">
<!-----          ------>
 <div class="col-lg-12  "> 
        <div class="card-head row">
         @if($tenantContract->tenant_contract_no)
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
         </div>
       </div> 
       @endif
       @if($tenantContract->created_at)
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Agreement Date :  </b><span>{{$tenantContract->created_at->format('d/m/Y')}}</span></h5>
       </div>
     </div> 
     @endif
   </div>
 </div>
 
<!-- Status Ribbon Starts -->
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-blue">
                <div class="ribbon"><span>Status</span></div>
               Status </header>
            <div class="panel-body light-green">
            <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->tenant_status_name}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContract->tenant_contract_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->unit->vacant_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
                    @if(!empty($tenantContract->unit->key)){{$tenantContract->unit->key->status_name}}
                    @else
                    NA
                    @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remaining days to Expiry :  </b><span>{{$remainingDays}} Days</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent paid Up To:  </b><span>
                     @if(!empty($tenantContract->tenant_contract_last_paid_date))
                     {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
                     @else
                     NA
                     @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> </b><span></span></h5>
                </div>
            </div>
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->

  <div class="card-head">
   <header>Building Details</header></div>
   <form action="#" id="form_sample_2" class="form-horizontal">
    <div class="card-body row">
      @if($tenantContract->building_id)
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
       </div>
     </div> 
     @endif
     @if($tenantContract->building_id)
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
     </div>
   </div> 
   @endif
   @if($tenantContract->unit_id)
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
   </div>
 </div>
 @endif
 @if($tenantContract->unit_id)
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant->tenant_name)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
@endif
@if($tenantContract->tenant->tenant_code)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->unit_usage)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
 </div>
</div>
@endif
@if(isset($tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Name :  </b><span>{{$tenantContract->occupant->occupant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($tenantContract->occupant->occupant_primary_contact_no))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Mob No:  </b><span>{{$tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
 </div>
</div>
@endif

@if(isset($tenantContract->occupant->occupant_email))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Email:  </b><span>{{$tenantContract->occupant->occupant_email ?? "NA"}}</span></h5>
 </div>
</div>
@endif
</div>
</form>
</div>

<div class="card-box">
  <div class="card-head">
    <header>Contract Details</header>
  </div>
  <div class="card-body row">
    @if($tenantContract->tenant_contract_start_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_effective_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_valid_to_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Duration  :  </b>
     <span>
      @php
      $duration = $tenantContract->tenant_contract_duration_countdown;
      $count = explode('-',$duration);
      @endphp
      @if(!empty($duration))
      {{$count[0]}} Year {{$count[1]}}Month {{$count[2]}}Day
      @endif
    </span></h5>
  </div>
</div>
@if(isset($tenantContract->tenant_contract_rent) && isset($tenantContract->tenant_contract_duration))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{numberFormat($tenantContract->tenant_contract_rent * $tenantContract->tenant_contract_duration) }} OMR</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_rent)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent (PM) :  </b><span>{{ numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
 </div>
</div>
@endif
<!-- <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?number_format($tenantContract->vaccant_date, 3):"NA"}}</span></h5>
 </div>
</div> -->

<!-- @if($tenantContract->tenant_contract_rent)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent Paid by Prevoius Tenant :  </b><span>{{ numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
 </div>
</div>
@endif -->
@if(isset($tenantContract->tenant_contract_muncipality_agr_no))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
 </div>
</div>
@endif
@if(isset($tenantContract->tenant_contract_electric_water))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Deposit Electric/Water :  </b><span>{{$tenantContract->tenant_contract_electric_water}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
 </div>
</div>
@endif
<!-- @if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
 </div>
</div>
@endif -->
<!-- @if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>PDC :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
 </div>
</div>
@endif -->
<!-- @if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Invoice Generate :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
 </div>
</div>
@endif -->
<!-- @if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Registered in Municipality :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
 </div>
</div>
@endif -->
</div>
</div>

<div class="card-box">
  <div class="card-head">
    <header>Payment Details</header>
  </div>
  <div class="card-body row">

    @if($tenantContract->tenant_contract_deposit_amt)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{numberFormat($tenantContract->tenant_contract_deposit_amt)}} OMR</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_guarantee_cheque_details)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{numberFormat($tenantContract->tenant_contract_guarantee_cheque_details)}} OMR</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_receipt_no)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt No :  </b><span>{{$tenantContract->tenant_contract_receipt_no}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_receipt_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt Date :  </b><span>{{$tenantContract->tenant_contract_receipt_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_receipt_amt)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt Amount :  </b><span>{{numberFormat($tenantContract->tenant_contract_receipt_amt)}} OMR</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_contract_receipt_amt)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Last Rent Paid :  </b><span>{{numberFormat($tenantContract->tenant_contract_receipt_amt)}} OMR</span></h5>
 </div>
</div>
@endif
</div>
</div>


<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Note</h4></div>
      </div>
      <div class="card-body">
        <div class="add-note-section">
          <form id="renewal_note-form" action="{{route( 'storeRenewalNote')}}"" method="POST">
            {{csrf_field()}}
            <div class="col-sm-12">
              <div class="form-group">
                <label for="simpleFormEmail">Renewal Note</label>
                <textarea class="form-control" rows="2" required name="renewal_note" placeholder="Enter Note" maxlength="200"></textarea>
                <input type="hidden" name="renewal_id" value="{{$getLastRenewalId->id}}">
                <input type="hidden" name="current_url" value="{{url()->current()}}">
              </div>
            </div>
            <div class="col-sm-2"><button type="submit" class="btn btn-primary">Save</button></div>
          </form>
          <div class="col-md-12">
            <div class="col p-0">
              <h4><strong></strong><div class="clr"></div></h4>

              <div class="table-responsive1">
                <table class="table" id="note_datatable">
                  <thead>
                    <tr style="background: #f5f5f5;">
                      <th>Progress</th>
                      <th>Date & Time</th>
                      <th>Created By</th>
                    </tr>
                  </thead>
                  <tbody>
                   @forelse ($renewalNotes as $renewalNote)
                   <tr @if (in_array('backoffice_executive', $roles) === true) 
                   bgcolor ="ADD8E6";
                   @elseif(in_array('are', $roles) ===true)
                   bgcolor = 'FFC0CB';
                   @elseif(in_array('super_admin', $roles) ===true)
                   bgcolor = '#ffcb80';
                   @else
                   bgcolor = 'FFF8DC';
                   @endif>
                   <td>{{$renewalNote->renewal_notes_note}} </td>
                   <td class="d-t">{{$renewalNote->created_at->format('d/m/Y h:m A')}}</td>
                   <td>{{$renewalNote->createdBy->username}} </td>

                 </tr>                        
                 @empty
                 <tr>
                  <td colspan="3" align="center">
                    <p>No Record</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>



<div class="row">
    <div class="col-sm-12">
        <div class="card card-box salesLeadBox">
        <div class="card-head">
            <div class="col"><h4>Renewal Stage Note</h4></div>
        </div>
         <div class="card-body">
         <div class="col">
                <div class="row">
          
                    <div class="col leadInformation">
                         <div class="table-responsive1">
                            <table class="table" >
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Stage</th>
                                        <th>Note</th>
                                        <th>Commented By</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>

                                  @forelse ($stageNotes as $note)
                                 
                                   @if($note->renewal_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->renewal_notes}}</td>
                                        <td>{{($note->updated_by > 0)? ( (!empty($note->updatedByUser->employee->employee_name))? $note->updatedByUser->employee->employee_name :   $note->updatedByUser->username ) : ((!empty($note->createdBy->employee->employee_name))? $note->createdBy->employee->employee_name :   $note->createdBy->username )}} 

                                        {{--(!empty($note->updated_by))? $note->updatedByUser :$note->createdBy->username --}}

                                        </td>
                                        <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
                                    </tr> 
                                    @endif                       
                                    @empty
                                    <tr>
                                        <td colspan="4" align="center">
                                        <p>No Record</p>
                                       </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
       
        </div>
    </div>
    </div>
</div>






</div>


</div>
</div>









<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {


    $('.Approve').on('click', function(e) {        

      var status =$(this).attr('data-id');
      var new_contract_id = $(this).attr('data-new-id');
      var stage =  $(this).attr('datas-id');
      var action_key =  $(this).attr('data-act-key');

      /*if (confirm('Do you want to Reject?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('renewalApproveReject')}}", // This is the url we gave in the route
            data: {'new_contract_id' : new_contract_id,'action_key' : action_key,'status' : status,'stage' : stage,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed

              $("#myModal").html(response);

            },
          });
        return true;       

      });
    $('.Reject').on('click', function(e) {        


      var status =$(this).attr('data-id');
      var new_contract_id = $(this).attr('data-new-id');
      var stage =  $(this).attr('datas-id');
      var action_key =  $(this).attr('data-act-key');

      /*if (confirm('Do you want to Reject?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('renewalApproveReject')}}", // This is the url we gave in the route
            data: {'new_contract_id' : new_contract_id,'action_key' : action_key,'status' : status,'stage' : stage,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed

              $("#myModal").html(response);

            },
          });
        return true;
        /*}else {
            return false;
          } */       

        });
    /***********************************************************/
    $("#myModal").on("hidden.bs.modal", function(){
      $("#myModal").html("");
      $(this).removeData('bs.modal');
    });

    /***********************************************************/

  });
</script>
@endsection