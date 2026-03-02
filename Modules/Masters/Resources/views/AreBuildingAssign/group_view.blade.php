@extends('layouts.plms-app')
@section('css')
<!-- gallery -->
<link href="{{asset('public/plugins/light-gallery/css/lightgallery.css')}}" rel="stylesheet">
@endsection
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">ARE View</div>
    </div>
    {{Breadcrumbs::render('groupView')}} 
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <div class="card-box"> 
      <div class="card-head">
            </div>
      <form action="#" id="form_sample_2" class="form-horizontal">
        <div class="card-body row"> 


          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> ARE  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$areBuildingAssign->areUser->username}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>From Date </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$areBuildingAssign->assign_from->format('d/m/Y')}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Buildings  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$areBuildingAssign->assignedBuildingNames->implode('building_name',', ')}}</span></div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')    
@endsection
