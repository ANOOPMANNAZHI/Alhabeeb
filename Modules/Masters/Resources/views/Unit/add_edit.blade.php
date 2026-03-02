@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
@endsection
@section('content')
<!-- start widget --> 
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Unit</div>
    </div>
    {{ (isset($unit))?Breadcrumbs::render('unit.edit',$unit,$backUrlBreadCrumb,$backIdBreadCrumb ) :  Breadcrumbs::render('unit.create') }}         
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form autocomplete="off"  action="{{ !isset($unit)? route('unit.store'): route('unit.update',$unit->id)}}" method="POST" id="form_sample_2" class="form-horizontal" >
        {{csrf_field()}} @if(isset($unit)){{method_field('PUT')}}@endif
        <input type="hidden" name="backurl" value="{{isset($previousUrl)?$previousUrl:''}}" >

        <div class="dataSearchBox ">

          <div class="row">

           <div class="col-sm-6">
            <div class="form-group">
              <label for="building_id">Building Name<small class="textRed">*</small> </label>
              <div class="p-relative">
                <i class="icon icon-building" aria-hidden="true"></i>
                <input type="text" placeholder="Enter Building Name" autocomplete="off"  required name="building_name" class="form-control building_name" id="building_name" value="{{ isset($unit)?  old('building_name',$unit->building->building_name): old('building_name','')}}">

                <input type="hidden" name="building_id" id="building_id" value="{{ isset($unit)?  old('building_id',$unit->building_id): old('building_id','')}}">

               <!--  <select class="form-control" id="building_id" required name="building_id" data-validation="required">
                  <option value="">Select Building</option>
                  @foreach($building as $val)
                   @if(isset($unit))  
                      @if($unit->building_id==$val->id) 
                            
                          @php 
                            $buildNoFloor =  $val->building_no_floor; 
							$buildPrefix =  $val->building_prefix;
                          @endphp

                      @endif
                   @endif
                   <option  {{(old('building_id', isset($unit)?  $unit->building_id : 0) == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->building_name}}</option>
                  @endforeach                  
                </select> --> 
              </div>               
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label for="building_prefix">Building Prefix<small class="textRed">*</small> </label>
              <div class="p-relative">
                <i class="icon icon-building" aria-hidden="true"></i>
                <input readonly type="text" class="form-control" required id="building_prefix"  name="building_prefix" placeholder="Enter Building Prefix" data-validation="required"  value="{{ old('building_prefix', isset($unit)?  $unit->building->building_prefix : '' )}}" >

              </div>            
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_no">Unit No<small class="textRed">*</small> </label><a  class="align-right viewExistingUnit" id="viewExistingUnit" data-toggle="modal" data-target="#myModal" data-backdrop="static"
data-keyboard="false">Existing Units</a>
              <div class="p-relative">
                <i class="icon icon-unit" aria-hidden="true"></i>
                <input type="text" class="form-control" required id="unit_no"  name="unit_no" data-validation="required"  value="{{isset($unit)?$unit->unit_no:old('unit_no')}}" placeholder="Enter Unit No">
              </div>
            </div>
          </div>   

          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_code">Unit Code<small class="textRed">*</small> </label>
              <div class="p-relative">
                <i class="icon icon-unit" aria-hidden="true"></i>
                <input readonly type="text" class="form-control" required id="unit_code" placeholder="Enter Unit Code"  name="unit_code" data-validation="required"  value="{{isset($unit)?$unit->unit_code:old('unit_no')}}" >
              </div>
            </div>
          </div>   

          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_type_id">Unit Type<small class="textRed">*</small> 
              </label>
              <div class="p-relative">
                <i class="icon icon-unit" aria-hidden="true"></i>
                <select class="form-control" id="unit_type_id" required name="unit_type_id" data-validation="required">
                 @if(isset($unit))
                 <option value="">Select Unit Type</option>
                 @foreach($unitTypes as $unitType)
                 <option  {{isset($unit)?(($unit->unit_type_id== $unitType->unit_type_id)? 'selected' : ''):old('unit_no')}} value="{{$unitType->unit_type_id}}">{{$unitType->unit_types_name}}</option>
                 @endforeach     
                 @else
                 <option value="">Select Unit Type</option>
                 @endif             
               </select>
             </div>             
           </div>
         </div>
         <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_base_rent">Base Rent</label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="text" class="form-control" id="unit_base_rent"  name="unit_base_rent" data-validation="required" onkeyup="FormatCurrency(this)"  value="{{ old('unit_base_rent', isset($unit)? numberFormat($unit->unit_base_rent): '' )}}" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"  placeholder="Enter Base Rent">
            </div>
          </div>
        </div> 
        <div class="w-100"></div>
        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_floor">Floor No<small class="textRed">*</small> </label>
            <div class="p-relative">
              <i class="fa fa-building icn-add" aria-hidden="true"></i>
              <select class="form-control" id="unit_floor"  name="unit_floor" required >
                @if(isset($unit))
                @for($i=-1; $i < $unit->building->building_no_floor; $i++)
                <option value="{{$i}}" {{(old('unit_floor', isset($unit)?  $unit->unit_floor : 0) == $i) ? 'selected' : '' }}>{{$i}}</option>
                @endfor
                @else
                <option value=" ">Select building first</option>
                @endif
              </select> 
              {{-- <input type="number" class="form-control" id="unit_floor"  name="unit_floor" required  value="{{ old('unit_floor', isset($unit)?  $unit->unit_floor : '' )}}" placeholder="Enter No.of Floors"> --}}
            </div>
          </div>
        </div>  
        <div class="col-sm-6">
          <div class="form-group">
            <label for="floor_area">Floor Area</label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="text" class="form-control" id="floor_area"  name="floor_area"  value="{{ old('floor_area', isset($unit)?  $unit->floor_area : '' )}}" placeholder="Enter Floor Area">
            </div>
          </div>
        </div>
        <div class="w-100"></div> 
        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_toilets">No.Of Toilets</label>
            <div class="p-relative">
              <i class="fa fa-bath icn-add" aria-hidden="true"></i>
              <input type="number" class="form-control" id="unit_toilets"  name="unit_toilets" data-validation="required"  value="{{ old('unit_toilets', isset($unit)?  $unit->unit_toilets : '' )}}" min="1" placeholder="Enter No.Of Toilets">
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_electric_consumer_no">Electric A/c No.<small class="textRed">*</small> </label>
            <div class="p-relative">
              <i class="icon icon-electrical" aria-hidden="true"></i>
              <input type="text" class="form-control" id="unit_electric_consumer_no"  name="unit_electric_consumer_no" required  value="{{ old('unit_electric_consumer_no', isset($unit)?  $unit->unit_electric_consumer_no : '' )}}" placeholder="Enter Electric A/c No.">
            </div>
          </div>
        </div>
        <div class="w-100"></div> 
        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_electric_meter">Electric Meter No <small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="icon icon-electrical" aria-hidden="true"></i>
              <input type="text" class="form-control" id="unit_electric_meter"  name="unit_electric_meter" required  value="{{ old('unit_electric_meter', isset($unit)?  $unit->unit_electric_meter : '' )}}" placeholder="Enter Electric Meter No">
            </div>
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_water_consumer_no">Water A/c No</label>
            <div class="p-relative">
              <i class="icon icon-plumbing" aria-hidden="true"></i>
              <input type="text" class="form-control"  id="unit_water_consumer_no"  name="unit_water_consumer_no"   value="{{ old('unit_water_consumer_no', isset($unit)?  $unit->unit_water_consumer_no : '' )}}" placeholder="Enter Water A/c No">
            </div>
          </div>
        </div>
        <div class="w-100"></div> 
        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_water_meter">Water Meter No</label>
            <div class="p-relative">
              <i class="icon icon-plumbing" aria-hidden="true"></i>
              <input type="text" class="form-control" id="unit_water_meter"  name="unit_water_meter" value="{{ old('unit_water_meter', isset($unit)?  $unit->unit_water_meter : '' )}}" placeholder="Enter Water Meter No">
            </div>
          </div>
        </div>


        <div class="col-sm-6">
          <div class="form-group">
            <label for="unit_status">Service</label>
            <div class="p-relative">
              <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
              <select class="form-control" id="unit_is_service"  name="unit_is_service">
                <option value="">Select </option>                  
                <option  {{(old('unit_is_service', isset($unit)?  $unit->unit_is_service : '') == 1) ? 'selected' : '' }} value="1">
                  Occupied by Landlord with service
                </option>
                <option  {{(old('unit_is_service', isset($unit)?  $unit->unit_is_service : '' ) == 2) ? 'selected' : '' }} value="2">
                  Occupied by Landlord without service
                </option>                                
              </select> 
            </div>               
          </div>
        </div>          		      


         <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_is_legal">Is Legal ? <small class="textRed">*</small></label>
                <div class="form-group">
                <input type="radio" required   {{ (old('unit_is_legal', isset($unit)?  $unit->unit_is_legal : '') == 1 )? 'checked' : '' }} name="unit_is_legal" value="1"> Yes 
                <input type="radio" required {{ (old('unit_is_legal', isset($unit)?  $unit->unit_is_legal : '') == 2 )? 'checked' : '' }} name="unit_is_legal" value="2"> No 
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_legal_date">Legal Date</label>
                 <div class="p-relative">
                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" data-validation="required" data-validation-depends-on="unit_is_legal"  data-validation-depends-on-value="1"   id="unit_legal_date"     name="unit_legal_date" value="{{ old('unit_legal_date', isset($unit)?   ( ($unit->unit_is_legal == 1) ? $unit->unit_legal_date->format('Y-m-d') : '' )  : '' )}}" placeholder="Enter Legal Date">
              </div>
            </div>
          </div> -->
          <div class="w-100"></div>    
          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_vaccant_status">Vacant Or Occupied ? <small class="textRed">*</small> </label>
              <div class="form-group">
                <input type="radio"  required {{ (old('unit_vaccant_status', isset($unit)?  $unit->unit_vaccant_status : -1) == 0 )? 'checked' : 'checked' }}  name="unit_vaccant_status" value="0"> Vacant 
                <input type="radio" required  {{ (old('unit_vaccant_status', isset($unit)?  $unit->unit_vaccant_status : -1) == 1 )? 'checked' : '' }}  name="unit_vaccant_status" value="1"> Occupied 
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_is_furnished">Furnished ? <small class="textRed">*</small></label>
              <div class="form-group">
                <input type="radio"  required {{ (old('unit_is_furnished', isset($unit)?  $unit->unit_is_furnished : '') == 1 )? 'checked' : '' }} name="unit_is_furnished" value="1"> Furnished 
                <input type="radio"  {{ (old('unit_is_furnished', isset($unit)?  $unit->unit_is_furnished : 'checked') == 2 )? 'checked' :'checked' }} name="unit_is_furnished" value="2"> Unfurnished 
              </div>
            </div>
          </div>     


          <div class="w-100"></div>    
          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_status">Status<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control" id="unit_status"  name="unit_status" required>
                  <option {{(old('unit_status', isset($unit)?  $unit->unit_status :0) == '1') ? 'selected' : '' }} value="1">Active</option>                
                   <option  {{(old('unit_status', isset($unit)?  $unit->unit_status :0) == '0') ? 'selected' : '' }} value="0">Inactive</option>                                
                </select> 
              </div>               
            </div>
          </div>         

          <div class="w-100"></div>

          <div class="col-sm-12">
            <div class="form-group">
              <label for="unit_note">Unit Note </label>
              <div class="p-relative">
                <i class="icon icon-unit" aria-hidden="true"></i>
                <textarea  class="form-control" id="unit_note"  name="unit_note" data-validation="required" placeholder="Enter Unit Note"   >{{ old('unit_note', isset($unit)?  $unit->unit_note : '' )}}</textarea>
              </div>
            </div>
          </div> 

          <div class="w-100"></div>

          <div class="col">
            <div class="w-100"></div>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>

        </div>

      </div>
      <div class="clearfix"></div>
    </form>
    
  </div>
</div>
</div>
<div class="modal" id="myModal"></div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
  $(document).ready(function() {
    $('#building_name').autocomplete({
      source : '{!!URL::route('buildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
          $("#building_id").val('');
          $("#building_name").val('');
          $("#building_prefix").val('');
          $("#unit_code").val('');
        }else {
          $('#building_id').val(ui.item.ids);
        }
        var id = ui.item.ids;
        if(id){
          $.ajax
          ({
            type: "POST",
            url: "{{url('/unit/ajaxFloorList')}}",
            data: {"id":id,"_token": "{{ csrf_token() }}"},
            cache: false,
            dataType: "json",
            success: function(data)
            {

              if(data.building.length > 0){

                $('#unit_floor').empty();
                $('#unit_type_id').html('<option value="" >Select Unit Type</option>');                 
                var floor = data.building[0]['building_no_floor'];
                var building_prefix = data.building[0]['building_prefix'];
                if(building_prefix)
                  $('#building_prefix').val(building_prefix);

                $('#unit_floor').append('<option value="">Select Floor</option>');
                for (var i = -1; i < floor; i++) {

                  $('#unit_floor').append('<option value="'+ i +'">'+ i +'</option>');
                }

              }
              else{
                $('#unit_floor').html('<option value="" >No Data</option>');
              }

              if(data.unit_type.length > 0){
               for (var i = 0; i < data.unit_type.length; i++) {
                console.log(data.unit_type[i].unit_types_name);
                $('#unit_type_id').append('<option value="'+ data.unit_type[i].unit_type_id +'">'+ data.unit_type[i].unit_types_name +'</option>');
              }
            }
            else{
              $('#unit_type_id').html('<option value="" >No Available Unit Type</option>');

            }
          } 
        });

        }

        else{
          $('#unit_floor').empty();
        }
      }
    });
    /************************************************************/
    $("#form_sample_2").validate()
    // Floor count
    /*$("#building_id").change(function()
    {
          var id  = $(this).val();

          if(id){
              $.ajax
                  ({
                      type: "POST",
                      url: "{{url('/unit/ajaxFloorList')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {

                        if(data.length > 0){
                          $('#unit_floor').empty();
                          var floor = data[0]['building_no_floor'];
                          var building_prefix = data[0]['building_prefix'];
                          if(building_prefix)
							 $('#building_prefix').val(building_prefix);
							 
                          $('#unit_floor').append('<option value="">Select Floor</option>');
                          for (var i = 1; i <= floor; i++) {
                       
                            $('#unit_floor').append('<option value="'+ i +'">'+ i +'</option>');
                          }
                           
                        }
                        else{
                            $('#unit_floor').html('<option value="" >No Data</option>');
                        }
                      } 
                  });

                }
          
          else{
              $('#unit_floor').empty();
          }
        });*/

        $("#unit_no").keyup(function(){

          var prefix 		= $("#building_prefix").val();
          var unit_code 	= $("#unit_no").val();
          var unit_no		= prefix+"-"+unit_code;
          if(prefix==''){
           alert("Please select the building");
           $("#unit_no").val('');
           return false;
         }
         $("#unit_code").val(unit_no);

		/*


        var id = $('#building_id').val();
        
         if(id){
              $.ajax
                  ({
                      type: "POST",
                      url: "{{url('/unit/ajaxFloorList')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {

                        if(data.building.length > 0){
              
                          $('#unit_floor').empty();
                          $('#unit_type_id').html('<option value="" >Select Unit Type</option>');                 
                          var floor = data.building[0]['building_no_floor'];
                          var building_prefix = data.building[0]['building_prefix'];
                          if(building_prefix)
							$('#building_prefix').val(building_prefix);
               
                          $('#unit_floor').append('<option value="">Select Floor</option>');
                          for (var i = 0; i < floor; i++) {
                       
                            $('#unit_floor').append('<option value="'+ i +'">'+ i +'</option>');
                          }
                           
                        }
                        else{
                            $('#unit_floor').html('<option value="" >No Data</option>');
                        }
                        
                        if(data.unit_type.length > 0){
              for (var i = 0; i < data.unit_type.length; i++) {
                console.log(data.unit_type[i].unit_types_name);
                $('#unit_type_id').append('<option value="'+ data.unit_type[i].unit_type_id +'">'+ data.unit_type[i].unit_types_name +'</option>');
              }
            }
            else{
                $('#unit_type_id').html('<option value="" >No Available Unit Type</option>');
              
            }
                      } 
                  });

                }
          
          else{
              $('#unit_floor').empty();
          }
          */
        });
       
      });
/***************************************************************************/
//View Existing Unit
$(document).on('click','.viewExistingUnit',function(){

  var building_id = $("#building_id").val();
  //alert(building_id);
  $.ajax({
    method: 'POST',
    url: "{{route('viewExistingUnit')}}",
            data: {'building_id' : building_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
            },
          });
  return true;


});
/***************************************************************************/
    </script> 
    @endsection
