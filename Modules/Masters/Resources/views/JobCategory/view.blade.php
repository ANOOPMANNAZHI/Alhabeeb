@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Job Category</div>
        </div>
        {{ Breadcrumbs::render('jobCategory.show' ,$jobCategory) }}
    </div>
</div>

<div class="row">
	
    <div class="col-sm-12">
        <div class="card-box">
                     <div class="card-head">

				@can('edit_job_category') 
				<h4>
					<a href="{{route('jobCategory.edit',[$jobCategory->id,'backurl'=>Route::currentRouteName(),'backid'=>$jobCategory->id])}}" class="btn btn-circle btn-primary  align-right">
						Edit
					</a>
					<div class="clr"></div>
				</h4>
				@endcan
				
			
            </div>
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 

               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Code   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$jobCategory->job_category_code}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$jobCategory->job_category_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($jobCategory->job_category_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>             
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
