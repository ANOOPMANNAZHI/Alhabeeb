@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Work Flow Category</div>
        </div>
        {{ Breadcrumbs::render('workFlowCategory.show') }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
            
    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Code  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowCategory->assign_field_name}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Price Range:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowCategory->priceRange->price_ranges_name}}</span></div>
                </div>
              </div>


    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowCategory->location->locations_name}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($workFlowCategory->assign_field_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>

            
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection