@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Approval View</div>
    </div>
    {{ Breadcrumbs::render('LCTerminationApprovalView',$termination) }} 
  </div>
</div>
<div class="row">
  <div class="col">
   <div class="card card-box salesSearchBox">
    <h4>
     @if($termination->termination_type_status == 2)
     @can('lc_termination_verification_approve')
     <a href="{{route('landlordTerminationChangeStatus',[$termination->id,3])}}" title="Approve" class="btn btn-circle btn-primary align-right">
      Approve
    </a>
    @endcan
    @can('lc_termination_verification_reject')
    <a href="{{route('landlordTerminationChangeStatus',[$termination->id,4])}}" title="Reject" class="btn btn-circle btn-danger align-right">
      Reject
    </a>
    @endcan
    @endif
  </h4>
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
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Marketing Executive :  </b>
      <?php if(isset($termination->landlordContract->marketExecutiveEmployeeInfo)){?>
        <span>{{$termination->landlordContract->marketExecutiveEmployeeInfo->username}}</span>
      <?php }?>
    </h5>
   </div>
 </div> 

</div>
</div>

</div></div>
</div>
<!-- verification list starts-->
<div class="row">
  <div class="col-sm-12">
    <div class="card-box salesSearchBox">
      <table class="table display product-overview mb-30" id="dtBasicExample">
        <thead>
          <tr>
            <th>Agreement No</th>
            <th>Name</th>
            <th>Building</th>
            <th>Unit No</th>
            <th>To</th>
            <th>Status</th>
            <th>Rent</th>
            <th>OS</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($tenantContracts as $tenantContract)
          <tr>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{$tenantContract->tenant_contract_no}}</a></td>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{$tenantContract->tenant->tenant_name}}</a></td>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{$tenantContract->building->building_name}}</a></td>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{$tenantContract->unit->unit_no}}</a></td>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</a></td>
            <td>
              @if($tenantContract->tenant_contract_status == 1)
              <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>{{$tenantContract->tenant_contract_status_name}}</b></span>
              @else 
              <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>{{$tenantContract->tenant_contract_status_name}}</b></span>
              @endif
            </td>
            <td><a class="no-link" href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}">{{number_format($tenantContract->tenant_contract_rent, 3)}} OMR</a></td>                    
            <td></td>
            <td>
              <a href="{{route('LandlordApprovalByTenantPdcView',[$tenantContract->id,$termination->id,1])}}" title="PDC" class="btn btn-tbl-general btn-xs">
                <i class="fa fa-retweet"></i>
              </a>
              <a href="{{route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1])}}" title="View" class="btn btn-tbl-view btn-xs">
                <i class="fa fa-eye "></i>
              </a>
              <a href="{{route('LandlordApprovalByTenantInvoiceView',[$tenantContract->id,$termination->id,1])}}" title="Invoice" class="btn btn-tbl-violet btn-xs">
                <i class="fa fa-life-ring"></i>
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
<!-- verification list ends -->
@endsection
@section('scripts')
@endsection