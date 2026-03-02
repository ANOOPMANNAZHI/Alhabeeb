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
      <div class="page-title">Building Insurance</div> 
    </div>
    {{ (isset($insurance))?   Breadcrumbs::render('building-insurance.edit',$insurance,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('building-insurance.create',$backUrlBreadCrumb,$backIdBreadCrumb ) }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form action="{{ !isset($buildingInsurance)? route('building-insurance.store'): route('building-insurance.update',$buildingInsurance->id)}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data"  >
        {{csrf_field()}} @if(isset($buildingInsurance)){{method_field('PUT')}}@endif
        <input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_company">Insurance Company<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="insurance_company"  placeholder="Enter Insurance Company" name="insurance_company" required  value="{{ isset($buildingInsurance)?  old('insurance_company',$buildingInsurance->insurance_company): old('insurance_company')}}" >
                </div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="building_id">Building <small class="textRed">*</small> </label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="buildings_id"  name="buildings_id" required>
                    <option value="">Select Building</option>
                    @foreach($buildings as $building)
                    <option  {{(old('building_id', isset($buildingInsurance)?  $buildingInsurance->building_id : $backIdBreadCrumb) == $building->id) ? 'selected' : '' }} value="{{$building->id}}">{{$building->building_name}}</option>
                    @endforeach                  
                  </select> 
                  <input type="hidden" name="building_id" value="{{old('building_id', isset($buildingInsurance)?  $buildingInsurance->building_id : $backIdBreadCrumb) }}" id="building_id">
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_policy_type">Insurance Policy Type<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="insurance_policy_type"  placeholder="Enter Insurance Policy Type" name="insurance_policy_type" required value="{{ isset($buildingInsurance)?  old('insurance_policy_type',$buildingInsurance->insurance_policy_type): old('insurance_policy_type')}}" >
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_building_value">Insurance Premium<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="insurance_premium_value"  placeholder="Enter Insurance Premium" name="insurance_premium_value" required value="{{ isset($buildingInsurance)?  old('insurance_premium_value',$buildingInsurance->insurance_premium_value): old('insurance_premium_value')}}" pattern="^[1-9]\d*(\.\d+)?$" maxlength="25">
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_start">Valid From<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="date" class="form-control" id="insurance_start"  placeholder="Enter Valid From" name="insurance_start" required value="{{ isset($buildingInsurance)?  old('insurance_start',$buildingInsurance->insurance_start->format('Y-m-d')): old('insurance_start')}}" >
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_end">Valid To<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="date" class="form-control" id="insurance_end"  placeholder="Enter Valid To" name="insurance_end" required value="{{ isset($buildingInsurance)?  old('insurance_end',$buildingInsurance->insurance_end->format('Y-m-d')): old('insurance_end')}}" >
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_start">Insured By<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <select class="form-control" id="insurance_insured_by"  name="insurance_insured_by" required>
                    <option value="">Select Insured By</option>
                    <option value="Landlord" {{(old('insurance_insured_by', isset($buildingInsurance)?  $buildingInsurance->insurance_insured_by : 0) == "Landlord") ? 'selected' : '' }}>Landlord</option>
                    <option value="Al-habib" {{(old('insurance_insured_by', isset($building_amentity)?  $buildingInsurance->insurance_insured_by : 0) == "Al-habib") ? 'selected' : '' }}>Al-habib</option>

                  </select> 
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_building_value">Insurance Building Value<small class="textRed">*</small></label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="number" class="form-control" id="insurance_building_value"  placeholder="Enter Insurance Building Value" name="insurance_building_value" required value="{{ isset($buildingInsurance)?  old('insurance_building_value',$buildingInsurance->insurance_building_value): old('insurance_building_value')}}" min="1" pattern="^[1-9]\d*(\.\d+)?$" maxlength="25">
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_debit_acc">Debit Acc</label>
                <div class="p-relative">
                  <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
                  <input  type="text" class="form-control" id="insurance_debit_acc"  placeholder="Enter Debit Acc" name="insurance_debit_acc" value="{{ isset($buildingInsurance)?  old('insurance_debit_acc',$buildingInsurance->insurance_debit_acc): old('insurance_debit_acc')}}" >
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="insurance_img_copy">Upload</label>
                <div class="p-relative">
                  <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                  <input type="file" class="form-control"  id="insurance_img_copy"  name="insurance_img_copy" data-rule-extension="jpeg|jpg" data-msg-extension="Only allowes jpeg|jpg">
                  @if(isset($buildingInsurance->insurance_path_thumbnail))
                  <div class="card-body row">
                    <div id="aniimated-thumbnials" class="list-unstyled  clearfix">

                      <div class="balance m-b-20"> 
                        <a href="{{asset('storage/app/'.$buildingInsurance->insurance_img_copy)}}" data-sub-html=" Images">
                        <img class="img-fluid img-thumbnail" target="_blank" src="{{asset('storage/app/'.$buildingInsurance->insurance_path_thumbnail)}}" > </a> </div>

                       </div>
                     </div>
                     @else
                     <p>No Uploads</p>
                     @endif
                   </div>
                 </div>
               </div>
                <input type="hidden" name="backUrlBreadCrumb" id="backUrlBreadCrumb" value="{{$backUrlBreadCrumb}}">
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

    //  $("#form_sample_2").validate();

    jQuery.validator.addMethod("greaterThan", 
      function(value, element, params) {

        if (!/Invalid|NaN/.test(new Date(value))) {
          return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
        || (Number(value) > Number($(params).val())); 
      },'Must be greater than Valid From.');
    $("#form_sample_2").validate({
      rules: {
        insurance_end: { greaterThan: "#insurance_start" 
      }
    }});
    /*******************************************************/
    var backUrlBreadCrumb =  $('#backUrlBreadCrumb').val();
    if(backUrlBreadCrumb == 'building.show'){
      $("#buildings_id").prop('disabled',true);
    }else{
      $("#buildings_id").prop('disabled',false);
    }
    $('#buildings_id').change(function(){
     var buildings_id = $("#buildings_id").val();
     alert(buildings_id);
     $("#building_id").val(buildings_id);
   });
  });
</script>
@endsection
