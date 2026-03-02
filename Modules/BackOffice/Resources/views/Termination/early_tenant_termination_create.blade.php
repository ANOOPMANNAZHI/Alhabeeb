@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Early Termination Request</div>
    </div>
    {{   Breadcrumbs::render('tenantTermination.create') }}  



  </div>
</div>


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->

<div class="row">  
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     

          <!-- --------------------------  Div Starts ----------------------------------- -->

          <div class="clearfix"></div>
          <div class="dataSearchBox">
            <div class="row">
              <!--Property Section starts -->
              <div class="card-body row">
               <!-- <header>Property Section</header> -->
               <div class="col-sm-6">
                <div class="form-group">
                  <label for="tenant_contract_no">Agreement No<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                   <input required type="text" autocomplete="off" class="form-control" id="tenant_contract_no"  name="tenant_contract_no" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->tenant_contract_no : ''}}"  placeholder="Enter Agreement No">
                   <input type="hidden"  name="tenant_contract_id" id="tenant_contract_id" value="{{isset($tenantTermination)? $tenantTermination->contract_id : ''}}">


                   <select id="tenant_contracts_id" class="form-control tenant_contracts_id" name="tenant_contracts_id" style="display: none">
                     <option value="">Select Contract</option>
                     @if(isset($tenantTermination))
                     <option selected value="{{$tenantTermination->tenantContract->unit->id}}">{{$tenantTermination->tenantContract->unit->unit_code}}</option>
                     @endif
                   </select>
                 </div>
               </div>
             </div>
             <div class="col-sm-6">
              <div class="form-group">
                <label for="building_id">Building Name <small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="icon icon-building" aria-hidden="true"></i>
                 <input required type="text" class="form-control" id="building"  name="building" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->building->building_name : ''}}"  placeholder="Enter Building Name">
                 <input type="hidden"  name="building_id" id="building_id" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->building->id : ''}}">
               </div>
             </div>
           </div>
           <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_id">Unit <small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="icon icon-unit" aria-hidden="true"></i>
               <select id="unit_id" class="form-control unit_id" name="unit_id" style="display: none">
                 <option value="">Select Unit</option>
                 @if(isset($tenantTermination))
                 <option selected value="{{$tenantTermination->tenantContract->unit->id}}">{{$tenantTermination->tenantContract->unit->unit_code}}</option>
                 @endif
               </select>

               <input required type="text" class="form-control" id="unit"  name="unit" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->unit->unit_name : ''}}"  placeholder="Enter Unit Name">

               <input type="hidden" class="units_id" name="units_id" id="units_id" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->unit->id : ''}}">

             </div>
           </div>
         </div>
         <div class="col-sm-6">
          <div class="form-group">
            <label for="vendor_id">Tenant Name<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="icon icon-tenant" aria-hidden="true"></i>
             <input required type="text" class="form-control" id="tenant_id"  name="tenant_id" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->tenant->tenant_name : ''}}"  placeholder="Enter Tenant Name">

             <input type="hidden"  name="tenants_id" id="tenants_id" value="{{isset($tenantTermination)? $tenantTermination->tenantContract->tenant->id : ''}}">
           </div>
         </div>
       </div>

       <!--Property Section  ends -->

     </div>
   </div>
   <form method="post" autocomplete="off" id="termination-form" action="{{isset($tenantTermination)? route( 'tenantTermination.update',$tenantTermination->id) : route( 'tenantTermination.store')}} " data-toggle="validator">
    {{csrf_field()}} @if(isset($tenantTermination)){{method_field('PUT')}}@endif
    <div id="agreementDetail"></div>
  </form>
</div>
</div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
@include('backoffice::Termination.tenant_termination_js')





@endsection
