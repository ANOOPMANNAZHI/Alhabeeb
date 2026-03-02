@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Sub Work</div>
    </div>
    {{ Breadcrumbs::render('subWork.show',$subWork) }} 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      @can('edit_sub_work') 
      <div class="card-head">
        <h4>
        <a href="{{route('subWork.edit',[$subWork->id,'backurl'=>Route::currentRouteName(),'backid'=>$subWork->id])}}" class="btn btn-circle btn-primary  align-right">
            Edit
          </a>
        <div class="clr"></div>
      </h4>
    </div>
    @endcan  
    <form action="#" id="form_sample_2" class="form-horizontal">
      <div class="card-body row">
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Work Code  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$subWork->work->works_code}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Sub Work </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$subWork->sub_work}}</span></div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</div> 


@endsection
