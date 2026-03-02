@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Contractor Work Link</div>
        </div>
        {{ Breadcrumbs::render('workLink.show',$workLink) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            @can('edit_worklink') 
			   <div class="card-head">
				  <h4>
					<a href="{{route('workLink.edit',[$workLink->id,'backurl'=>Route::currentRouteName(),'backid'=>$workLink->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Code  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workLink->vendor->vendor_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Name </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workLink->work->works_code}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($workLink->contractor_work_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>

            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
