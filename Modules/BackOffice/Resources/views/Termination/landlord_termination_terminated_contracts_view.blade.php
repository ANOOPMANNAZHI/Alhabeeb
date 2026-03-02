@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Terminated Landlord Contract View</div>
    </div>
    {{ Breadcrumbs::render('TerminatedContractLandlordView',$termination) }}
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
            <h5 class="details"><b>Agreement No :  </b><span>{{$termination->landlordContract->landlord_contract_no}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement Date  :  </b><span>{{isset($termination->landlordContract->created_at)?$termination->landlordContract->created_at->format('d/m/Y'):""}}</span></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$termination->landlordContract->vendorInfo->vendor_name}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$termination->landlordContract->vendorInfo->vendor_code}}</span></h5>
          </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name :  </b><span>{{$termination->landlordContract->buildingInfo->building_name}}</span></h5>
         </div>
       </div> 

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$termination->landlordContract->buildingInfo->building_code}}</span></h5>
       </div>
     </div> 

   </div>
 </div>

 <div class="sub-head">Payment Information</div>   
 <div class="dataSearchBox">  
  <div class="card-body row">
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Duration Type  :  </b><span>@if($termination->landlordContract->landlord_contract_duration == 1)
      Open
      @else
      Perpetual
      @endif</span></h5>
    </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Start Date :  </b><span>{{$termination->landlordContract->landlord_contract_valid_from_date->format('d/m/Y')}}</span></h5>
   </div>
 </div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Type  :  </b><span>{{$termination->landlordContract->managementTypeInfo->management_types_name}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Fee  :  </b><span>@if($termination->landlordContract->management_method==1)
    Percentage
    @elseif($termination->landlordContract->management_method==2)
    Amount
    @endif</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Type  :  </b><span>{{$termination->landlordContract->paymentMethodInfo->payment_method_code}}</span></h5>
 </div>
</div>
</div>

</div>
<div class="sub-head">Contract Details</div>   
<div class="dataSearchBox">  
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Valid From :  </b><span>{{$termination->landlordContract->landlord_contract_valid_from_date->format('d/m/Y')}}</span></h5>
     </div>
   </div>
   @if($termination->landlordContract->landlord_contract_valid_to_date != null)
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Valid To :  </b><span>{{$termination->landlordContract->landlord_contract_valid_to_date->format('d/m/Y')}}</span></h5>
     </div>
   </div>
   @endif
   @if($termination->landlordContract->landlord_contract_valid_to_date == null)
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Valid To :  </b><span>-</span></h5>
     </div>
   </div>
   @endif
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Marketing Executive :  </b><span>{{isset($termination->landlordContract->marketExecutiveEmployeeInfo->username)}}</span></h5>
   </div>
 </div> 
 <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Terminated Date :  </b><span>{{$termination->landlordContract->end_date->format('d/m/Y')}}</span></h5>
   </div>
 </div> 

</div>
</div>

</div></div>
</div>
@endsection
@section('scripts')
@endsection
