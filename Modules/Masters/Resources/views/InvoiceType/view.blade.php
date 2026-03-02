@extends('layouts.plms-app')

@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Invoice Type</div>
        </div>
        {{ Breadcrumbs::render('invoiceType.show', $invoiceType) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            @can('edit_invoice_type')
				 <div class="card-head">
				<h4>
					<a href="{{route('invoiceType.edit',[$invoiceType->id,'backurl'=>Route::currentRouteName(),'backid'=>$invoiceType->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Name </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$invoiceType->invoice_types_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($invoiceType->invoice_types_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$invoiceType->invoice_types_desc}}</span></div>
                </div>
              </div>
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
