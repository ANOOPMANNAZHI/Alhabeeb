<div class="modal-dialog custom-pop assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Create Building</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="" method="POST" id="building_pup" class="form-horizontal sbt-build" autocomplete="off" >
{{csrf_field()}} @if(isset($building)){{method_field('PUT')}}@endif

 
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_code">Building Code<small class="textRed">*</small> </label>
                <input disabled type="text" class="form-control" id="building_code"  name="building_code" required  value="{{isset($building)?  $building->building_code : $nextCode }}">
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_name">Building Name<small class="textRed">*</small> </label>
                <input type="text" class="form-control" id="building_name"  name="building_name" required  value="{{ old('building_name', isset($building)?  $building->building_name : '' )}}" placeholder="Enter Building Name">
            </div>
          </div>          

		     <div class="col-sm-6">
            <div class="form-group">
                <label for="building_prefix">Building Prefix<small class="textRed">*</small> </label>
                <div class="p-relative">
                   <i class="fa fa-sort-numeric-asc icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="building_prefix"  name="building_prefix" required  value="{{ old('building_prefix', isset($building)?  $building->building_prefix : '' )}}" placeholder="Enter Building Prefix" maxlength="4">
            </div>
            </div>
          </div>  
          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_maintenance_info"> Building Maintenance Info</label>
                 <div class="p-relative">
                   <i class="fa fa-hospital-o icn-add" aria-hidden="true"></i>
                <select class="form-control" id="building_maintenance_info"  name="building_maintenance_info" >
                  <option value="">Select Building Maintenance Info</option>
                   <option  {{(old('building_maintenance_info', isset($building)?  $building->building_maintenance_info : '') == 0) ? 'selected' : '' }} value="0">Managed by us</option>
                   <option  {{(old('building_maintenance_info', isset($building)?  $building->building_maintenance_info : '') == 1) ? 'selected' : '' }} value="1">Not Managed by us</option>                
                </select> 
                </div>               
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="vendor_id"> Vendor <small class="textRed">*</small></label>
                <div class="p-relative">
                   <i class="fa fa-hospital-o icn-add" aria-hidden="true"></i>
                <!-- <input type="text" readonly placeholder="Enter Vendor Autocomplete" name="vendor_name" class="form-control vendor_name" id="vendors_name" required value="{{ isset($building)?  old('vendor_name',$building->vendor->vendor_name): old('vendor_name','')}}"> -->
					<input type="text" readonly name="vendor_name" class="form-control vendor_name" id="vendors_name_popup" required value="{{ isset($building)?  old('vendor_name',$building->vendor->vendor_name): old('vendor_name','')}}">
					
                    <input type="hidden" name="vendor_id" id="vendor_id" value="{{ isset($building)?  old('vendor_id',$building->vendor_id): old('vendor_id','')}}">


                <!-- <select class="form-control" id="vendor_id"  name="vendor_id" required>
                  <option value="">Select Vendor</option>
                  @foreach($vendors as $vendor)
                   <option  {{(old('vendor_id', isset($building)?  $building->vendor_id : 0) == $vendor->id) ? 'selected' : '' }} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                  @endforeach                  
                </select> -->  
                </div>              
            </div>
          </div>
          <div class="w-100"></div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_no">Building No<small class="textRed">*</small> </label>
                <div class="p-relative">
                   <i class="fa fa-hospital-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_no"  name="building_no" required  value="{{ old('building_no', isset($building)?  $building->building_no : '' )}}" placeholder="Enter Building No">
              </div>
            </div>
          </div>  
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_pc">Way No<small class="textRed">*</small> </label>
                 <div class="p-relative">
                  <i class="fa fa-university icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="building_pc"  name="building_pc" required  value="{{ old('building_pc', isset($building)?  $building->building_pc : '' )}}" placeholder="Enter Way No">
            </div>
            </div>
          </div>
          <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="block_number">Block Number</label>
                <div class="p-relative">
                   <i class="fa fa-hospital-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="block_number"  name="block_number"   value="{{ old('block_number', isset($building)?  $building->block_number : '' )}}" placeholder="Enter Block No">
            </div>
            </div>
          </div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="plot_no">Plot No</label>
                <div class="p-relative">
                   <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="plot_no"  name="plot_no"   value="{{ old('plot_no', isset($building)?  $building->plot_no : '' )}}" placeholder="Enter Plot No">
            </div>
            </div>
          </div>
          <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="landmark">Landmark</label>
                <div class="p-relative">
                   <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" autocomplete="off" id="landmark"  name="landmark"  value="{{ old('landmark', isset($building)?  $building->landmark : '' )}}" placeholder="Enter Landmark">
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="location_id">Location<small class="textRed">*</small> </label>
                <div class="p-relative">
                   <i class="fa fa-clock-o icn-add" aria-hidden="true"></i>
                <input type="text" placeholder="Enter Location" required name="location_name" class="form-control location_name" id="locations_name" value="{{ isset($building)?  old('location_name',$building->location->locations_name): old('location_name','')}}">

                    <input type="hidden" name="location_id" id="location_id" value="{{ isset($building)?  old('location_id',$building->location_id): old('location_id','')}}">
                <!-- <select class="form-control" id="location_id"  name="location_id" required>
                  <option value="">Select Location</option>
                  @foreach($location as $val)
                   <option  {{(old('location_id', isset($building)?  $building->location_id : '') == $val->id) ? 'selected' : '' }} value="{{$val->id}}">{{$val->locations_name}}</option>
                  @endforeach                  
                </select> --> 
                </div>               
            </div>
          </div>
          <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="building_type_id"> Building Type <small class="textRed">*</small></label>
                <select class="form-control" id="building_type_id"  name="building_type_id" required>
                  <option value="">Select Building Type</option>
                  @foreach($buildingTypes as $buildingType)
                   <option  {{(old('building_type_id', isset($building)?  $building->building_type_id : 0) == $buildingType->id) ? 'selected' : '' }} value="{{$buildingType->id}}">{{$buildingType->building_types_name}}</option>
                  @endforeach                  
                </select>                
            </div>
          </div>     
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_no_floor">Number of Floors<small class="textRed">*</small> </label>
                <input type="number" class="form-control" id="building_no_floor"  name="building_no_floor" required  value="{{ old('building_no_floor', isset($building)?  $building->building_no_floor : '' )}}" placeholder="Enter Number of Floors" data-rule-maxlength="3" data-msg-maxlength="Only allowed 3 length numbers" min="1">
            </div>
          </div>
       <!--    <div class="w-100"></div> -->
          <div class="col-sm-6">
            <div class="form-group">
                <label for="watchman_no">Watchman No</label>
                <div class="p-relative">
                   <i class="fa fa-clock-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="watchman_no"  name="watchman_no"   value="{{ old('watchman_no', isset($building)?  $building->watchman_no : '' )}}" placeholder="Enter Watchman No">
            </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="management_id"> Management<small class="textRed">*</small></label>
                <select class="form-control" id="management_id"  name="management_id" required>
                  <option value="">Select Management</option>
                  @foreach($managementTypes as $managementType)
                   <option  {{(old('management_id', isset($building)?  $building->management_id : 0) == $managementType->id) ? 'selected' : '' }} value="{{$managementType->id}}">{{$managementType->management_types_name}}</option>
                  @endforeach                  
                </select>                
            </div>
          </div> 
		   <div class="col-sm-6">
            <div class="form-group">
                <label for="management_id">Division<small class="textRed">*</small></label>
          <select class="form-control" id="ax_division"  name="ax_division" required>
                  <option {{(old('ax_division', isset($building)?  $building->ax_division :0) == '02') ? 'selected' : '' }} value="02">PLM</option>
                 
                   <option  {{(old('ax_division', isset($building)?  $building->ax_division :0) == '01') ? 'selected' : '' }} value="01">HO</option>
                                 
                </select> 
            </div>
          </div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="build_up_area">Management Date</label>
                <div class="p-relative">
                   <i class="fa fa-creative-commons icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="management_date"  name="management_date"   value="{{ old('management_date', isset($building)?  $building->management_date : '' )}}" placeholder="Enter Date">
            </div>
            </div>
          </div>
          <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="build_up_area">Build-up Area</label>
                <div class="p-relative">
                   <i class="fa fa-creative-commons icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="build_up_area"  name="build_up_area"   value="{{ old('build_up_area', isset($building)?  $building->build_up_area : '' )}}" placeholder="Enter Build-Up Area">
            </div>
            </div>
          </div>
           <div class="col-sm-6">
            <div class="form-group">
                <label for="google_location">Google Location<small class="textRed">*</small> </label>
                <input type="text" class="form-control" id="google_location"  name="google_location" required  value="{{ old('google_location', isset($building)?  $building->google_location : '' )}}" placeholder="Enter Location" >
                <input type="hidden" value="{{ old('building_geo_lat', isset($building)?  $building->building_geo_lat : '' )}}" name="building_geo_lat" id="building_geo_lat" >
                <input type="hidden" value="{{ old('building_geo_long', isset($building)?  $building->building_geo_long : '' )}}" name="building_geo_long" id="building_geo_long" >
            </div>
          </div> 
           <div class="w-100"></div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="buiding_address">Building Address<small class="textRed">*</small> </label>
                <textarea  class="form-control" id="building_address"  name="building_address" required placeholder="Enter Building Address"   >{{ old('building_address', isset($building)?  $building->building_address : '' )}}</textarea>
            </div>
          </div>   
          <div class="col-sm-6">
            <div class="form-group">
                <label for="building_note">Note<small class="textRed">*</small> </label>
                <textarea  class="form-control" required id="building_note"  name="building_note" placeholder="Enter Note "   >{{ old('building_note', isset($building)?  $building->building_note : '' )}}</textarea>
            </div>
          </div> 
          <div class="w-100"></div>       
          <div class="col-sm-6">
            <div class="form-group">
                <label for="db_number">Tel DB No.</label>
                <div class="p-relative">
                   <i class="fa fa-compass icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="db_number"  name="db_number"   value="{{ old('db_number', isset($building)?  $building->db_number : '' )}}" placeholder="Enter DB No" maxlength="20" onkeypress="return isNumberKey(event)">
            </div>
            </div>
          </div>
          
        <!--  <div class="col-sm-6">
            <div class="form-group">
                <label for="building_status">Status<small class="textRed">*</small></label>
                <select class="form-control" id="building_status"  name="building_status" required>
                <option value="">Select </option>                  
                <option  {{(old('building_status', isset($building)?  $building->building_status : '') == 1) ? 'selected' : '' }} value="1">Active</option>
                <option  {{(old('building_status', isset($building)?  $building->building_status : -1) == 0) ? 'selected' : '' }} value="0">Inactive</option>                                
                </select>                
            </div>
          </div>   -->
         
       <div class="w-100"></div>
        <!-- save button -->
           
      </div>
    
</div>
<div class="clearfix"></div>
<div class="sub-head">Building Meter Details</div>
<div class="dataSearchBox ">
        <div class="row">
          <div class="col-sm-2">
              <div class="form-group">
                <label for="building_meter_category">Category</label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="electricity_acc_no">Ele A/c No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="electricity_met_no">Ele Met No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="water_acc_no">Water A/c No </label>
              </div>
          </div>
          <div class="col-sm-2">
              <div class="form-group">
                <label for="water_met_no">Water Met No </label>
              </div>
          </div>
           <div class="w-100"></div>
        </div>  
        <div class="field_wrapper_ele">
              
         <div class="row ele">
           <div class="col-sm-2">
            <div class="form-group">
                <div class="p-relative">
                      <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                <select class="form-control" id="building_meter_category"  name="building_meter_category[]" >
                <option value="">Select Category</option>                  
                 <option value="0">Common Area</option>
                <option value="1">Lift</option> 
                <option value="2">Others</option>                               
                </select>  
                </div>              
            </div>
          </div>
           <div class="col-sm-2">
            <div class="form-group">
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                 <input type="text" autocomplete="off" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No">
            </div>
            </div>
          </div>
           <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-electrical" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Enter Water A/c. No">
            </div>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="form-group">
                
                <div class="p-relative">
                   <i class="icon icon-plumbing" aria-hidden="true"></i>
                <input type="text" autocomplete="off" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Water Met No">
            </div>
            </div>
          </div>
     
          <div class="col-sm-2">
               
                <button type="button" class="btn btn-primary add_button_ele">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button>
          </div>
    </div>
 </div>
    
</div>
<div class="clearfix"></div>
<div class="sub-head">Image Upload(Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
        <div class="row">
        <div class="col-sm-5">
              <div class="form-group">
                <label for="building_img_category">Image Category</label>
              </div>
          </div>
          <div class="col-sm-5">
              <div class="form-group">
                <label for="building_img_name">Image</label>
              </div>
          </div>
        </div>
        <div class="field_wrapper">
          <div class="row" id="1">
          <div class="col-sm-5">
            <div class="form-group">
                
                  <div class="p-relative">
                      <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                 <select class="form-control margin-top-8" id="building_img_category"  name="building_img_category[1]" >
                <option value="">Select Category</option>                  
                <option value="0">Common Area</option>
                <option value="1">Lift</option> 
                <option value="2">Others</option>                               
                </select>  
                </div>              
            </div>
          </div>
          <div class="col-sm-5">
            <div class="form-group">
                
                <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
               <input type="file" class="form-control upload"  id="building_img_name[1]"  name="building_img_name[1]" >
            </div>
            </div>
          </div>
      
          
        
          <div class="col-sm-2">
                <button type="button" class="btn btn-primary add_button">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_button"  style="display:none;"><i class="fa fa-trash-o "></i></button>
          </div>
        <div class="w-100"></div>
     
         
        </div>    
      </div>   
</div>
<div class="clearfix"></div>
<div class="sub-head">Docs Upload(Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
      <div class="row">
        <div class="col-sm-5">
              <div class="form-group">
               <label for="building_doc_category">Doc Category</label>
              </div>
          </div>
          <div class="col-sm-5">
              <div class="form-group">
                <label for="building_doc_path_name">Docs</label>
              </div>
          </div>
        </div>
        <div class="field_wrapper_docs">
          <div class="row" id="1">
          <div class="col-sm-5">
            <div class="form-group">
                
                  <div class="p-relative">
                      <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                 <select class="form-control margin-top-8" id="building_doc_category[1]"  name="building_doc_category[1]" >
                <option value="">Select </option>                  
                <option value="0">Mulkia</option>
                <option value="1">Krooki</option> 
                <option value="2">Waqala (POA)</option>
                <option value="3">Landlord ID</option>
                <option value="4">CR (if Landlord is a company)</option>
                <option value="5">Drawings</option>
                <option value="6">Building Permit</option>
                <option value="7">Civil Defense Certificate</option>
                <option value="8">Others</option>                               
                </select>  
                </div>              
            </div>
          </div> 
           <div class="col-sm-5">
            <div class="form-group">
                
                <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="form-control upload_doc"  id="building_doc_path_name[1]"  name="building_doc_path_name[1]" >
            </div>
            </div>
          </div>
          <div class="col-sm-2">
               
                <button type="button" class="btn btn-primary add_docs_button">Add</button>
                <button title="Delete" type="button" class="btn btn-warning remove_doc_button" style="display:none;"><i class="fa fa-trash-o "></i></button>
            </div>

 </div>
     </div> 
</div>    
   <div class="w-100"></div>
   
      <div class="w-100"></div>
          <button type="submit" id="submitBtn" class="btn btn-primary">SAVE</button>
  
</form>
    
</div>
</div>
</div>

    </div>
    </div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLp1Ma7-2RoyI9C-BKY0tiyd-eAqg68dA&libraries=places&callback=initAutocomplete"
        async defer></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>-->
<script> 
$(document).on("change",".upload",function(){
          
          fileUpload($(this));
});  
$(document).on("change",".upload_doc",function(){
          
          fileUploadDoc($(this));
}); 
$(document).ready(function() {

   $("#building_pup").validate();
    $('#vendors_name_popup').autocomplete({
      source : '{!!URL::route('landlordAutocomplete')!!}',
      minlenght:2,
      appendTo: "#myModal1",
      autoFocus:true,
      change:function(e,ui){
         if (ui.item == null || ui.item == undefined) {
            $("#vendors_name_popup").val('');
            $('#vendors_name-error').show();
         }else {
          $('#vendor_id').val(ui.item.ids);
          
         }
       
      }
    });
    /******************************************************/
    $('#locations_name').autocomplete({
      source : '{!!URL::route('locationsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      appendTo: "#myModal1",
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
            $("#locations_name").val('');
            $('#location_name-error').show();
        }else {
           $('#location_id').val(ui.item.ids);
          
       }         
      }
    });

});

$(document).on('submit', 'form.sbt-build', function (event) {
    event.preventDefault();
    var form = $(this);
    $('#submitBtn').prop('disabled', true);
    var data = new FormData($(this)[0]);
    var url = "{{url('/buildingPopupAction')}}";

    $.ajax({
        type: form.attr('method'),
        url: url,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (data) {

            if (data.fail) {
                for (control in data.errors) {
                    $('input[name=' + control + ']').addClass('is-invalid');
                    $('#error-' + control).html(data.errors[control]);
                }
              
            } else {
                $.each(data, function(key, value){
                    if(key=='building_name'){
                        $('#building_name').val(value);
						alert('Building Added Successfully !');
                    }
                    else if(key=='management_id'){
						 $('#management_id').val(value);
						  if(value == 1){
					
								$("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
								$(".percentage_field_value input,.percentage_type select").val('');
								$(".normal_commission_field").hide();
								$("#management_fee_type").removeAttr('required');
								$(".percentage_field_value").hide();
								$(".percentage_type").hide();
								$(".percentage_field_value").hide();
								$("#landlord_contract_amt").attr('required','required');
								$("#comprehensive_field").show();
								
							}   
							else{
								$("#comprehensive_field input,.normal_commission_field select radio").val('');
								$("#comprehensive_field input,.normal_commission_field select, .normal_commission_field radio").val('');
								$(".percentage_field_value input,.percentage_type select").val('');
								$("#landlord_contract_amt").removeAttr('required');
								$("#management_method_one").prop('checked',true);
								$("#management_fee_type").attr('required','required');
								$(".percentage_field_value").show();
								$(".percentage_type").show();
								$("#comprehensive_field").hide();
								$(".normal_commission_field").show();
							}         
						}
                    else{
                      
                         $.ajax({
                          method: 'POST', // Type of response and matches what we said in the route
                          url:"{{url('/buildingNameCodeAjax')}}", 
                          data:{'id' : key,'_token':"{{ csrf_token() }}"}, 
                          success: function(response){ // What to do if we succeed
                              if(response){
                                $("#building_name").val(response); 
                                $("#building_id").val(key); 
                                
                              }else{
                                $("#building_name").val('');
                                $("#building_id").val('');
                                $("#management_id").val('');
                              }
                                    
                          },

                      });
                    }   
                });     
                $('#myModal1').modal('hide'); 
            }
        }
    });
   
});
function initAutocomplete() {
     
        var input = document.getElementById('google_location'); 
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.setComponentRestrictions({'country': 'OM'});

        autocomplete.addListener('place_changed', function() {
          var place = autocomplete.getPlace();
          $('#building_geo_lat').val(place.geometry.location.lat());
          $('#building_geo_long').val(place.geometry.location.lng());    
             
        });
           
}
/*****************************************************************************************/
    var maxField = 40; //Input fields increment limitation
    var addEleButton = $('.add_button_ele'); //Add button selector
    var wrapperEle = $('.field_wrapper_ele'); //Input field wrapper
    var fieldEleHTML = '<div class="row ele"><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control" id="building_meter_category"  name="building_meter_category[]" ><option value="">Select Category </option><option value="0">Common Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-fire icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-bandcamp icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No"></div> </div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-tint icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Enter Water A/c. No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-dot-circle-o icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Enter Water Met. No"></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button_ele">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    
      var x = 1; //Initial field counter is 1
      
    //Once add button is clicked
      $(wrapperEle).on('click', '.add_button_ele',function(){
          var values = $("select[name='building_meter_category[]']")
                  .map(function(){
                    if($(this).val())return $(this).val();}).get();

          var lastRowId   = parseInt($(".field_wrapper_ele .row").first().attr("id"));   

          var len = $("select[name='building_meter_category[]']").length;
         // alert(len);
          if (values.length != len ) {
             alert("Select Category");
            
          }else{
              lastRowId = lastRowId++; 
            //Check maximum number of input fields
              if(x < maxField){ 
                  x++; //Increment field counter
                  var fieldEleHTML = '<div class="row ele"><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control" id="building_meter_category"  name="building_meter_category[]" ><option value="">Select Category </option><option value="0">Common Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-fire icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_acc_no"  name="electricity_acc_no[]"   value="{{ old('electricity_acc_no', isset($building)?  $building->electricity_acc_no : '' )}}" placeholder="Enter Electricity A/c No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-bandcamp icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="electricity_met_no"  name="electricity_met_no[]"   value="{{ old('electricity_met_no', isset($building)?  $building->electricity_met_no : '' )}}" placeholder="Enter Electricity Met. No"></div> </div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-tint icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_acc_no"  name="water_acc_no[]"   value="{{ old('water_acc_no', isset($building)?  $building->water_acc_no : '' )}}" placeholder="Enter Water A/c. No"></div></div></div><div class="col-sm-2"><div class="form-group"><div class="p-relative"><i class="fa fa-dot-circle-o icn-add" aria-hidden="true"></i><input type="text" class="form-control" id="water_met_no"  name="water_met_no[]"   value="{{ old('water_met_no', isset($building)?  $building->water_met_no : '' )}}" placeholder="Enter Water Met. No"></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button_ele">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button_ele" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';

                  $(wrapperEle).prepend(fieldEleHTML); //Add field html

              $('.field_wrapper_ele .row:not(:first)').find('.add_button_ele').remove();
              $('.field_wrapper_ele .row:not(:first)').find('.remove_button_ele').show();
                
              }

          }
        
      });
    
   
    $(wrapperEle).on('click', '.remove_button_ele', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.ele').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });

/********************************Add Image multiple*************************************/    
     var maxField = 40; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
     //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(wrapper).on('click', '.add_button',function(){
      var lastRowId   = parseInt($(".field_wrapper .row").first().attr("id"));
      var values = $("input[name='building_img_name["+lastRowId+"]']")
              .map(function(){
                if($(this).val())return $(this).val();}).get();
            
      var len = $("input[name='building_img_name["+lastRowId+"]']").length;
      if (($( ".report_image_file_name" ).is( ".building_img_name.form-control.error" )) || ( values.length != len ) || ($(".upload").valid() != 1 ) || ($("select[name='building_img_category["+lastRowId+"]']").val() =='')) {
        
              alert("Select Image Category Or Please Upload ");
          
      }else{
        //Check maximum number of input fields
    
          if(x < maxField){ 
              x++; //Increment field counter
              var lastRowId       = lastRowId + 1;

              var fieldHTML = '<div class="row rows" id="'+lastRowId+'" ><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-camera-retro icn-add" aria-hidden="true"></i><select class="form-control margin-top-8" id="building_img_category"  name="building_img_category['+lastRowId+']" ><option value="">Select Category</option><option value="0">Common Area</option><option value="1">Lift</option><option value="2">Others</option></select></div></div>     </div><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control upload"  id="building_img_name"  name="building_img_name['+lastRowId+']" ></div></div></div><div class="col-sm-2"><button type="button" class="btn btn-primary add_button">Add</button><button title="Delete" type="button" class="btn btn-warning remove_button"  style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';
              $(wrapper).prepend(fieldHTML); //Add field html
              $('.field_wrapper .row:not(:first)').find('.add_button').remove();
              $('.field_wrapper .row:not(:first)').find('.remove_button').show();
          }
      }
        
    });
    
  
  $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.row').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });

/*****************************************************************************************/
var maxField = 40; //Input fields increment limitation
    var addDocButton = $('.add_docs_button'); //Add button selector
    var wrapperDoc = $('.field_wrapper_docs'); //Input field wrapper
     
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
   $(wrapperDoc).on('click', '.add_docs_button', function(e){
      var lastRowId   = parseInt($(".field_wrapper_docs .row").first().attr("id"));
      var values = $("input[name='building_doc_path_name["+lastRowId+"]']")
              .map(function(){
                if($(this).val())return $(this).val();}).get();
              
      var len = $("input[name='building_doc_path_name["+lastRowId+"]']").length;
      if (values.length != len || ($(".upload_doc").valid() != 1 ) || ($("select[name='building_doc_category["+lastRowId+"]']").val() =='')) {
         
          alert("Select Doc Category Or Please Upload ");
      }else{
        //Check maximum number of input fields
          if(x < maxField){ 
             
              x++; //Increment field counter
              var lastRowId       = lastRowId + 1;
              var fieldDocHTML = '<div class="row ro" id="'+lastRowId+'"><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-anchor icn-add" aria-hidden="true"></i><select class="form-control margin-top-8" id="building_doc_category"  name="building_doc_category['+lastRowId+']" ><option value="">Select </option> <option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="0">Mulkia</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : -1) == 0) ? 'selected' : '' }} value="1">Krooki</option> <option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="2">Waqala (POA)</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="3">Landlord ID</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="4">CR (if Landlord is a company)</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="5">Drawings</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="6">Building Permit</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="7">Civil Defense Certificate</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="9">AMC</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="10">Insurance</option><option  {{(old('building_doc_category', isset($building)?  $building->building_doc_category : '') == 1) ? 'selected' : '' }} value="8">Others</option>      </select></div> </div></div><div class="col-sm-5 "><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control upload_doc"  id="building_doc_path_name"  name="building_doc_path_name['+lastRowId+']"></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 2px"></div> <button type="button" class="btn btn-primary add_docs_button">Add</button><button title="Delete" type="button" class="btn btn-warning remove_doc_button" style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>';


              $(wrapperDoc).prepend(fieldDocHTML); //Add field html
              $('.field_wrapper_docs .row:not(:first)').find('.add_docs_button').remove();
              $('.field_wrapper_docs .row:not(:first)').find('.remove_doc_button').show();
          }
      }
    });
    $(wrapperDoc).on('click', '.remove_doc_button', function(e){
        e.preventDefault();
       
        
             $(this).closest('.row').remove();
          
          
        x--; //Decrement field counter

    });
function fileUpload(file){
   
    var currentRowId    = parseInt(file.closest('.increment').attr('id'));
    var lastRowId       = parseInt($(".increment").first().attr("id"));
    
    $('.upload').each(function() {
        $(this).rules("add", 
            {
                extension:"Pdf|Doc|Docx|Jpeg|Jpg",
                filesize: {{$upload_size}},
                messages: {
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
                }
            });
      });
      $(".upload").valid();
     
   }
  $.validator.addMethod('filesize', function(value, element, param) {
      // param = size (in bytes) 
      // element = element to validate (<input>)
      // value = value of the element (file name)
      return this.optional(element) || (element.files[0].size <= param) 
  });	
  function fileUploadDoc(file){
   
    var currentRowId    = parseInt(file.closest('.increment').attr('id'));
    var lastRowId       = parseInt($(".increment").first().attr("id"));
      
    $('.upload_doc').each(function() {
        $(this).rules("add", 
            {
                extension:"Pdf|Doc|Docx|Jpeg|Jpg",
                filesize: {{$upload_size}},
                messages: {
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
                }
            });
      });
     $(".upload_doc").valid();
     
     
   }
   $.validator.addMethod('filesize', function(value, element, param) {
      // param = size (in bytes) 
      // element = element to validate (<input>)
      // value = value of the element (file name)
      return this.optional(element) || (element.files[0].size <= param) 
  });
/*****************************************************************************************/
</script>

<style type="text/css">
.pac-container {
    z-index: 99999 !important;
}
</style>
