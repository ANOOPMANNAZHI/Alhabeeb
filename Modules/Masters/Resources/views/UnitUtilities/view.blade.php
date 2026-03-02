@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Unit Utility</div>
        </div>
         {{ Breadcrumbs::render('unit-utility.show',$unit_utility) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
           	@can('edit_unit_utility') 
            <div class="card-head">
              <h4>
			    <a href="{{route('unit-utility.edit',[$unit_utility->id,'backurl'=>Route::currentRouteName(),'backid'=>$unit_utility->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Home Utility   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$unit_utility->homeUtility->home_utilities_code}}</span></div>
                </div>
              </div>

             <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$unit_utility->unit->unit_code}}</span></div>
                </div>
              </div>
             <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>AMC Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$unit_utility->amc_contract_no}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Remarks  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$unit_utility->unit_utilities_remark}}</span></div>
                </div>
              </div>
   
            </div>
            </form>
        </div>
    </div>
</div>

@endsection
