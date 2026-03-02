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
              <div class="page-title">Work</div>
          </div>
           {{ (isset($work))?   Breadcrumbs::render('work.edit',$work,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('work.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($work)? route('work.store'): route('work.update',$work->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($work)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Code<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Work Code" name="works_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($work)?  old('works_code',$work->works_code): old('works_code')}}" data-rule-maxlength="20" data-msg-maxlength="Maximum 20 Characters Allowed">
              </div>
            </div>
          </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Work Type<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-briefcase icn-add" aria-hidden="true"></i>
                <select class="form-control" name="works_type" required>
                    <option  value="">Select Work Type</option>
                    <option {{ isset($work)? ((old('works_type',$work->works_type) == 0)? 'selected' : '') : ''}} value="0">Both</option>
                    <option {{ isset($work)? ((old('works_type',$work->works_type) == 1)? 'selected' : '') : ''}} value="1">Maintenance</option>
                    <option {{ isset($work)? ((old('works_type',$work->works_type) == 2)? 'selected' : '') : ''}} value="2">AMC</option>
                </select>
                </div>
            </div> 
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Acc Code<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-briefcase icn-add" aria-hidden="true"></i>
                <select class="form-control select2" name="acc_code" required>
                    <option  value="">Select Acc COde</option>
                     <?php foreach($acc_codes as $code){?>
                       
                       <option  {{(old('acc_code_id', isset($work)?  $work->acc_code_id : 0) == $code->id) ? 'selected' : '' }} value="{{$code->id}}">{{$code->acc_code_val}}-{{$code->acc_code_desc}}</option>

                     <?php }?>
                </select>
                </div>
            </div> 
        </div>
        <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormEmail">Description</label>
                <div class="p-relative">
                <i class="fa fa-files-o icn-add" aria-hidden="true"></i>
               <textarea name="works_desc" class="form-control" placeholder="Enter Work Description">{{ isset($work)?  old('works_desc',$work->works_desc): old('works_desc')}}</textarea>
             </div>
                
            </div>
        </div>
         
       <div class="w-100"></div>
         
       
        
        <div class="col">
          <div class="w-100"></div>
              <button type="submit" class="btn btn-primary">Save</button>
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
