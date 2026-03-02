@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Work</div>
        </div>
         {{ Breadcrumbs::render('work.show',$work) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            	@can('edit_work') 
			   <div class="card-head">
				  <h4>
					<a href="{{route('work.edit',[$work->id,'backurl'=>Route::currentRouteName(),'backid'=>$work->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-6"><span>{{$work->works_code}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Type  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($work->works_type==0){{'Both(Maintenance & AMC)'}}@elseif($work->works_type==1){{'Maintenance'}}@else {{'AMC'}} @endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($work->works_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$work->works_desc}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Acc Code  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$work->acc_code_val}}-{{$work->acc_code_desc}}</span></div>
                </div>
              </div>
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
