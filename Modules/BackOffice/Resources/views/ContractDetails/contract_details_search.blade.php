@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Building/Unit/Tenant</div>
    </div>
    {{   Breadcrumbs::render('contract-details.index') }}  



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
                <label for="building_id">Building Name <small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="icon icon-building" aria-hidden="true"></i>
                 <input required type="text" class="form-control" id="building"  name="building"  placeholder="Enter Building Name">
                 <input type="hidden"  name="building_id" id="building_id">
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
               </select>

               <input required type="text" class="form-control" id="unit"  name="unit"   placeholder="Enter Unit Name">

               <input type="hidden" class="units_id" name="units_id" id="units_id">

             </div>
           </div>
         </div>
         <div class="col-sm-6">
          <div class="form-group">
            <label for="vendor_id">Tenant Name<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="icon icon-tenant" aria-hidden="true"></i>

             <select id="tenant_id" class="form-control tenant_id" name="tenant_id" style="display: none">
                 <option value="">Select Tenant</option>
               </select>


             <input required type="text" class="form-control" id="tenant"  name="tenant"  placeholder="Enter Tenant Name">

             <input type="hidden"  name="tenants_id" id="tenants_id">
           </div>
         </div>
       </div>

       <!--Property Section  ends -->

     </div>
   </div>
    <div id="agreementDetail"></div>
  
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
@include('backoffice::ContractDetails.contract_details_js')





@endsection
