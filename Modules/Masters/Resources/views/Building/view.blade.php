@extends('layouts.plms-app')
@section('css')
<!-- gallery -->
<link href="{{asset('public/plugins/light-gallery/css/lightgallery.css')}}" rel="stylesheet">
@endsection

@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Building</div>
    </div>
    {{ Breadcrumbs::render('building.show',$building) }}
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box salesSearchBox">
     @can('edit_building')   
     <div class="">
      <h4>
       <a href="{{route('building.edit',[$building->id,'backurl'=>Route::currentRouteName(),'backid'=>$building->id])}}" class="btn btn-circle btn-primary  align-right">
         Edit
       </a>
       <div class="clr"></div>
     </h4>
   </div>
   @endcan  
   <form action="#" id="form_sample_2" class="form-horizontal">
    <div class="card-body row"> 


      <!-- starts -->
      <!-- <div class="sub-head">Building Details</div> -->
      <div class="dataSearchBox">
        <div class="card-body row">
          {{-- @if(isset($building->building_code)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b> Building Code  :  </b><span>{{$building->building_code}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_name))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Name  :  </b><span>{{$building->building_name}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_prefix))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Prefix  :  </b><span>{{$building->building_prefix}}</span></h5>
            </div>
          </div>
          {{-- @endif  --}}
          {{-- @if(isset($building->building_no))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building No  :  </b><span>{{$building->building_no}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_no))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Type  :  </b><span>{{$building->buildingType->building_types_name}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_address))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Address  :  </b><span>{{$building->building_address}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_no_floor))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Number Of Floors  :  </b><span>{{$building->building_no_floor}}</span></h5>
            </div>
          </div>
          {{-- @endif  --}}
          {{-- @if(isset($building->MaintenanceInfoName))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Maintenance Info  :  </b><span>{{$building->MaintenanceInfoName}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->landmark)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b> Landmark  :  </b><span>{{$building->landmark}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->db_number))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>DB No  :  </b><span>{{$building->db_number}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->building_pc))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Way No  :  </b><span>{{$building->building_pc}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->google_location))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Google Location  :  </b><span>{{$building->google_location}}</span></h5>
            </div>
          </div>
          {{-- @endif  --}}
          {{-- @if(isset($building->plot_no)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Plot No :  </b><span>{{$building->plot_no}}</span></h5>
            </div>
          </div> 
          {{-- @endif --}}
          {{-- @if(isset($building->block_number)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Block No :  </b><span>{{$building->block_number}}</span></h5>
            </div>
          </div> 
          {{-- @endif --}}
          {{-- @if(isset($building->watchman_no)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Watchman No :  </b><span>{{$building->watchman_no}}</span></h5>
            </div>
          </div> 
          {{-- @endif --}}
          {{-- @if(isset($building->building_status_name)) --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Status :  </b><span>{{$building->building_status_name}}</span></h5>
            </div>
          </div> 
          {{-- @endif --}}
          {{-- @if(isset($building->building_note))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Note :  </b><span>{{$building->building_note}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Division :  </b><span>
                {{($building->ax_division==1)?'HO':'PLM'}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Are :  </b><span>
				@if(count($building->buildingAssignTo) > 0)
				@foreach($building->buildingAssignTo as $assign)
					
				  {{$assign->buildingAssignToName->areUser->employee->employee_name??'Not Assigned' }}
				  
				@endforeach
				@else
					Not Assigned
				@endif 
				</span></h5>
            </div>
          </div>
       
        </div>
      </div>
      <!-- 1 -->
      <!-- 3 -->
      <div class="sub-head">Landlord Details</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          {{-- @if(isset($building->vendor_id))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Landlord Name  :  </b><span>{{$building->vendor->vendor_name}}</span></h5>
            </div>
          </div>
          {{-- @endif  --}}
          {{-- @if(isset($building->management->management_types_name))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Management  :  </b><span>{{$building->management->management_types_name}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}
          {{-- @if(isset($building->management_date))  --}}
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Management Date  :  </b><span>{{$building->management_date}}</span></h5>
            </div>
          </div>
          {{-- @endif --}}

        </div>
      </div>
      <!-- 
       <div class="dataSearchBox">
                  <div class="card-body row">
                    <div class="col-lg-6 p-t-20">
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Total Units  :  </b><span>{{$building->unit->count()}}</span></h5>
                      </div>
                    </div><div class="col-lg-6 p-t-20">
                      <div class = "txt-full-width">
                        <h5 class="details"><b> </b><span></span></h5>
                      </div>
                    </div>
                  </div>
                </div>-->

    </div>
  </form>
</div>
</div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="card card-topline-green">
      <div class="card-head">
        <header>Location</header>
        <div class="tools">
          <a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
          <a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
          <a class="t-close btn-color fa fa-times" href="javascript:;"></a>
        </div>
      </div>
      <div class="card-body ">
        <div id="map" class="gmaps"> </div>
      </div>
    </div>
  </div>
</div>
@if($images->count()>0)
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <header>Gallery</header>
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
            @foreach ($images as $image) 
            <div class="balance m-b-20"> 
              <a href="{{asset('storage/app/'.$image->building_path_file_name)}}" data-sub-html="Building Images">
               <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$image->building_path_thumbnail)}}" alt="{{$image->BuildingImgCategoryName}}" title ="{{$image->BuildingImgCategoryName}}"> </a> </div>
               @endforeach    
             </div>
           </div>
         </div>
       </div>
     </div>
     @endif
     @if($docs->count()>0)
     <!--
     <div class="row">
      <div class="col-sm-12">
        <div class="card-box">
          <div class="card-head">
            <header> Building Docs</header>
			<button id = "panel-button" 
                       class = "mdl-button mdl-js-button mdl-button--icon pull-right" 
                       data-upgraded = ",MaterialButton">
         <i class = "material-icons">more_vert</i>
          </button>
          <ul class = "mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
             data-mdl-for = "panel-button">
             <li class = "mdl-menu__item"><i class="material-icons">assistant_photo</i>Action</li>
             <li class = "mdl-menu__item"><i class="material-icons">print</i>Another action</li>
             <li class = "mdl-menu__item"><i class="material-icons">favorite</i>Something else here</li>
           </ul> 
         </div>
         <div class="card-body row">
          <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
            @foreach ($docs as $doc)
            <div class="col-sm-6">
              {{$doc->BuildingDocCategoryName}}
            </div> 
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> 
              <a target="_blank" href="{{asset('storage/app/'.$doc->building_doc_path_name)}}">
                {{$doc->building_doc_name}}  </a> </div>

                @endforeach 
              </div>
            </div>
          </div>
        </div>
      </div>
      -->
      @endif
      @can('view_building_details') 
      <div class="row">
       <div class="col-md-12 col-sm-12 dashboardtab1">
        <div class="card  card-box">

          <div class="card-body ">
            <header class="panel-heading custom-tab ">
              <ul class="nav nav-tabs">

                <li class="nav-item"><a href="#unit" data-toggle="tab" class="{{($tab=='unit')?'active': ''}}" >Unit Detail</a>
                </li>

                <li class="nav-item"><a href="#amentity" data-toggle="tab" class="{{($tab=='amentity')?'active': ''}}">Amentity</a>
                </li>                                   
                <li class="nav-item"><a href="#insurance" data-toggle="tab" class="{{($tab=='insurance')?'active': ''}}">Insurance</a>
                </li>
                <li class="nav-item"><a href="#documents" data-toggle="tab" class="{{($tab=='documents')?'active': ''}}">Documents</a>
                </li>
                <li class="nav-item"><a href="#meter" data-toggle="tab" class="{{($tab=='meter')?'active': ''}}">Meter Details</a>
                </li>
                <li class="nav-item"><a href="#units" data-toggle="tab" class="{{($tab=='meter')?'active': ''}}">Units</a>
                </li>
              </ul>
            </header>


            <div class="panel-body tab-color">
              <div class="tab-content">  
                <div class="tab-pane @if($tab=='unit') active @endif" id="unit">
                  <form action="{{route('building.updateUnitTypeCount')}}" method="POST" class="form-horizontal" onSubmit="return confirm('Please Confirm the Unit Details')"> 
                    {{csrf_field()}}
                    <table class="table display product-overview mb-30" id="dtBasicExample">

                      <thead>
                        <tr>
                          <th>Sl No.</th>
                          <th>Unit Type</th>
                          <th style="text-align:left">No Of Unit Type</th>

                        </tr>
                      </thead>
                      <tbody>
                        <input type="hidden" name="building_id" value="{{$building->id}}">
                        @forelse ($unitype as $unit)
                        <tr>
                          <td>{{$loop->iteration}}</td>
                          <td>{{$unit->unit_types_name}}</td>                            
                          <td>
                            {{-- dd($unit->unitTypeCount->find('building_id',5)->first())--}}
                            @php 
                            if(count($unit->unitTypeCount->where('building_id',$building->id))> 0)          
                            $countunitType =  $unit->unitTypeCount->where('building_id',$building->id)->first()->unittype_count;

                            else
                            $countunitType = 0 ;

                            @endphp

                            <input type="hidden" name="unit_type_{{$unit->id}}" value="{{$unit->id}}" >
                            <input type="text" maxlength="2" id="count-{{$unit->id}}" name="count-{{$unit->id}}" class="form-control col-md-2 countCls" value="{{$countunitType}}">

                          </td>                            

                        </tr>  
                        @empty
                        <tr>
                          <td colspan="4" align="center">
                            <p>No records</p>
                          </td>
                        </tr>
                        @endforelse

                      </tbody>
                    </table>
                    <div class="col-sm-2">
                      <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                      <button type="submit" class="btn btn-primary add_button">Update</button>
                    </div>
                  </form>

                </div>
                <div class="tab-pane @if($tab=='insurance') active @endif" id="insurance">
                  @if($building->building_status == 1)
                  <h4>
                    <a href="{{route('building-insurance.create',['id'=>$building->id,'backurl'=>Route::currentRouteName(),'tab'=>'insurance'])}}" class="btn btn-circle btn-primary  align-right">Add</a>
                    <div class="clr"></div>
                  </h4>
                  @endif
                  <div class="clearfix"></div>
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Insurance Company</th>
                        <th>Valid From</th>
                        <th>Valid To</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($buildinginsurances as $insurance)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$insurance->insurance_company}}</td>
                        <td>{{$insurance->insurance_start->format('d-m-Y')}}</td> 
                        <td>{{$insurance->insurance_end->format('d-m-Y')}}</td>                             
                        <td> 
                          @can('edit_building_insurance')
                          <a title="Edit" href="{{route('building-insurance.edit',[$insurance->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                          </a>   
                          @endcan 
                          @can('view_building_insurance')
                          <a title="View" href="{{route('building-insurance.show',[$insurance->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                          </a>   
                          @endcan  
                          @can('delete_building_insurances')
                          <a href="{{route('building-insurance.destroy',$insurance->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_insu_type">
                            <i class="fa fa-trash-o "></i>
                          </a>  
                          @endcan 
                        </td>
                      </tr>  
                      @empty
                      <tr>
                        <td colspan="4" align="center">
                          <p>No records</p>
                        </td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>

                  {{-- $buildinginsurances->links() --}}   

                </div>
                <div class="tab-pane @if($tab =='amentity') active @endif " id="amentity">
                  @if($building->building_status == 1)
                  <h4>
                    <a href="{{route('building-amentity.create',['id'=>$building->id,'backurl'=>Route::currentRouteName(),'tab'=>'amentity'])}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
                    <div class="clr"></div>
                  </h4>
                  @endif
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Amentity Type</th>
                         <th>AMC Expiry Date</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($buildingAmentities as $amentity)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$amentity->amentityType->amentity_types_name}}</td>                            
                        <td> @if(!empty($amentity->amc->amc_contract_period_to))
                  {{$amentity->amc->amc_contract_period_to->format('d-m-Y')}}
                  @endif
						</td>                           
                        <td>
                          @can('edit_building_amentity') 
                          <a title="Edit" href="{{route('building-amentity.edit',[$amentity->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil"></i>
                          </a>  
                          @endcan 
                          @can('view_building_amentity') 
                          <a title="View" href="{{route('building-amentity.show',[$amentity->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                          </a>  
                          @endcan   
                          @can('delete_building_amentity') 
                          @if($amentity->amc_contract_no == '')
                          <a title="Delete" href="{{ route('building-amentity.destroy',$amentity->id) }}" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o"></i>
                          </a>  
                          @endif
                          @endcan                                          
                        </td>
                      </tr>  
                      @empty
                      <tr>
                        <td colspan="4" align="center">
                          <p>No records</p>
                        </td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <!--documents starts -->
                <div class="tab-pane @if($tab =='documents') active @endif " id="documents">
               
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Doc Category</th>
                        <th>Docs</th>
                        
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($docs as $doc)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$doc->BuildingDocCategoryName}}</td>                            
                        <td><a target="_blank" href="{{asset('storage/app/'.$doc->building_doc_path_name)}}">
                              {{$doc->building_doc_name}}  </a>	
						</td>                            
                      </tr>  
                      @empty
                      <tr>
                        <td colspan="3" align="center">
                          <p>No records</p>
                        </td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
               
                  </div>
                  <!--documents ends -->
                  <!-- meter details starts-->
                  <div class="tab-pane @if($tab=='meter') active @endif" id="meter">


                    <table class="table display product-overview mb-30" id="dtBasicExample">
                      <thead>
                        <tr>
                          <th>Sl No.</th>
                          <th>Category</th>
                          <th>Ele A/c No</th>
                          <th>Ele Met No</th>
                          <th>Water A/c No</th>
                          <th>Water Met No</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($buildingEleWaterReading as $buildingEleWater)
                        <tr>
                          <td>{{$loop->iteration}}</td>
                          <td>{{$buildingEleWater->building_meter_category_name}}</td>
                          <td>{{$buildingEleWater->electricity_acc_no ?? ''}}</td>
                          <td>{{$buildingEleWater->electricity_met_no ?? ''}}</td>
                          <td>{{$buildingEleWater->water_acc_no ?? ''}}</td>
                          <td>{{$buildingEleWater->water_met_no ?? ''}}</td>
                        </tr>  
                        @empty
                        <tr>
                          <td colspan="6" align="center">
                            <p>No records</p>
                          </td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>

                    {{-- $buildingEleWaterReading->links() --}}   

                  </div>
				 
                  <!-- meter details ends-->
                   <div class="tab-pane @if($tab=='units') active @endif" id="units">
                 
                  <div class="clearfix"></div>
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Unit Code</th>
                        <th>Unit No</th>
                        <th>Unit Type</th>                                      
                        <th>Vaccant</th>                                      
                        <th>Status</th>                                      
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($buildingUnit as $unit)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$unit->unit_code}}</td>
                        <td>{{$unit->unit_no}}</td>
                        <td>{{$unit->unit->unit_types_name}}</td>
                        <td>{{$unit->vacant_status_name}}</td>
                        <td>{{$unit->unit_status_name}}</td>
                      </tr>  
                      @empty
                      <tr>
                        <td colspan="3" align="center">
                          <p>No records</p>
                        </td>
                      </tr>
                      @endforelse
					   @if($building->unit->count() > 0)
                       <tr>
                        <td colspan="2">Total No Of Units :</td>
                        <td>{{$building->unit->count()}}</td>  
                      </tr> 
                      @endif
                    </tbody>
                  </table>                   

                </div>
                  <!-- meter details ends-->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endcan 
      @can('are_building_details') 
      <div class="row">
       <div class="col-md-12 col-sm-12 dashboardtab1">
        <div class="card  card-box">

          <div class="card-body ">
            <header class="panel-heading custom-tab ">
              <ul class="nav nav-tabs">

                <li class="nav-item"><a href="#unit" data-toggle="tab" class="{{($tab=='unit')?'active': ''}}" >Unit Detail</a>
                </li>

                <li class="nav-item"><a href="#amentity" data-toggle="tab" class="{{($tab=='amentity')?'active': ''}}">Amentity</a>
                </li>                                   
                <li class="nav-item"><a href="#insurance" data-toggle="tab" class="{{($tab=='insurance')?'active': ''}}">Insurance</a>
                </li>
                <li class="nav-item"><a href="#documents" data-toggle="tab" class="{{($tab=='documents')?'active': ''}}">Documents</a>
                </li>
                <li class="nav-item"><a href="#meter" data-toggle="tab" class="{{($tab=='meter')?'active': ''}}">Meter Details</a>
                </li>
                 <li class="nav-item"><a href="#units" data-toggle="tab" class="{{($tab=='meter')?'active': ''}}">Units</a>
                </li>
              </ul>
            </header>
            <div class="panel-body tab-color">
              <div class="tab-content">  
                <div class="tab-pane active" id="unit">
                  <form action="{{route('building.updateUnitTypeCount')}}" method="POST" class="form-horizontal"> 
                   {{csrf_field()}}
                   <table class="table display product-overview mb-30" id="dtBasicExample">

                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Unit Type</th>
                        <th>No Of Unit Type</th>

                      </tr>
                    </thead>
                    <tbody>
                      <input type="hidden" name="building_id" value="{{$building->id}}" >
                      @forelse ($unitype as $unit)
                      {{-- dd($unit->unitTypeCount->find('building_id',5)->first())--}}
                      @php 
                      if(count($unit->unitTypeCount->where('building_id',$building->id))> 0)          
                      $countunitType =  $unit->unitTypeCount->where('building_id',$building->id)->first()->unittype_count;

                      else
                      $countunitType = 0 ;

                      @endphp 
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$unit->unit_types_name}}</td>     
                        <td>{{$countunitType}}
                         <input type="hidden" name="unit_type_{{$unit->id}}" value="{{$unit->id}}" >
                       </td>                            

                     </tr>  
                     @empty
                     <tr>
                       <td colspan="4" align="center">
                        <p>No records</p>
                      </td>
                    </tr>
                    @endforelse

                  </tbody>
                </table>
              </form>

            </div>
            <div class="tab-pane @if($tab=='insurance') active @endif" id="insurance">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                  <tr>
                    <th>Sl No.</th>
                    <th>Insurance Company</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($buildinginsurances as $insurance)
                  <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$insurance->insurance_company}}</td>
                    <td>{{$insurance->insurance_start->format('d-m-Y')}}</td> 
                    <td>{{$insurance->insurance_end->format('d-m-Y')}}</td>                             
                    <td> @can('edit_building_insurance')
                      <a title="Edit" href="{{route('building-insurance.edit',[$insurance->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                        <i class="fa fa-pencil"></i>
                      </a>   
                      @endcan  
                    </td>
                  </tr>  
                  @empty
                  <tr>
                    <td colspan="4" align="center">
                      <p>No records</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              {{-- $buildinginsurances->links() --}}   

            </div>
            <div class="tab-pane @if($tab =='amentity') active @endif " id="amentity">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                  <tr>
                    <th>Sl No.</th>
                    <th>Amentity Type</th>
                    <th>AMC Expiry Date</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($buildingAmentities as $amentity)
                  <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$amentity->amentityType->amentity_types_name}}</td>                            
                    <td>@if(!empty($amentity->amc->amc_contract_period_to))
                  {{$amentity->amc->amc_contract_period_to->format('d-m-Y')}}
                  @endif</td>                            
                    <td>
                      @can('edit_building_amentity') 
                      <a title="Edit" href="{{route('building-amentity.edit',[$amentity->id,'backid'=>$building->id,'backurl'=>Route::currentRouteName()])}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                        <i class="fa fa-pencil"></i>
                      </a>  
                      @endcan                                            
                    </td>
                  </tr>  
                  @empty
                  <tr>
                    <td colspan="4" align="center">
                      <p>No records</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <!--documents starts -->
     
            <div class="tab-pane @if($tab =='documents') active @endif " id="documents">
             @if($docs->count()>0)
             <div class="row">
              <div class="col-sm-12">
                <div class="card-box">
                  <div class="card-head">
                    <header> Building Docs</header>
                  </div>
                  <div class="card-body row">
                    <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                      @foreach ($docs as $doc)
                      <div class="col-sm-6">
                        {{$doc->BuildingDocCategoryName}}
                      </div> 
                      <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> 
                        <a target="_blank" href="{{asset('storage/app/'.$doc->building_doc_path_name)}}">
                          {{$doc->building_doc_name}}  </a> </div>

                          @endforeach 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @else
                <div class="row">
                  <div class="col-sm-12">
                    <div class="card-box">
                      <div class="card-head">
                        <header> Building Docs</header>
                      </div>
                      <div class="card-body row">
                        <p>No Documents Found !</p>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
              </div>
           
              <!--documents ends -->
              <!-- meter details starts-->
              <div class="tab-pane @if($tab=='meter') active @endif" id="meter">


                <table class="table display product-overview mb-30" id="dtBasicExample">
                  <thead>
                    <tr>
                      <th>Sl No.</th>
                      <th>Category</th>
                      <th>Ele A/c No</th>
                      <th>Ele Met No</th>
                      <th>Water A/c No</th>
                      <th>Water Met No</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($buildingEleWaterReading as $buildingEleWater)
                    <tr>
                      <td>{{$loop->iteration}}</td>
                      <td>{{$buildingEleWater->building_meter_category_name}}</td>
                      <td>{{$buildingEleWater->electricity_acc_no ?? ''}}</td>
                      <td>{{$buildingEleWater->electricity_met_no ?? ''}}</td>
                      <td>{{$buildingEleWater->water_acc_no ?? ''}}</td>
                      <td>{{$buildingEleWater->water_met_no ?? ''}}</td>
                    </tr>  
                    @empty
                    <tr>
                      <td colspan="4" align="center">
                        <p>No records</p>
                      </td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>

                {{-- $buildingEleWaterReading->links() --}}   

              </div>
              <!-- meter details ends-->
              <div class="tab-pane @if($tab=='units') active @endif" id="units">
                 
                  <div class="clearfix"></div>
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                      <tr>
                        <th>Sl No.</th>
                        <th>Unit Code</th>
                        <th>Unit No</th>
                        <th>Unit Type</th>                                      
                        <th>Vaccant</th>                                      
                        <th>Status</th>                                      
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($building->unit as $unit)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$unit->unit_code}}</td>
                        <td>{{$unit->unit_no}}</td>
                        <td>{{$unit->unit->unit_types_name}}</td>
                        <td>{{$unit->vacant_status_name}}</td>
                        <td>{{$unit->unit_status_name}}</td>
                      </tr>  
                      @empty
                      <tr>
                        <td colspan="3" align="center">
                          <p>No records</p>
                        </td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>                   

                </div>
              <!-- meter details ends-->

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endcan
  @endsection

  @section('scripts')   
  <form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
  </form> 
  <!-- gallery -->
  <script src="{{asset('public/plugins/light-gallery/js/lightgallery-all.js')}}"></script> <!-- Light Gallery Plugin Js --> 
  <script src="{{asset('public/plugins/light-gallery/js/image-gallery.js')}}"></script>
  <script>
    function initMap() {
  // The location of Uluru
  var lat = parseFloat(<?php echo json_encode($building->building_geo_lat)?>);
  var lng = parseFloat(<?php echo json_encode($building->building_geo_long)?>);//alert(lng);
  //var uluru = {lat: 23.6473237, lng: 58.14582459999997};
  var uluru = {lat: lat, lng: lng};
  // The map, centered at Uluru
  var map = new google.maps.Map(document.getElementById('map'), {
    center: {
      lat: lat,
      lng: lng
    },
    zoom: 8,
    mapTypeId: 'roadmap'
  });
  // The marker, positioned at Uluru
  var marker = new google.maps.Marker({position: uluru, map: map});
}

jQuery(document).ready(function() {


  jQuery('.btn-tbl-edit-disable').click(function (event) {
    var textId = $(this).attr("id");
    $("#edit-"+textId).prop("disabled",false);
    $("#save-"+textId).show();
    $("#"+textId).hide();

  });
            // Save event
            jQuery('.btn-tbl-edit-save').click(function (event) {
              var arrTextId = $(this).attr("id").split('-');
              var textId    = arrTextId[arrTextId.length-1];
              $("#edit-"+textId).prop("disabled",true);
              $("#save-"+textId).hide();
              $("#"+textId).show();

            });


            jQuery('.delete_type').click(function (event) {
              var action = $(this).attr("href");
              event.preventDefault();
              if (confirm('Do you want to Delete this Amenity?')) {
                jQuery("#delete-form").attr('action', action);
                jQuery("#delete-form").submit();
              } else {
                return false;
              }
            });
            jQuery('.delete_insu_type').click(function (event) {
              var action = $(this).attr("href");
              event.preventDefault();
              if (confirm('Do you want to Delete this Building Insurance?')) {
                jQuery("#delete-form").attr('action', action);
                jQuery("#delete-form").submit();
              } else {
                return false;
              }
            });
          });

        </script> 
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC5ojp1R4jsDhXaVvcsv_z1VbhPdFNulLc&libraries=places&callback=initMap"
        ></script>

        @endsection
