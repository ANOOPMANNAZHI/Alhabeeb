@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Renewal Contract View   </div>
        </div>
         {{ Breadcrumbs::render('renewedContractShow',$tenantContract) }}  
       

    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
          
          @if($tenantContract->tenant_renewal_termination_status == 2 || $tenantContract->tenant_renewal_termination_status == 4)
            <div class="card-head">
              <h4>
              @can('renewal_send_approval')
              <a href="{{route('sendApprovalFromRenewal',[$tenantContract->id,302,3,$oldContractDetail->id])}}" title="Send For Approval" class="btn btn-circle btn-primary align-right">
                  Send For Approval
              </a>
              @endcan
              @can('renewal_contract_edit')
               <a  href="{{route('tenantRenewal.edit',$tenantContract->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">
                  Edit
              </a>
              @endcan             
              </h4>
            </div>
             @endif 


<div class="card-body">     

<!----    Discussion Form                        ------ -->
@if(count($tenantContract->tenantContractOld->discussion) > 0)
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
                    @foreach($tenantContract->tenantContractOld->discussion  as $discussion)
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

@if(count($arNotes) > 0)
<div class="row">
<!--------     -->
 <div class="col-lg-12  ">
    <div class="card-body">      
     <h4><strong>Suggested Contract Type </strong><div class="clr"></div></h4>
       <div class="table-responsive1" style="background: #f7f7f7;">
                <table class="table" id="note_datatable">
                  <thead>
                    <tr >
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



   <div class="row">
  <div class="col-sm-12">
    <div class="card-box">

             <!--Agreement Section starts -->
         <div class="dataSearchBox">
          <div class="row">
            <div class="card-body row">
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
     </div>


     <!--Agreement Section ends -->
<div class="card-box">
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
                                        <th>Created By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>

                                  @forelse ($allNotes as $note)
                                 
                                   @if($note->renewal_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->renewal_notes}}</td>
                                        <td>{{$note->createdBy->username}} </td>
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



@if(url()->current() == route('renewedContract.edit',$tenantContract->id) )
<div class="row">
    <div class="col-sm-12">
        <div class="card card-box salesLeadBox">

        <div class="card-head">
            <div class="col"><h4>Register Contract</h4></div>
        </div>
       
         <div class="card-body"> 

          <div class="dataSearchBox ">
           <form method="post" action="{{route('renewedContract.save',$tenantContract->id)}}"  >
            {{csrf_field()}}
            <div class="row">            

               <div class="col-sm-12">
                <div class="form-group">
                    <label for="tenant_contract_old_no">Comment <small class="textRed">*</small></label>
                     <div class="p-relative">
                    <i class="fa icon-contract icn-add" aria-hidden="true"></i>
                    <textarea class="form-control" id="renewal_notes"   name="renewal_notes" required></textarea>                   
                </div>
                </div>
            </div>


             <div class="col-sm-6 ">
                  <span class="AccountSelectBox "> 
                    <input type="checkbox" class="group_ctrl" name="registered_in_municipality" value="1">
                    <label class="checkbox-label">Registered in Municipality</label>
                  </span>
              </div>

              <div class="col-sm-6">
                  <div class="form-group">
                       <button type="submit" class="btn btn-primary ">Save</button> 
                  </div>
             </div>
          </div>

            </form>
          </div>

          </div>
          
          </div>
        </div>
      </div>
      @endif





@endsection
@section('scripts')

@endsection