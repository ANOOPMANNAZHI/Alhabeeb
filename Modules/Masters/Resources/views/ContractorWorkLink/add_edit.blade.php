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
              <div class="page-title">Contractor Work Link</div>
          </div>
          {{ (isset($workLink))?   Breadcrumbs::render('workLink.edit',$workLink,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('workLink.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($workLink)? route('workLink.store'): route('workLink.update',$workLink->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($workLink)){{method_field('PUT')}}@endif
<input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label>Vendor<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-venus-mars icn-add" aria-hidden="true"></i>
                <select class="form-control" name="vendor_id" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                    <option {{ isset($workLink)? ((old('vendor_id',$workLink->vendor_id) == $vendor->id)? 'selected' : '') : ''}} value="{{$vendor->id}}" >{{$vendor->vendor_name}}</option>
                    @endforeach
                </select>
              </div>
            </div> 
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Work<small class="textRed">*</small></label>
                 <div class="p-relative">
                <i class="fa fa-briefcase icn-add" aria-hidden="true"></i>
                <select class="form-control" name="work_id" required>
                    <option value="">Select Work</option>
                    @foreach($works as $work)
                    <option {{ isset($workLink)? ((old('work_id',$workLink->work_id) == $work->id)? 'selected' : '') : ''}} value="{{$work->id}}" >{{$work->works_code}}</option>
                    @endforeach
                </select>
              </div>
            </div> 
        </div>
         
       <div class="w-100">
         
       </div>
        
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
