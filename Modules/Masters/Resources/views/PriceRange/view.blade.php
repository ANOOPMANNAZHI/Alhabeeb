@extends('layouts.plms-app')

@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Price Range</div>
        </div>
        {{ Breadcrumbs::render('priceRange.show',$priceRange ) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <div class="card-head">
              <h4>
				@can('edit_price_range') 
				
			    <a href="{{route('priceRange.edit',[$priceRange->id,'backurl'=>Route::currentRouteName(),'backid'=>$priceRange->id])}}" class="btn btn-circle btn-primary  align-right">
					Edit
				</a>
				 @endcan 
				<div class="clr"></div>
            </h4>
            </div>
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
            <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$priceRange->price_ranges_name}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>From </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$priceRange->price_ranges_from}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>To </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$priceRange->price_ranges_to}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($priceRange->price_ranges_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>             
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
