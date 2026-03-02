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
      <div class="page-title">ARE Assign Detailed View</div>
    </div>
    {{ Breadcrumbs::render('areBuildingAssign.show') }} 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box"> 
      <div class="card-head">
        <h4>
          @if($areBuildingAssign->amc_schedule_task_status==0)
          <a title="Edit" href="{{route('areBuildingAssign.edit',$areBuildingAssign->id)}}" class="btn btn-circle btn-primary align-right" title="Edit">Edit
          </a>  
          @endif 
          <div class="clr"></div>
        </h4>
      </div>
      <form action="#" id="form_sample_2" class="form-horizontal">
        <div class="card-body row"> 


          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> ARE  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$areBuildingAssign->areUser->employee->employee_name}}</span></div>
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
