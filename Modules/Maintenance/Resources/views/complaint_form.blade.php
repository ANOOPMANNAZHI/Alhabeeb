  
          <!-- -------------------------- Maintenance Div ----------------------------------- -->
 
          @if((isset($complaintEnquiry) ) || (!isset($enquiry)&& !isset($complaintEnquiry)) || $type==3 ) 
          <div class="tab-pane  {{( in_array(Route::currentRouteName(),['complaint.create','complaint.edit']))? 'active': ''}}    @if(isset($type)) {{($type==3)?'active':''}} @else 'active'    @endif " id="maintenance">
            <form method="post" class="js-validation" autocomplete="off" id="maintenance-form" action="{{isset($complaintEnquiry)? route( 'complaint.update',$complaintEnquiry->id) : route( 'complaint.store')}}">
              @csrf  @if(isset($complaintEnquiry)){{method_field('PUT')}}@endif
              <div class="clearfix"></div>

               
                <div class="row"  style="{{(Route::currentRouteName() != 'complaint.edit')? '' : 'display:none'}}">
                  <div class="col-sm-12">
				  <div class="dataSearchBox">
                    <div class="form-group"> 
                    <label for="complaint_no"><b>Category</b><small class="textRed">*</small></label>                  
                    <div class="p-relative checkbox-mrg">                           
                      <input type="radio"   @if(!isset($complaintEnquiry)) checked  @elseif(!empty($complaintEnquiry->tenant_id)) checked  @endif  id="cmp_type_1"  name="cmp_type" value="1" class="cmp_type mdl-switch__input ">  <label class="checkbox-label"> Occupied </label>
                      <input @if(empty($complaintEnquiry->tenant_id) && !empty($complaintEnquiry->unit_id) ) checked  @endif  type="radio" name="cmp_type" id="cmp_type_2"   value="2" class="cmp_type mdl-switch__input ">  <label class="checkbox-label"> Vacant </label>
                      <input @if(isset($complaintEnquiry) && empty($complaintEnquiry->unit_id) ) checked  @endif type="radio" id="cmp_type_3" name="cmp_type" value="3" class="cmp_type mdl-switch__input ">  
                      <label class="checkbox-label"> Other </label>
                    </div>
                   </div>
                 </div>
				 </div>
               </div>
            @if(isset($complaintEnquiry))
               <div class="row">
                  <div class="col-sm-12">
                 <span> <strong>Category</strong>   :   {{$complaintEnquiry->category_name}} </span>
                  </div>
                </div>
            @endif  

              <div class="dataSearchBox">
                <input type="hidden" name="tenantStatus" id="tenantStatus" value="">
                <div class=" tenantStatus">

                </div>
                
                <div class="row">
                  <div class="col-sm-6 ">
                    <div class="form-group">
                      <label for="complaint_no">Complaint No<small class="textRed">*</small></label>
                      <div class="p-relative">
                       <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                       <input required type="text" class="form-control" id="complaint_no" readonly name="complaint_no" value="{{ old('complaint_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_no : $nextcomplaintCode )}}"  placeholder="Enter Comp No">
                     </div>
                   </div>
                 </div>
                 <div class="col-sm-6">
                  <div class="form-group">
                    <label for="complaint_date">Complaint Date<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                     <input required type="date" class="form-control" id="complaint_date" name="complaint_date" value="{{ old('complaint_date', isset($complaintEnquiry)? $complaintEnquiry->complaint_date->format('Y-m-d') : today()->format('Y-m-d')  )}}"  placeholder="Enter Comp Date">
                   </div>
                 </div>
               </div>
               <div class="w-100"></div>
               <div class="col-sm-6 row-even occupied">
                <div class="form-group">
                  <label for="registerd_mob_no">Registered Mobile No<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                   <input  onkeypress="return isNumber(event)"  type="text" class="form-control " data-rule-pattern="^((\+)?(\d{2,2}))?(\d{8}){1}?$" data-msg-pattern="Allowed only 8 Digit Numeric Values" id="registerd_mob_no"  name="registerd_mob_no" value="{{ old('registerd_mob_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  )}}"  placeholder="Enter Registered Mobile No">
                 </div>
               </div>
             </div>
             <div class="col-sm-6 row-even occupied">
              <div class="form-group">
                <label for="resident_card_id">Resident Card ID</label>
                <div class="p-relative">
                 <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                 <input  type="text" class="form-control  cmp_comp_search" id="resident_card_id"  name="resident_card_id" value="{{ old('resident_card_id', isset($complaintEnquiry)? ($complaintEnquiry->tenant->resident_id ?? '' ) : ''  )}}"  placeholder="Enter Resident Card ID">
               </div>
             </div>
           </div>
           <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
              <label for="complainer_name">Complainer Name<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
               <input required type="text" class="form-control" id="complainer_name"  name="complainer_name" value="{{ old('complainer_name', isset($complaintEnquiry)? $complaintEnquiry->complainer_name : ''  )}}"  placeholder="Enter Comp Name">
             </div>
           </div>
         </div>

         <div class="col-sm-6">
          <div class="form-group">
            <label for="complaint_mob_no">Complainer Mobile No<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
             <input  data-rule-pattern="^((\+)?(\d{2,2}))?(\d{8}){1}?$" data-msg-pattern="Allowed only 8 Digit Numeric Values"  required type="text" class="form-control" id="complaint_mob_no"  name="complaint_mob_no" value="{{ old('complaint_mob_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  )}}"  placeholder="Enter Comp Mobile No">
           </div>
         </div>
       </div>
       <div class="w-100"></div>
       <div class="col-sm-6 building_text">
        <div class="form-group">
          <label>Building <small class="textRed">*</small></label>
          <div class="p-relative">
           <i class="icon icon-building" aria-hidden="true"></i>
           <input type="text" name="building_text" id="building_text" class="form-control building_text " placeholder="Enter Building Name" value="{{ old('building_text', isset($complaintEnquiry)? $complaintEnquiry->building->building_name : ''  )}}" required> 
           <input type="hidden" name="building_text_id" id="building_text_id" class="form-control building_text" placeholder="Enter Building Name" value="{{ old('building_text_id', isset($complaintEnquiry)? $complaintEnquiry->building_id : ''  )}}"  >
         </div>
       </div> 
     </div>
     <div class="col-sm-6 building_select" style="display: none">
      <div class="form-group">
        <label>Building <small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="icon icon-building" aria-hidden="true"></i>
        <select name="building_id" required class="form-control Building cmp_comp_search" id="building_id">
          <option value="">Select</option>
          @foreach($buildings as $building)
          <option {{ (old('building_id',isset($complaintEnquiry)?  $complaintEnquiry->building_id : '') == $building->id)?  'selected':''  }}  value="{{$building->id}}">{{$building->building_code}}</option>
          @endforeach
        </select> 
      </div>
    </div> 
  </div>
  <input type="hidden" name="buildings" id="buildings" class="form-control" placeholder="Enter Building Name" value="{{ old('buildings', isset($complaintEnquiry)? $complaintEnquiry->building_id : ''  )}}"> 

<div class="col-sm-6">
   <div class="unit_text vacant">
        <div class="form-group">
          <label>Unit <small class="textRed">*</small></label>
          <div class="p-relative">
          <i class="icon icon-unit" aria-hidden="true"></i>    
           <input type="text" name="unit_text" id="unit_text" class="form-control unit_text " placeholder="Enter Unit Name" value="{{ old('unit_text', isset($complaintEnquiry)? ($complaintEnquiry->Unit->unit_code ?? '' ) : ''  )}}"> 

           <input type="hidden" name="unit_text_id" id="unit_text_id" class="form-control unit_text" placeholder="Enter Unit Name" value=""  >
         </div>
       </div> 
     </div>

  <div class="unit_select " style="display: none">
    <div class="form-group">
      <label>Unit <small class="textRed">*</small></label>
      <div class="p-relative">

      <i class="icon icon-unit" aria-hidden="true"></i>      
       <select name="unit_id"  class="form-control complaintUnit cmp_comp_search" id="unit_id">
        <option value="">Select</option>
        @if(isset($complaintEnquiry))
        @foreach($com_unit as $unit)
        <option {{ (old('unit_id',isset($complaintEnquiry)?  $complaintEnquiry->unit_id : '') == $unit->id)?  'selected':''  }}  value="{{$unit->id}}">{{$unit->unit_code}}</option>
        @endforeach
        @endif
      </select> 
    </div>
  </div> 
</div> 

 <input type="hidden" name="unit" id="unit" class="form-control"  value="{{ old('unit', isset($complaintEnquiry)? $complaintEnquiry->unit_id : ''  )}}"> 
</div>


<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group occupied">
    <label for="tenant_name">Tenant<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="icon icon-tenant" aria-hidden="true"></i>
     <input  type="text" class="form-control read" id="tenant_name" name="tenant_name" value="{{ old('tenant_name', isset($complaintEnquiry->tenant_id)? $complaintEnquiry->tenant->tenant_name : ''  )}}"  placeholder="Enter Tenant Name">
     <input type="hidden" name="tenant_id" id="tenant_id" value="{{ old('tenant_id', isset($complaintEnquiry)? $complaintEnquiry->tenant_id : ''  )}}">
   </div>
 </div>
</div>
<div class="col-sm-6  occupied">
  <div class="form-group">
    <label for="occupant_name">Occupant Name</label>
    <div class="p-relative">
     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
     <select name="occupant_name" class="form-control read" id="occupant_name">
      
      @if(isset($complaintEnquiry->occupant_id))
		<option value="{{$complaintEnquiry->occupant_id}}">{{$complaintEnquiry->occupant->occupant_name}}</option>
	  @else
		<option value="">Select</option>
      @endif 
    </select>
    <input type="hidden" name="occupant_id" id="occupant_id" value="{{ old('occupant_id', isset($complaintEnquiry)? $complaintEnquiry->occupant_id : ''  )}}">

  </div>
</div>
</div>
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="location_name">Location<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-globe icn-add" aria-hidden="true"></i>
     <input required type="text" class="form-control read" id="location_name" name="location_name" value="{{ old('location_name', isset($complaintEnquiry)? $complaintEnquiry->location->locations_name : ''  )}}"  placeholder="Enter Location">
     <input type="hidden" name="tenant_location_id" value="{{ old('tenant_location_id', isset($complaintEnquiry)? $complaintEnquiry->location_id : ''  )}}" id="tenant_location_id">
   </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="way_no">Way No</label>
    <div class="p-relative">
     <i class="fa fa-address-card icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control read" id="way_no" name="way_no" value="{{ old('way_no', isset($complaintEnquiry)? $complaintEnquiry->way_no : ''  )}}"  placeholder="Enter Way No">
   </div>
 </div>
</div>   
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="priority_status">Priority</label>
    <div class="p-relative">
     <i class="fa fa-spinner icn-add" aria-hidden="true"></i>
     <select name="priority_status" class="form-control">
      <option {{ (old('priority_status',isset($complaintEnquiry)? $complaintEnquiry->priority_status : '') == 0)? 'selected':'' }}  value="0">Normal</option>
      <option {{ (old('priority_status',isset($complaintEnquiry)? $complaintEnquiry->priority_status : '') == 1)? 'selected':'' }}  value="1">High</option>
    </select>
  </div>
</div>
</div> 



<div class="col-sm-6">
  <div class="form-group">
    <label >Preferred Time</label>
    <div class="p-relative checkbox-mrg"> 
      @foreach($preferred_time as $val)    
      <input type="checkbox"
      @if(old('preferred_time[]'))  {{in_array($val->id,old('preferred_time[]'))? 'checked': ''}} 
      @elseif(isset($complaintEnquiry))  {{in_array($val->id,$complaintEnquiry->preferredTime->pluck('id')->all())? 'checked': ''}}  @endif  name="preferred_time[]"  value="{{$val->id}}"  class="mdl-switch__input ">  <label class="checkbox-label"> {{$val->pre_time}} </label>
      @endforeach 
    </div>
  </div>
</div>                   

</div>
</div>
<!-- resh -->
@if(!isset($complaintEnquiry))
<!--new -->
<div class="clearfix"></div>
<div class="dataSearchBox panel-heading-lightblue">
  <h4>Ticket List <small class="textRed">*</small></h4>
  <div class="row">
<table id="ContentPlaceHolder1_AccountsTable" class="complaint_tbl">
 @foreach($works as $work)
        <tr>
            <td><span class="AccountSelectBox"> <input type="checkbox" class="group_ctrl" name="works_id[{{$loop->index}}]" value="{{$work->id}}"><label class="checkbox-label">{{$work->works_code}}</label></span></td>

            <td><textarea class="form-control group_b AccountAmountBox" rows="2" placeholder="Enter Description" name="checklist_des[{{$loop->index}}]" id="checklist_desc" disabled="disabled"></textarea></td>
        </tr>
        @endforeach
        </table>
        </div>
        </div>
<!--new -->

@endif

@if(isset($complaintEnquiry))
<div class="dataSearchBox panel-heading-lightblue">
  <h4>Ticket List</h4>
  <div class="row">
   @foreach($complaintEnquiry->complaintTickets as $complaintTickets)
   <div class="col-sm-2"></div>
   <div class="col-sm-3">
     <input type="hidden" name="complaint_checklist_id[{{$loop->index}}]" value="{{$complaintTickets->id}}">
     <select class="form-control worksIds" id="work_id" name="work_id[{{$loop->index}}]" required>
      @if(!isset($complaintEnquiry))
      <option value="">Select Category </option>
      @endif
      @foreach($works as $work)
      <option {{(old('work_id',isset($complaintTickets->work_id)?  $complaintTickets->work_id : '') == $work->id)?  'selected':''  }} value="{{$work->id}}">{{$work->works_code}} </option>
      @endforeach
    </select>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <textarea class="form-control checkList" rows="2" placeholder="Enter Description" name="checklist_desc[{{$loop->index}}]"  id="checklist_desc">{{$complaintTickets->checklist_desc}}</textarea>
    </div>
  </div>
  <div class="col-sm-3"></div>
  @endforeach
</div>
</div>
@endif
<!-- resh -->
<div class="align-left"><button type="submit" class="btn btn-primary save_compliant">Save</button></div>
</form>
</div>
@endif


