@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Amenity Type</div>
        </div>
         {{ Breadcrumbs::render('amentityType.show', $amentityType) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
			@can('edit_amentity_type')
			 <div class="card-head">
            <h4>
			    <a href="{{route('amentityType.edit',[$amentityType->id,'backurl'=>Route::currentRouteName(),'backid'=>$amentityType->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Name   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amentityType->amentity_types_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($amentityType->amentity_types_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amentityType->amentity_types_desc}}</span></div>
                </div>
              </div>

            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
