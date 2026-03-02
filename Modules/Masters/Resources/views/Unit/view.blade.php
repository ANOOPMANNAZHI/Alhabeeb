@extends('layouts.plms-app')

@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Unit</div>
    </div>
    {{ Breadcrumbs::render('unit.show',$unit) }}
    @php
    $current = 'unit.show';
    $currentId =  $unit->id;
    Session::put('current', $current);
    Session::put('currentId', $currentId);
    @endphp 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box salesSearchBox">
            @can('edit_unit')   
			 <div class="">
			  <h4>
			   <a href="{{route('unit.edit',[$unit->id,'backurl'=>Route::currentRouteName(),'backid'=>$unit->id])}}" class="btn btn-circle btn-primary  align-right">
				 Edit
			   </a>
			   <div class="clr"></div>
			 </h4>
		   </div>
		   @endcan  
            <form action="#" id="form_sample_2" class="form-horizontal">
              <div class="card-body row"> 

                <!-- starts -->
            
                <div class="dataSearchBox">
                  <div class="card-body row">
					{{-- @if(isset($unit->building->building_name))  --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Building  :  </b><span>{{$unit->building->building_name}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif  --}}
					{{-- @if(isset($unit->unit_no)) --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b> Unit No  :  </b><span>{{$unit->unit_no}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
                    {{-- @if(isset($unit->unit_code)) --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b> Unit Code  :  </b><span>{{$unit->unit_code}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
                    {{-- @if(isset($unit->unit->unit_types_name))  --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Unit Type  :  </b><span>{{$unit->unit->unit_types_name}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
					{{-- @if(isset($unit->unit_base_rent))  --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Base Rent  :  </b><span>
							 @if(isset($unit->unit_base_rent))
							  {{numberFormat($unit->unit_base_rent)}} OMR 
							  @endif
				  </span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
					{{-- @if(isset($unit->unit_floor)) --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Floor No :  </b><span>{{$unit->unit_floor}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
                    {{-- @if(isset($unit->floor_area)) --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Floor Area :  </b><span>{{$unit->floor_area}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif --}}
                    {{-- @if(isset($unit->unit_toilets))  --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>No.Of Toilets  :  </b><span>{{$unit->unit_toilets}}</span></h5>
                      </div>
                    </div>
                     {{-- @endif --}}
                     {{-- @if(isset($unit->unit_is_furnished_name)) --}} 
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Furnished  :  </b><span>{{$unit->unit_is_furnished_name}}</span></h5>
                      </div>
                    </div>
                    {{-- @endif  --}}
                   
                    {{-- @if(isset($unit->unit_water_consumer_no))  --}}
                    <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                        <h5 class="details"><b>Service  :  </b> <span>
                         @if($unit->unit_is_service==1)
                         Occupied by Landlord with service 
                         @elseif($unit->unit_is_service==2)
                         Occupied by Landlord without service
                         @else
                         NA
                         @endif
                       </span></h5>
                     </div>
                   </div>
                   {{-- @endif --}}
                   {{-- @if(isset($unit->vacant_status_name))  --}}
                   <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Vacant Or Occupied ?  :  </b><span>{{$unit->vacant_status_name}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif  --}}
                  {{-- @if(isset($unit->unit_status_name))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Status  :  </b><span>{{$unit->unit_status_name}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif --}}
                  {{-- @if(isset($unit->unit_note))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Unit Note  :  </b><span>{{$unit->unit_note}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif --}}
                </div>
              </div>
              <!-- 1 -->
              <!-- starts -->
              <div class="sub-head">Meter Details</div>
              <div class="dataSearchBox">
                <div class="card-body row">

                  {{-- @if(isset($unit->unit_electric_meter))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Electric Meter  :  </b><span>{{$unit->unit_electric_meter}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif --}}
                  {{-- @if(isset($unit->unit_electric_consumer_no))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Electric Consumer No  :  </b><span>{{$unit->unit_electric_consumer_no}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif  --}}
                  {{-- @if(isset($unit->unit_water_meter))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Water Meter  :  </b><span>{{$unit->unit_water_meter}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif  --}}
                  {{-- @if(isset($unit->unit_water_consumer_no))  --}}
                  <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                      <h5 class="details"><b>Water Consumer No  :  </b><span>{{$unit->unit_water_consumer_no}}</span></h5>
                    </div>
                  </div>
                  {{-- @endif --}}

                </div>
              </div>
              <!-- 1 -->  
            </div>
          </form>
        </div>
      </div>
    </div>
    @can('view_unit_details')
    <div class="row">
     <div class="col-md-12 col-sm-12 dashboardtab1">
      <div class="card  card-box">

        <div class="card-body ">
          <header class="panel-heading custom-tab ">
            <ul class="nav nav-tabs">

              <li class="nav-item"><a href="#asset" data-toggle="tab" class="{{($cat==null)?'active':(($cat==1)?'active':'')}}" >Unit Assets </a>
              </li>
              <li class="nav-item"><a href="#amenities" data-toggle="tab" class="{{($cat==2)?'active':''}}" >Unit Amenities </a>
              </li>
            </ul>
          </header>
          <div class="panel-body tab-color">
            <div class="tab-content">  

              <div class="tab-pane {{($cat==null)?'active':(($cat==1)?'active':'')}}" id="asset">
                <form action="{{route('unit.utilitiesUpdate')}}" method="POST" class="form-horizontal"> 
                 {{csrf_field()}}

                 <input type="hidden" value="1" name="category" >
                 <input type="hidden" value="{{$unit->id}}" name="unit" >
                 <div class="row">
                  @forelse ($utility as $utilities)
                  @if($utilities->category==1)
                  @php
                  if(!empty($utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count))
                  $countUtilities = $utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count;
                  else
                  $countUtilities = 0;
                  @endphp
                  <div class="col-sm-4">
                   <div class=" input-group mb-2">
                     <div class="input-group-prepend">
                      <div class="input-group-text">
                        <input type="checkbox" {{($countUtilities)?'checked':""}}  class="chkCls" id="chk-{{$utilities->id}}"  name="chk-{{$utilities->id}}" >
                      </div>
                    </div>

                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        {{$utilities->home_utilities_code}}
                      </div>
                    </div>
                    <input type="text" maxlength="2" id="count-{{$utilities->id}}" name="count-{{$utilities->id}}" class="form-control col-md-4 countCls" value="{{($countUtilities)? $countUtilities:0}}" aria-label="Asset count">

                  </div>
                </div>

                @endif
                @empty
                No Record
                @endforelse
              </div>

              <div class="col-sm-4">
               <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
               <button type="submit" class="btn btn-primary add_button">Update</button>
             </div>
           </form>
         </div>

         <div class="tab-pane {{ ($cat==2)?'active':''}}" id="amenities">
          <form action="{{route('unit.utilitiesUpdate')}}" method="POST" class="form-horizontal">
            <input type="hidden" value="2" name="category" >
            <input type="hidden" value="{{$unit->id}}" name="unit" >
            <div class="row">
              {{csrf_field()}}
              @forelse ($utility as $utilities)
              @if($utilities->category==2)
              @php
              if(!empty($utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count))
              $countUtilities = $utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count;
              else
              $countUtilities = 0;
              @endphp

              <div class="col-sm-4">
               <div class=" input-group mb-2">
                 <div class="input-group-prepend">
                  <div class="input-group-text">
                   <input type="checkbox" {{($countUtilities)?"checked":""}} class="chkCls" id="chk-{{$utilities->id}}"  name="chk-{{$utilities->id}}"  >
                 </div>
               </div>
               <div class="input-group-prepend">
                <div class="input-group-text"> 
                  {{$utilities->home_utilities_code}}
                </div>
              </div>
              <input type="text" maxlength="2" id="count-{{$utilities->id}}" name="count-{{$utilities->id}}" class="form-control col-md-4 countCls" value="{{($countUtilities)? $countUtilities:0}}" aria-label="Asset count">

            </div>
          </div>
          @endif
          @empty
          No Record
          @endforelse
        </div>
        <div class="col-sm-2">
          <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
          <button type="submit" class="btn btn-primary add_button">Update</button>
        </div>
      </form>

    </div>

  </div>

</div>
</div>
</div>
</div>
@endcan
@can('are_unit_details')
<div class="col-md-12 col-sm-12 dashboardtab1">
  <div class="card  card-box">

    <div class="card-body ">
      <header class="panel-heading custom-tab ">
        <ul class="nav nav-tabs">

          <li class="nav-item"><a href="#asset" data-toggle="tab" class="{{($cat==null)?'active':(($cat==1)?'active':'')}}" >Unit Assets </a>
          </li>
          <li class="nav-item"><a href="#amenities" data-toggle="tab" class="{{($cat==2)?'active':''}}" >Unit Amenities </a>
          </li>
        </ul>
      </header>
      <div class="panel-body tab-color">
        <div class="tab-content">  

          <div class="tab-pane {{($cat==null)?'active':(($cat==1)?'active':'')}}" id="asset">
           <form action="{{route('unit.utilitiesUpdate')}}" method="POST" class="form-horizontal"> 
            {{csrf_field()}}

            <input type="hidden" value="1" name="category" >
            <input type="hidden" value="{{$unit->id}}" name="unit" >
            <div class="row">
              @forelse ($utility as $utilities)
              @if($utilities->category==1)
              @php
              if(!empty($utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count))
              $countUtilities = $utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count;
              else
              $countUtilities = 0;
              @endphp
              <div class="col-sm-4">
                <div class=" input-group mb-2">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                    </div>
                  </div>

                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      {{$utilities->home_utilities_code}}
                    </div>
                    <div class="input-group-text">
                      {{($countUtilities)? $countUtilities:0}}
                    </div>
                  </div>     
                </div>
              </div>

              @endif
              @empty
              No Record
              @endforelse
            </div>

            <div class="col-sm-4">
              <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
            </div>
          </form>
        </div>

        <div class="tab-pane {{ ($cat==2)?'active':''}}" id="amenities">
         <form action="{{route('unit.utilitiesUpdate')}}" method="POST" class="form-horizontal">
          <input type="hidden" value="2" name="category" >
          <input type="hidden" value="{{$unit->id}}" name="unit" >
          <div class="row">
            {{csrf_field()}}
            @forelse ($utility as $utilities)
            @if($utilities->category==2)
            @php
            if(!empty($utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count))
            $countUtilities = $utilities->getHomeUtility->where('unit_id',$unit->id)->first()->utiltity_count;
            else
            $countUtilities = 0;
            @endphp

            <div class="col-sm-4">
              <div class=" input-group mb-2">
                <div class="input-group-prepend">
                  <div class="input-group-text">

                  </div>
                </div>
                <div class="input-group-prepend">
                  <div class="input-group-text"> 
                    {{$utilities->home_utilities_code}}
                  </div>
                  <div class="input-group-text"> 
                    {{($countUtilities)? $countUtilities:0}}
                  </div>
                </div>
              </div>
            </div>
            @endif
            @empty
            No Record
            @endforelse
          </div>
          <div class="col-sm-2">
            <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
          </div>
        </form>

      </div>

    </div>

  </div>
</div>
</div>
</div>
@endcan
</div>
@endsection
@section('scripts')

<script>
  $(document).ready(function() {
    $(".chkCls").on('click',function(e){

      var name = $(this).attr('name');

      var res = name.replace('chk-', 'count-');
      if ($("#"+name).is(":checked")) {

       $("#"+res).val(1);
     }else{

       $("#"+res).val(0);
     }
   });
    $(".countCls").on('keyup',function(e){

      var name = $(this).attr('name');

      var res = name.replace('count-', 'chk-');
      if (parseInt($("#"+name).val()) > 0) {

       $("#"+res).prop('checked', true);
     }else{

       $("#"+res).prop('checked', false);
     }
   });

  });
  
</script>

@endsection
