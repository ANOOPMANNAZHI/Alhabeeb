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
      <div class="page-title">Sub Work</div>
    </div>
     {{ (isset($subWork))?   Breadcrumbs::render('subWork.edit',$subWork,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('subWork.create') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form action="{{ !isset($subWork)? route('subWork.store'): route('subWork.update',$subWork->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($subWork)){{method_field('PUT')}}@endif
        <input type="hidden" name="backurl" value="{{isset($previousUrl)? $previousUrl:''}}" >
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">

          <div class="row">
            <div class="col-sm-12 col-md-6">
             <div class="form-group">
               <label for="work">Work<small class="textRed">*</small></label>
               <div class="p-relative">
				    <i class="fa fa-tasks icn-add" aria-hidden="true"></i>
               <select class="form-control" id="works_id" name="works_id" required="">
                <option value="">Select Work Code</option>
                @foreach($Work as $method)
                <option {{isset($subWork)?(($subWork->works_id== $method->id)?'selected':''):''}} value={{$method->id}}>{{$method->works_code}}</option>
                @endforeach
              </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="sub_work">Sub Work<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="sub_work"  placeholder="Enter Sub Work" name="sub_work" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($subWork)?  old('works_code',$subWork->sub_work): old('sub_work')}}" data-rule-maxlength="50" data-msg-maxlength="Maximum 50 Characters Allowed">
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
