@extends('layouts.plms-app')

@section('content')
	<!-- start widget --> 
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Unit Utility</div>
          </div>
         
           {{ (isset($unit_utility))?   Breadcrumbs::render('unit-utility.edit',$unit_utility,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('unit-utility.create') }}         
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

<form action="{{ !isset($unit_utility)? route('unit-utility.store'): route('unit-utility.update',$unit_utility->id)}}" method="POST" id="form_sample_2" class="form-horizontal" >
  
{{csrf_field()}} @if(isset($unit_utility)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >
<div class="dataSearchBox ">
    
        <div class="row">

          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_code">Home Utility <small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-home icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="home_utility_id"  name="home_utility_id" required>
                  <option value="">Select Home Utility</option>
                    @foreach($homeUtilities as $homeUtility)
                     <option  {{(old('home_utility_id', isset($unit_utility)?  $unit_utility->home_utility_id : 0) == $homeUtility->id) ? 'selected' : '' }} value="{{$homeUtility->id}}">{{$homeUtility->home_utilities_code}}</option>
                    @endforeach                  
                </select> 
                </div>               
            </div>
          </div>          

          <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_id">Unit<small class="textRed">*</small> </label>
                 <div class="p-relative">
                <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="unit_id"  name="unit_id" required>
                  <option value=""> Select Unit </option>
                    @foreach($units as $unit)
                     <option  {{(old('unit_id', isset($unit_utility)?  $unit_utility->unit_id : 0) == $unit->id) ? 'selected' : '' }} value="{{$unit->id}}">{{$unit->unit_code}}</option>
                    @endforeach                  
                </select> 
                </div>               
            </div>
          </div>


         <div class="col-sm-6">
            <div class="form-group">
                <label for="amc_contract_no">AMC Contract No. <small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-phone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="amc_contract_no"  name="amc_contract_no" required  value="{{ old('amc_contract_no', isset($unit_utility)?  $unit_utility->amc_contract_no : '' )}}" placeholder="Enter AMC Contract No.">
              </div>
            </div>
          </div>

          <div class="col-sm-12">
            <div class="form-group">
                <label for="unit_utilities_remark">Remark </label>
                 <div class="p-relative">
                <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea  class="form-control" id="unit_utilities_remark"  name="unit_utilities_remark" placeholder="Enter Remark"   >{{ old('unit_utilities_remark', isset($unit_utility)?  $unit_utility->unit_utilities_remark : '' )}}</textarea>
              </div>
            </div>
          </div> 

        
          
       <div class="w-100"></div>

        <div class="col">
          <div class="w-100"></div>
              <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
           
      </div>
    
</div>
<div class="clearfix"></div>
</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
  });
</script>
@endsection
