@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Status</div>
        </div>
        {{ Breadcrumbs::render('tenantStatus.show',$tenantStatus) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            @can('edit_tenant_status')
			 <div class="card-head">
            <h4>
			    <a href="{{route('tenantStatus.edit',[$tenantStatus->id,'backurl'=>Route::currentRouteName(),'backid'=>$tenantStatus->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenantStatus->tenant_statuses_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($tenantStatus->tenant_statuses_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenantStatus->tenant_statuses_desc}}</span></div>
                </div>
              </div>

            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
