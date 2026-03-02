@extends('layouts.plms-app')

@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Building Insurance</div>
        </div>
         {{ Breadcrumbs::render('building-insurance.show',$insurance->id) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
           <div class="card-head">
              <h4>
				@can('edit_building_insurance') 
				
			    <a href="{{route('building-insurance.edit',[$insurance->id,'backurl'=>Route::currentRouteName(),'backid'=>$insurance->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Insurance Company :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$insurance->insurance_company}}</span></div>
                </div>
              </div>
             <div class="col-md-6 p-t-10">
                      <div class="row">
                        <div class="col-md-5"><b>Building :  </b></div>
                        <div class="col-md-1 s-clm">:</div>
                        <div class="col-md-6"><span>{{$insurance->building->building_name}}</span></div>
                      </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Insurance Policy Type :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$insurance->insurance_policy_type}}</span></div>
                </div>
              </div>
             <div class="col-md-6 p-t-10">
                      <div class="row">
                        <div class="col-md-5"><b>Insurance Premium :  </b></div>
                        <div class="col-md-1 s-clm">:</div>
                        <div class="col-md-6"><span>{{$insurance->insurance_premium_value}}</span></div>
                      </div>
              </div>
                  <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Valid From :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$insurance->insurance_start->format('d-m-Y')}}</span></div>
                </div>
              </div>
             <div class="col-md-6 p-t-10">
                      <div class="row">
                        <div class="col-md-5"><b>Valid To :  </b></div>
                        <div class="col-md-1 s-clm">:</div>
                        <div class="col-md-6"><span>{{$insurance->insurance_end->format('d-m-Y')}}</span></div>
                      </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Insured By :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$insurance->insurance_policy_type}}</span></div>
                </div>
              </div>
             <div class="col-md-6 p-t-10">
                      <div class="row">
                        <div class="col-md-5"><b>Insurance Building Value:  </b></div>
                        <div class="col-md-1 s-clm">:</div>
                        <div class="col-md-6"><span>{{$insurance->insurance_building_value}}</span></div>
                      </div>
              </div>

              <div class="col-md-6 p-t-10">
                      <div class="row">
                        <div class="col-md-5"><b>Debit Acc:  </b></div>
                        <div class="col-md-1 s-clm">:</div>
                        <div class="col-md-6"><span>{{$insurance->insurance_debit_acc?? 'NA'}}</span></div>
                      </div>
              </div>
            </div>
            
            </form>
        </div>
    </div>
</div> 
@if($insurance->insurance_img_copy)
    <div class="row">
      <div class="col-sm-12">
        <div class="card-box">
          <div class="card-head">
            <header>Image</header>
            <!-- <button id = "panel-button" 
                           class = "mdl-button mdl-js-button mdl-button--icon pull-right" 
                           data-upgraded = ",MaterialButton">
             <i class = "material-icons">more_vert</i>
              </button>
              <ul class = "mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
                 data-mdl-for = "panel-button">
                 <li class = "mdl-menu__item"><i class="material-icons">assistant_photo</i>Action</li>
                 <li class = "mdl-menu__item"><i class="material-icons">print</i>Another action</li>
                 <li class = "mdl-menu__item"><i class="material-icons">favorite</i>Something else here</li>
              </ul> -->
          </div>
          <div class="card-body row">
        <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
             
                  <div class="balance m-b-20"> 
                    <a href="{{asset('storage/app/'.$insurance->insurance_img_copy)}}" data-sub-html=" Images">
                     <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$insurance->insurance_path_thumbnail)}}" > </a> </div>
                  
              </div>
          </div>
        </div>
      </div>
    </div>
    @endif

@endsection
