@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Landlord Renewed Contract View</div>
    </div>
     {{ Breadcrumbs::render('landlordContractShow',$renewedContract) }}
  </div>
</div>
<div class="row">
  <div class="col">
   <div class="card card-box salesSearchBox">
    <div class="sub-head">Agreement Details</div>
    <div class="dataSearchBox">
      <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No :  </b><span>{{$renewedContract->newLandlordContract->landlord_contract_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Old Agreement No :  </b><span>{{$renewedContract->oldLandlordContract->landlord_contract_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement Date  :  </b><span>{{$renewedContract->newLandlordContract->created_at->format('d/m/Y') ?? ''}}</span></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$renewedContract->newLandlordContract->vendorInfo->vendor_name ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$renewedContract->newLandlordContract->vendorInfo->vendor_code ?? ''}}</span></h5>
          </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name :  </b><span>{{$renewedContract->newLandlordContract->buildingInfo->building_name ?? ''}}</span></h5>
         </div>
       </div> 

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$renewedContract->newLandlordContract->buildingInfo->building_code ?? ''}}</span></h5>
       </div>
     </div> 

   </div>
 </div>

 <div class="sub-head">Payment Information</div>   
 <div class="dataSearchBox">  
  <div class="card-body row">
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Duration Type  :  </b><span>@if($renewedContract->newLandlordContract->landlord_contract_duration_type==1)Month
              @elseif($renewedContract->newLandlordContract->landlord_contract_duration_type==2)Year
              @else
              Day
              @endif</span></h5>
    </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Start Date   :  </b><span>@if($renewedContract->newLandlordContract->start_date){{$renewedContract->newLandlordContract->start_date->format('d/m/Y') ?? ''}}@endif</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Type  :  </b><span>{{$renewedContract->newLandlordContract->managementTypeInfo->management_types_name ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Fee  :  </b><span>{{$renewedContract->newLandlordContract->landlord_contract_management_fee ?? ''}}</span></h5>
   @if($renewedContract->newLandlordContract->landlord_contract_cleaning_charge)
   <h5 class="details"><b>Cleaning Charges  :  </b><span>{{ ($renewedContract->newLandlordContract->cleaning_charge_method ?? null) == 1 ? ($renewedContract->newLandlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($renewedContract->newLandlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
   @endif
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Type  :  </b><span>{{$renewedContract->newLandlordContract->paymentMethodInfo->payment_method_code ?? ''}}</span></h5>
  </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Amount :  </b><span>{{ numberFormat($renewedContract->newLandlordContract->landlord_contract_amt) ?? '' }} OMR</span></h5>
  </div>
</div> 
</div>

</div>
<div class="sub-head">Contract Details</div>   
<div class="dataSearchBox">  
  <div class="card-body row">
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid From :  </b><span>{{$renewedContract->newLandlordContract->landlord_contract_valid_from_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$renewedContract->newLandlordContract->landlord_contract_valid_to_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Free Lease Period :  </b><span>{{$renewedContract->newLandlordContract->landlord_free_lease_period ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Close Activity :  </b><span>{{$renewedContract->newLandlordContract->close_activity ?? ''}}</span></h5>
  </div>
</div>  
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Marketing Executive :  </b><span>{{$renewedContract->newLandlordContract->marketExecutiveEmployeeInfo->employee_name.'('.$renewedContract->newLandlordContract->marketExecutiveEmployeeInfo->employee_code.')' ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Remarks :  </b><span>{{$renewedContract->newLandlordContract->landlord_contract_note ?? ''}}</span></h5>
  </div>
</div> 

  </div>
</div>

</div></div>
</div> 
<!-- ends-->
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

                                  @forelse ($notes as $note)
                                 
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
<!-- ends-->
@endsection
@section('scripts')
@endsection