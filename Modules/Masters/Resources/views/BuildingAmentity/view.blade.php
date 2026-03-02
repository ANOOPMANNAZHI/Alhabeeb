@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Building Amenity</div>
        </div>
         {{ Breadcrumbs::render('building-amentity.show',$building_amentity->id ) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
           
            <div class="card-head">
              <h4>
				@can('edit_building_amentity') 
				
			    <a href="{{route('building-amentity.edit',[$building_amentity->id,'backurl'=>Route::currentRouteName(),'backid'=>$building_amentity->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Amenity Type   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$building_amentity->amentityType->amentity_types_name}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Landlord Building  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$building_amentity->building->building_name}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>AMC Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$building_amentity->amc_contract_no}}</span></div>
                </div>
              </div>

              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Remarks  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$building_amentity->building_amentity_utilities_remark}}</span></div>
                </div>
              </div>

  
            </div>
            </form>
        </div>
    </div>
</div>

@endsection
