@extends('layouts.plms-app')

@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Payment Method</div>
        </div>
        {{ Breadcrumbs::render('paymentMethod.show',$paymentMethod) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            @can('edit_payment_method')
				 <div class="card-head">
				<h4>
					<a href="{{route('paymentMethod.edit',[$paymentMethod->id,'backurl'=>Route::currentRouteName(),'backid'=>$paymentMethod->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Code : </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$paymentMethod->payment_method_code}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($paymentMethod->payment_method_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$paymentMethod->payment_method_desc}}</span></div>
                </div>
              </div>                                      
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
