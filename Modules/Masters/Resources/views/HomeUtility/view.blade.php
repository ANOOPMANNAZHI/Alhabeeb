@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Home Amenities</div>
        </div>
         {{ Breadcrumbs::render('homeUtility.show',$utility) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <div class="card-head">
            <h4>
			    <a href="{{route('homeUtility.edit',[$utility->id,'backurl'=>Route::currentRouteName(),'backid'=>$utility->id])}}" class="btn btn-circle btn-primary  align-right">
					Edit
				</a>
				<div class="clr"></div>
            </h4>
           </div>
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 

              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Code   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$utility->home_utilities_code}}</span></div>
                </div>
              </div>
               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Category   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{($utility->category==1)?'Asset':'Amenity'}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Make  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$utility->home_utilities_make}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Model </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$utility->home_utilities_model}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Serial No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$utility->home_utilities_serial_no}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($utility->home_utilities_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$utility->home_utilities_desc}}</span></div>
                </div>
              </div>

            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
