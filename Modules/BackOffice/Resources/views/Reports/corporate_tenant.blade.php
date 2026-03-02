@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Corporate Tenant (Multiple flats)</div>
    </div>
    {{ Breadcrumbs::render('showCorporateTenantReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
         @can('view_corporate_tenant_report') 
      <form action="{{route('corporateTenantReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        <div class="dataSearchBox ">
          
          <div class="row">
          <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type" required>
                  <option value="">Select Filter</option>                
                  <option value="1">Tenant Name</option>                
                  <option value="2">Tenant Code</option>               
                </select> 
              </div>               
            </div>
          </div> 
            <div class="col-sm-6">
              <div class="form-group">
                <label for="simpleFormEmail">Value<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="tenant_name"  placeholder="Enter Tenant Name" name="tenant_name">
                  <input  type="text" class="form-control" id="tenant_code"  placeholder="Enter Tenant Code" name="tenant_code">
                </div>
              </div>
            </div>  

             <div class="col-sm-6">
              <div class="form-group">
                <label for="download_type">Download Type<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="download_type"  name="download_type" required>
                  <option value="">Select Download Type</option>                
                  <option value="pdf">PDF</option>                
                  <option value="excel">Excel</option>               
                </select> 
              </div>               
            </div>
          </div>
          <div class="w-100"></div>
          
            <div class="w-100"></div>
            <div class="col">
              <div class="w-100"></div>
              <button type="submit" name="search" class="btn btn-primary">Generate</button>
            </div>
            
          </div>
          
        </div>
        <div class="clearfix"></div>
      </form>
        @endcan 
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
   $(document).ready(function() {
    $('#tenant_code').hide();

  $(document).on('change',"#filter_type", function(){
    var type = $("#filter_type").val();
    if(type == 1){
      $('#tenant_code').hide();
      $('#tenant_name').show();
      $('#vendor_name').val('');
      $('#tenant_code').val('');
      $('#tenant_name').val('');
      $('#tenant_name').prop('required',true);
      $('#tenant_code').prop('required', false);

    }else{
      $('#tenant_name').hide();
      $('#tenant_code').show();
      $('#vendor_name').val('');
      $('#tenant_code').val('');
      $('#tenant_name').val('');
      $('#tenant_code').prop('required',true);
      $('#tenant_name').prop('required', false);
    }
  });

//AutoComplete For Landlord/Vendor Name
 /*********************************************************************************/
 $('#tenant_name').autocomplete({
  source : '{!!URL::route('tenantCompanyAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#tenant_name").val('');
      $('#tenant_name-error').show();
    }
  }
});  
  //AutoComplete For Landlord/Vendor Name
 /*********************************************************************************/
 $('#tenant_code').autocomplete({
  source : '{!!URL::route('tenantCodeCompanyAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#tenant_code").val('');
      $('#tenant_code-error').show();
    }
  }
});

 });
</script>
@endsection
