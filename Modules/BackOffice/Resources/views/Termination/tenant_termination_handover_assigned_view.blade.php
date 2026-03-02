@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Vacating Unit Inspection Details </div>
    </div>
    {{ Breadcrumbs::render('handoverAssignedView',$tenantContract) }} 
  </div>
</div>
<div class="row">
	@can('signature_view') 
	@if(empty($termination->termination_tenant_signature) && $termination->termination_review_status == 0)
	<div class="col-md-12">
   <div class="card-box">
    <div class="card-head">
      <h4>
       @if($termination->termination_review_status == 2)
       @can('termination_taken_over')
       <a href="{{route('tenantTerminationStage',[$termination->id,$termination->tenantContract->id,$termination->work_flow_processes_code,'ACPT'])}}" title="TakenOver" class="btn btn-circle btn-primary  align-right ">
        TakenOver
      </a>
      @endcan
      @endif
      @if(count($tenantContract->terminationChecklist)>0 && $termination->termination_review_status == 0)
      @can('termination_inspection_create')
      <a title="Edit" href="{{route('handoverAssignedInspectionEdit',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Edit 
      </a> 
      @endcan
      @endif
      @if($termination->termination_review_status == 1)
      @can('send_for_review')
      <a title="Send for Review" href="{{route('tenantTerminationReview',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Send for Review 
      </a> 
      @endcan
      @endif
      @if(!empty($termination->termination_tenant_signature))
      @can('termination_send_email')
      <a title="Email" href="{{route('inspectionSendMail',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Email
      </a> 
      @endcan
      @endif
    </h4>
  </div>

  <div class="card-body">
   <div class="dataSearchBox ">
     <iframe name="my_iframe" src="{{route('tenantTerminationSignature')}}" scroll="none" style="overflow: hidden;height: 350px; width:350px"></iframe>
     <input type="hidden" name="termination_Id" value="{{$termination->id}}">

   </div>

 </div>
</div>
</div>
@endif
@endcan
@can('image_upload')
@if(!empty($termination->termination_tenant_signature) && (empty($termination->termination_review_status == 0) || $termination->termination_review_status == null))

<div class="col-md-12">
	
  <div class="card card-box salesSearchBox">
   <div class="card-head">
    <h4>
     @if($termination->termination_review_status == 2)
     @can('termination_taken_over')
     <a href="{{route('tenantTerminationStage',[$termination->id,$termination->tenantContract->id,$termination->work_flow_processes_code,'ACPT'])}}" title="TakenOver" class="btn btn-circle btn-primary  align-right ">
      TakenOver
    </a>
    @endcan
    @endif
    @if(count($tenantContract->terminationChecklist)>0 && $termination->termination_review_status == 0)
    @can('termination_inspection_create')
    <a title="Edit" href="{{route('handoverAssignedInspectionEdit',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Edit 
    </a> 
    @endcan
    @endif
    @if($termination->termination_review_status == 1)
    @can('send_for_review')
    <a title="Send for Review" href="{{route('tenantTerminationReview',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Send for Review 
    </a> 
    @endcan
    @endif
    @if(!empty($termination->termination_tenant_signature))
    @can('termination_send_email')
    <a title="Email" href="{{route('inspectionSendMail',$termination->id)}}" class="btn btn-circle btn-primary  align-right">Email 
    </a> 
    @endcan
    @endif
  </h4>
</div>

<div class="dataSearchBox ">
  @else

  <div class="col-md-12"> 
    <div class="card card-box salesSearchBox ">
     <div class="dataSearchBox">


       @endif

       <!-- Image upload-->
       <div class="card-body"> 

        <div class="sub-head">Image Upload</div>
        <div class="dataSearchBox ">
          <form autocomplete="off" action="{{route('imageUpload')}}" method="POST" id="img_form" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
            {{csrf_field()}}
            <div class="row">
              <div class="col-sm-5">
                <div class="form-group">
                  <label for="building_img_category">Image Category</label>
                  <div class="p-relative">
                    <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                    <select class="form-control margin-top-8" id="termination_doc_type"  name="termination_doc_type[]" required>
                      <option value="">Select Category</option>                  
                      <option value="Electricity">Electricity</option>
                      <option value="Plumbing">Plumbing</option> 
                      <option value="Carpentry">Carpentry</option>
                      <option value="Others">Others</option>                               
                    </select>  
                  </div>              
                </div>
              </div>
              <div class="col-sm-5">
                <div class="form-group">
                  <label for="report_image_file_name">Image   <!-- <button type="button" class="btn btn-primary add_button">Add Multiple Image</button> --></label>
                  <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="report_image_file_name form-control "  id="report_image_file_name"  name="report_image_file_name[]" data-rule-extension="jpg|jpeg|png" data-msg-extension="Only allows jpg,jpeg and png" required>
                    <input type="hidden" name="termination_id" value="{{$termination->id}}">
                    <input type="hidden" name="termination_contract" value="{{$termination->contract_id}}">
                    <input type="hidden" name="work_flow_processes" value="{{$termination->work_flow_processes_code}}">

                  </div>
                </div>
              </div>
              <div class="col-sm-2">
                <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                <!--  <button type="button" class="btn btn-primary add_button">Add</button> -->
              </div>
              <div class="w-100"></div>
            </div>
            <div class="field_wrapper">

            </div>
            <div class="row">
              <div class="col-sm-1">
                <button type="submit" class="btn btn-primary">Upload</button>
              </div>
            </div> 

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endcan

<!-- ends-->
@can('termination_supervisor_comment')
@if(empty($termination->termination_notes))
<div class="col-md-12"> 
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Supervisor Note</h4></div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="card-box">
         <!--Property Section  ends -->
         <div class="clearfix"></div>
         <!--Agreement Section starts -->
         <div class="dataSearchBox">
           <form action="{{route('supervisorNoteStore')}}" method="POST" id="handover_note_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
             {{csrf_field()}}
             <div class="row">
              <div class="card-body row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <input type="hidden" name="termination_id" value="{{$termination->id}}">
                    <input type="hidden" name="current_url" value="{{url()->current()}}">
                    <label>Note<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <textarea class="form-control" rows="4" cols="50" id="supervisor_comment"  placeholder="Enter Note" name="supervisor_comment"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required></textarea>
                   </div>
                 </div> 
               </div>
             </div>
           </div>
           <div class="col-sm-12">
            <div class="row">
              <div class="col no-padding">
                <button type="submit" class="btn btn-primary">Save</button> 
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
</div>
@endif
@endcan
<!-- ends-->  
<div class="col-md-12"> 
 <div class="card card-box salesSearchBox ">
  <div class="dataSearchBox">
    <div class="card-body row"> 

     <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b> Contract No  </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->tenant_contract_no}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Building Name  </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->building->building_name}}</span></div>
      </div>
    </div>

    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Building No  </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->building->building_no}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Unit No </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->unit->unit_no}}</span></div>
      </div>
    </div> 
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Unit Type </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->unit->unit->unit_types_name}}</span></div>
      </div>
    </div> 
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Tenant Name  </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_name}}</span></div>
      </div>
    </div> 
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Mob No </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_contact_no}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Location</b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->building->location->locations_name??'NA'}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Way No</b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$tenantContract->building->building_address??'NA'}}</span></div>
      </div>
    </div>
  
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

<div class="dataSearchBox">    
  <div class="card-body row">
    @if(!empty($tenantContract->terminationContract->termination_takenover_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>TakeOver Date :  </b><span>{{ (!empty($tenantContract->terminationContract->termination_takenover_date)) ? $tenantContract->terminationContract->termination_takenover_date->format('d/m/Y') : "NA"}}</span></h5>
      </div>
    </div>
    @endif
    @if(!empty($tenantContract->terminationContract->termination_takenover_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Termination Date:  </b><span>{{(!empty($tenantContract->terminationContract->termination_date))? $tenantContract->terminationContract->termination_date->format('d/m/Y') : "NA"}}</span></h5>
      </div>
    </div>
    @endif

    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Tenancy Start Date:  </b><span>
         @if(!empty($tenancyStartDt->tenant_contract_start_date))

         {{$tenancyStartDt->tenant_contract_start_date->format('d/m/Y')}}
         @endif
         @if(empty($tenancyStartDt->tenant_contract_start_date))
         NA
         @endif
       </span></h5>
     </div>
   </div>
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Tenancy End Date:  </b><span>
         @if(!empty($tenantContract->tenant_contract_valid_to_date))

         {{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}
         @endif
         @if(empty($tenantContract->tenant_contract_valid_to_date))
         NA
         @endif
       </span></h5>
     </div>
   </div>

   @if(!empty($tenantContract->tenant_contract_duration_countdown))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Total Duration:  </b><span> 
		@if(isset($tenancyStartDt->tenant_contract_start_date) &&
          isset($tenantContract->tenant_contract_valid_to_date))

        @php 
        $duration = contractDurationCalculation($tenancyStartDt->tenant_contract_start_date, $tenantContract->tenant_contract_valid_to_date);
        @endphp
        
        {{$duration['year']}} Year
        {{$duration['month']}} Month
        {{$duration['day']}} Days
		@endif
		</span></h5>
      </div>
    </div>
    @endif 


  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Last Paid : </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? numberFormat($tenantContract->tenant_contract_last_paid_amt)." OMR":"NA"}}</span></h5>
    </div>
  </div>

  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>OutStanding Rent : </b>
        <span>
        {{($outstandingOs > 0)? numberFormat($outstandingOs)." OMR":"NA"}}
        </span>
      </h5>
    </div>
  </div>  

  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Assigned To : </b><span>{{$termination->assignedTo->employee->employee_name ?? "NA"}}</span></h5>
    </div>
  </div>  

 <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Previous Contract Deposit Cheque No: </b><span>{{isset($depositeCheque->pdc_check_no)?$depositeCheque->pdc_check_no:'N/A'}}</span></h5>
        </div>
      </div>  
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Previous Contract  Deposit Cheque Amount : </b><span>{{isset($depositeCheque->pdc_amt)?$depositeCheque->pdc_amt:'N/A'}}</span></h5>
        </div>
      </div>  
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Remark:  </b><span>{{$tenantContract->terminationContract->termination_remark?? "NA"}}</span></h5>
    </div>
  </div>
  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Supervisor Comment:  </b><span>{{$termination->supervisor_comment?? "NA"}}</span></h5>
    </div>
  </div>

</div>
</div>
<div class="dataSearchBox">
  <div class="card-body row">

    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Electricity Acc/No :  </b><span>{{$termination->termination_electricity_acc_no}}</span></h5>

      </div>
    </div>
    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Closing Reading  :  </b><span>{{$termination->termination_electricity_close_reading}}</span></h5>
      </div>
    </div>
    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Amount RO :  </b><span>{{isset($termination->termination_electricity_amount)? numberFormat($termination->termination_electricity_amount)." OMR":"NA"}}</span></h5>
      </div> 
    </div>
    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Water Acc/No :  </b><span>{{$termination->termination_water_acc_no}}</span></h5>

      </div>
    </div>
    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Closing Reading  :  </b><span>{{$termination->termination_water_close_reading}}</span></h5>
      </div>
    </div>
    <div class="col-lg-4 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Amount RO  :  </b><span>{{isset($termination->termination_water_amount)? numberFormat($termination->termination_water_amount)." OMR":"NA"}}</span></h5>        
      </div>
    </div>
  </div>
</div>


@if(count($groupedWork)> 0)


@foreach($groupedWork as $checklist)

<div class="sub-head">{{$checklist->first()->work->works_code}}</div>
<div class="dataSearchBox">
  <div class="card-body row">
   @foreach($checklist as $subWork)
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b> {{$subWork->subWorks->sub_work}}:  </b><span>{{$subWork->termination_amount}}</span></h5>

    </div>
  </div>
  @endforeach
</div>
</div>

@endforeach

@endif
@if(count($tenantContract->terminationChecklistOther)> 0)

<div class="sub-head">Others</div>
<div class="dataSearchBox">
  <div class="card-body row">
    @foreach($tenantContract->terminationChecklistOther as $other)
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>{{$other->termination_other_work}} :  </b><span>{{$other->termination_amount}}</span></h5>

      </div>
    </div>
    @endforeach

  </div>
</div>
</div>
@endif

@if(count($tenantContract->terminationChecklistOther)> 0 || count($groupedWork)> 0)

<div class="dataSearchBox">
  <div class="card-body row">

    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Total Amount :  </b><span>{{numberFormat($termination->termination_total_amount)}} OMR</span></h5>

      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Total of Electricity and Water  :  </b><span>{{isset($termination->termination_total_elec_water_amount)? numberFormat($termination->termination_total_elec_water_amount)." OMR":"NA"}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Discount on Total Maintenance Due:  </b><span>{{isset($termination->termination_discount_maintenance_due)? numberFormat($termination->termination_discount_maintenance_due)." OMR":"NA"}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Net Amount:  </b><span>{{isset($termination->termination_net_amount)? numberFormat($termination->termination_net_amount)." OMR":"NA"}} </span></h5>

      </div>
    </div>
  </div>
</div>


@endif
<!-- ends-->
</div>
</div>
@php $totalamt = 0;
foreach($tenantContract->terminationChecklistOther as $other) {
$total = $other->termination_amount;
$totalamt+= $total;
}
@endphp
@if(count($tenantContract->terminationChecklistOther)> 0 || count($groupedWork)> 0)
<div class="dataSearchBox">
  <div class="card-body row">

    <div class="col-lg-6 p-t-20">
      <div class = "txt-full-width">
        <h5 class="details"><b>Grand Total Of Others :  </b><span>{{numberFormat($totalamt)}} OMR</span></h5>

      </div>
    </div>
    </div>
  </div>
@endif
<!-- Document Show Starts -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Open For Termination Document List</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Sl No</th>
                <th>Document</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($openTerminationDocument as $key=>$document)
              <tr>
                <td>{{ ++$key }}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a></td>

              </tr>                     
              @empty
              <tr>
                <td colspan="3" >
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
<!-- Document Show Ends -->

<!--Current And Previous Contracts starts -->

  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Current And Previous Contracts</h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" >
              <thead>
                <tr style="background: #f5f5f5;">
                    <th>Agreement No</th>
                    <th>Name</th>
                    <th>Building</th>
                    <th>Unit</th>
                    <th>Start Dt</th>
                    <th>End Dt</th>
                    <th>Rent</th>
                    <th>OS</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($backHistory as $contract)
                <tr>
                    <td>{{$contract->tenant_contract_no}}</td>
                    <td>{{$contract->tenant->tenant_name}}</td>
                    <td>{{$contract->building->building_name}}</td>
                    <td>{{$contract->unit->unit_no}}</td>
                    <td>{{$contract->tenant_contract_start_date->format('d/m/Y')}}</td>
                    <td>{{$contract->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
                    <td>{{numberFormat($contract->tenant_contract_rent)}}</td>
                    <td>{{numberFormat($contract->tenant_contract_os)}}</td>
                    <td>
                       <a target="_blank" href="{{route('tenant-contract.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
						@if($contract->invoice_check !=null)
                        <a target="_blank" href="{{route('invoice.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="{{'Invoice'}}">
                            <i class="fa fa-files-o"></i>
                        </a>
						@endif
                        <a target="_blank" href="{{route('pdcView',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC">
                            <i class="fa fa-book"></i>
                        </a>
                        <a target="_blank" href="{{route('rentReceiptFromTermination',[$contract->id])}}" class="btn btn-tbl-view btn-xs" title="Receipt">
                            <i class="fa fa-files-o"></i>
                        </a>
                    </td>
                </tr>
                @empty 
                <tr>
                   <td colspan="9" align="center">
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
<!-- ends -->


<!-- Inspection Image Starts -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Inspection Image</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Category</th>
                <th>Document</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($terminationDocument as $document)
              
              <tr>
                <td>{{$document->termination_doc_type}}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a></td>

              </tr>                     
              @empty
              <tr>
                <td colspan="3" >
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

<!-- Inspection Image Ends -->

@endsection
@section('scripts')
@endsection
