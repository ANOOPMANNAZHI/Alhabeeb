@extends('layouts.plms-app')


@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">AMC Schedule  {{ (isset($amcSchedule))? 'Edit' : 'Add'}} </div>
    </div>
    {{ (isset($amcSchedule))?   Breadcrumbs::render('amcSchedule.edit',$amcSchedule,Session::get('current')) :  Breadcrumbs::render('amcSchedule.create') }} 



  </div>
</div>


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">



<style>
  body {counter-reset:section 0 sec 0;}
  .counters:before
  {
    counter-increment:section;
    content:counter(section);
  }
  .countn:before
  {
    counter-increment:sec;
    content:counter(sec);
  }
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;

    select {
  /* for Firefox */
  -moz-appearance: none;
  /* for Chrome */
  -webkit-appearance: none;
}
/* For IE10 */
select::-ms-expand {
  display: none;
}
}

</style>

<div class="row">  

  <!-- activities -->
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     

          <!-- -------------------------- Amc Contract  Div ----------------------------------- -->
          {{-- @if(isset($amcSchedule)) --}}
          <div class="tab-pane active" id="maintenance">
            <form method="post" autocomplete="off" id="schedule-form" action="{{isset($amcSchedule)? route( 'amcSchedule.update',$amcSchedule->id) : route( 'amcSchedule.store')}}">
             @csrf    @if(isset($amcSchedule)){{method_field('PUT')}}@endif 
             <div class="clearfix"></div>
             <div class="dataSearchBox">
              <input type="hidden" name="tenantStatus" id="tenantStatus" value="">
              <div class=" tenantStatus">

              </div>

              <div class="row">
                <div class="col-sm-8 building_select">
                  <div class="form-group">
                    <label>Contractor Type <small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-building icn-add" aria-hidden="true"></i>
                     <select class="form-control" id="contract_type" name="contract_type" required {{  isset($amcSchedule)?'disabled' : '' }} >
                      <option value="">Select</option>
                      <option  {{(old('contract_type', isset($amcSchedule->vendor_id)?  $amcSchedule->vendor_id : '')) ? 'selected' : '' }} value="sub_contractor">Sub-Contractor</option>
                      <option {{(old('contract_type', isset($amcSchedule->user_id)?  $amcSchedule->user_id : '')) ? 'selected' : '' }} value="technician">In-house</option>
                    </select>

                    <select class="form-control" id="contract_types" name="contract_types" required style="display: none;">
                      <option value="">Select</option>
                      <option  {{(old('contract_type', isset($amcSchedule->vendor_id)?  $amcSchedule->vendor_id : '')) ? 'selected' : '' }} value="sub_contractor">Sub-Contractor</option>
                      <option {{(old('contract_type', isset($amcSchedule->user_id)?  $amcSchedule->user_id : '')) ? 'selected' : '' }} value="technician">In-house</option>
                    </select>

                  </div>
                </div> 
              </div>
            </div>
            <input type="hidden" name="amc_schedule_id" value="{{ old('amc_schedule_id', isset($amcSchedule)? $amcSchedule->id : '' )}}">
            <!-- subcontractor div starts -->
            <div class="subcontractor" id="subcontractor">
          
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="amc_contract_no">Contract No<small class="textRed">*</small></label>
                    <div class="p-relative">
                      <i class="fa fa-list icn-add" aria-hidden="true"></i>
                      <input  type="text" class="form-control" id="amc_contract_no"  name="amc_contract_no" value="{{ old('amc_contract_no', isset($amcSchedule->amc_contract_id)? $amcSchedule->amcContract->amc_contract_no : '' )}}"  placeholder="Enter Contract No" required {{  isset($amcSchedule)?'disabled' : '' }}>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="amc_contract_id" value="{{ old('amc_contract_id', isset($amcSchedule->amc_contract_id)? $amcSchedule->amc_contract_id : '' )}}" id="amc_contract_id">
                <input type="hidden" name="amc_contract_num" id="amc_contract_num" value="{{ old('amc_contract_num', isset($amcSchedule->amc_contract_id)? $amcSchedule->amc_contract_id : '' )}}" >
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="vendor_name">Contractor<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                     <input  type="text" class="form-control read" id="vendor_name"  name="vendor_name" value="{{ isset($amcSchedule->vendor_id)?  old('vendor_name',$amcSchedule->vendor->vendor_name): old('vendor_name')}}"  placeholder="Enter Contractor Name" required>
                   </div>
                 </div>
               </div>
               <input type="hidden" class="form-control" id="vendor_code" name="vendor_code" value="{{ isset($amcSchedule->vendor_id)?  old('vendor_code',$amcSchedule->vendor_id): old('vendor_code')}}" readonly>
               <div class="col-sm-6">
                <div class="form-group">
                  <label for="building_name">Building<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                   <input  type="text" class="form-control read" id="building_name"  name="building_name" value="{{ isset($amcSchedule->building_id)?  old('building_name',$amcSchedule->building->building_name): old('building_name')}}"  placeholder="Enter Building Name" required>
                 </div>
               </div>
             </div>
             <input type="hidden" name="building_id" id="building_id" value="{{ isset($amcSchedule->building_id)?  old('building_id',$amcSchedule->building_id): old('building_id')}}" >
             <div class="col-sm-6 unit_select">
              <div class="form-group">
                <label>Unit</label>
                <div class="p-relative">
                 <i class="fa fa-home icn-add" aria-hidden="true"></i>
                 <select name="unit_id"  class="form-control complaintUnit read unitExist" id="unit_id"  {{  isset($amcSchedule->taskStatus)?'disabled' : '' }}>
                 <option value = "">Select Unit</option>
                  @if(isset($amcSchedule))
                   @foreach($units as $unit)
                    <option {{(old('unit_id',isset($amcSchedule->unit_id)?  $amcSchedule->unit_id : '') == $unit->id)?  'selected':''  }}   value="{{$unit->id}}"  >{{$unit->unit_code}}</option>
                    @endforeach
                   @endif
                </select> 
              </div>
            </div> 
          </div>  


          <div class="col-sm-6">
            <div class="form-group">
              <label for="amc_schedule_period_from">Start Date<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
               <input  type="date" class="form-control read" id="amc_schedule_period_from" name="amc_schedule_period_from" value="{{ old('amc_schedule_period_from', isset($amcSchedule)? $amcSchedule->amc_schedule_period_from->format('Y-m-d') : ''  )}}"  placeholder="Enter Start Date" required>
             </div>
           </div>
         </div>
         <div class="col-sm-6">
          <div class="form-group">
            <label for="amc_schedule_period_to">End Date<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
             <input  type="date" class="form-control read" id="amc_schedule_period_to" name="amc_schedule_period_to" value="{{ old('amc_schedule_period_to', isset($amcSchedule)? $amcSchedule->amc_schedule_period_to->format('Y-m-d') : ''  )}}"  placeholder="Enter End Date" required >
           </div>
         </div>
       </div>

       <div class="col-sm-6">
        <div class="form-group">
          <label for="amc_contract_period_to">Frequency<small class="textRed">*</small></label>
          <div class="p-relative">
           <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
           <input  type="text" class="form-control read" id="payment_method_id" name="payment_method_id" value="{{ isset($amcSchedule->payment_method_id)?  old('payment_method_code',$amcSchedule->paymentMethod->payment_method_code): old('payment_method_code')}}"  placeholder="Enter Frequency" required>

         </div>
       </div>
     </div>
     <input type="hidden" name="frequency" id="frequency" value="{{ isset($amcSchedule->payment_method_id)?  old('frequency',$amcSchedule->payment_method_id): old('frequency')}}" >
     <input type="hidden" name="no_days_sub" id="no_days_sub" value="{{ old('no_days', isset($amcSchedule)? $days : ''  )}}" >
     <div class="col-sm-6">
      <div class="form-group">
        <label for="amc_schedule_description">Description<small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
         <textarea class="form-control" name="amc_schedule_description" id="amc_schedule_description"  cols="55" placeholder="Enter Description" required>{{ old('amc_schedule_description', isset($amcSchedule)? $amcSchedule->amc_schedule_description : ''  )}}</textarea>
       </div>
     </div>
   </div>
 </div>
 <div class="clearfix"></div>
 <div class="dataSearchBox panel-heading-lightblue">
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box">
      <div class="card-body ">
        <div class="col-md-12">
          <div class="table-wrap">
            <div class="table-responsive">
              <table class="table display product-overview mb-30" id="support_table5">
                <thead>
                  <tr>
                    <th>Serial No</th>
                    <th>Amenity</th>
                    <th>Amenity Code</th>
                  </tr>
                </thead>
                <tbody id="complaint_form">
                  <tr class="tr" id=""><input type="hidden"  id="amenities_type_id" value="">
                  </tr> 
                  @if(isset($amcSchedule))
                  @forelse ($amcScheduleAmenities as $amcScheduleAmenity)
                  <tr class="tr" id="{{$loop->iteration}}"><input type="hidden" name="amc_contract_aminities_id[]" value="{{$amcScheduleAmenity->id}}">
                    <td id="no{{$loop->iteration}}" class="counters"></td>
                    <td id="work_id{{$loop->iteration}}">{{$amcScheduleAmenity->amenityType->amentity_types_name}}<input type="hidden" name="amenities_type_id[]" id="amentities_types_id" value="{{$amcScheduleAmenity->amenityType->id}}"></td>
                    <td id="work_id{{$loop->iteration}}">{{$amcScheduleAmenity->amenityType->amentity_types_code}}<input type="hidden" name="amenities_type_idss[]" value="{{$amcScheduleAmenity->amenityType->id}}"></td>
                    <!-- <td id="action{{$loop->iteration}}">
                      <a href="{{route('amcSchedule.destroy',$amcScheduleAmenity->id)}}" class="btn btn-tbl-delete btn-xs" type="button">
                        <i class="fa fa-trash-o "></i>
                      </a>
                    </td>  -->                           

                  </tr>  
                  @empty
                  <tr>
                    <td colspan="4" align="center">
                      <p>No record</p>
                    </td>
                  </tr>
                  @endforelse  
                  @endif 

                </tbody>
              </table>
            </div>
          </div>  
        </div>
      </div>
    </div>
  </div>
</div>
<button type="button" class="btn btn-primary dataSearchLabel process_amenity_subcontractor">Process</button>

<div class="dataSearchBox panel-heading-lightblue">
  <div class="row" style="float: right;">
    <button type="button" class="btn btn-primary AddTaskSub" data-toggle="modal" data-target="#myModal">Add </button>
  </div>
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box" style="margin-top: 45px;">
      <div class="card-body ">
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table display product-overview mb-30" id="support_table5">
              <thead>
                <tr>
                  <th>Start Dt</th>
                  <th>End Dt</th>
                  <th>Amenity</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="process_form_sub">
              <input type="hidden" name="counters" class="counters">
               @if(isset($amcSchedule))
               @forelse ($amcTasks as $amcTask)
               <tr class="tr" id="{{$loop->iteration}}"><input type="hidden" name="amc_contract_aminities_id[]" value="{{$amcTask->id}}">
                <td id="work_id{{$loop->iteration}}">{{$amcTask->amc_schedule_from_date->format('d/m/Y')}}<input type="hidden" name="amc_schedule_period_from_text_sub[]" value="{{$amcTask->amc_schedule_from_date}}"></td>

                <td id="work_id{{$loop->iteration}}">{{$amcTask->amc_schedule_to_date->format('d/m/Y')}}<input type="hidden" name="amc_schedule_period_to_text_sub[]" value="{{$amcTask->amc_schedule_to_date}}"></td>

                <td id="work_id{{$loop->iteration}}">{{$amcTask->amenityType->amentity_types_name}}<input type="hidden" name="amentity_types_idd_sub[]" value="{{$amcTask->amenities_type_id}}"></td>

                <td id="amc_schedule_status{{$loop->iteration}}">@if($amcTask->amc_schedule_task_status==0)<button type="button" class="btn label label-primary label-mini">OPEN</button>@endif
                  @if($amcTask->amc_schedule_task_status==1)<button type="button" class="btn label label-danger label-mini">CLOSED</button>@endif
                </td>
                <td id="action{{$loop->iteration}}">
                  
                  <!-- <a href="{{route('amcTask.destroy',$amcTask->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                    <i class="fa fa-trash-o "></i>
                  </a> -->
                  <button class="btn btn-tbl-delete btn-xs remove_amenity"  type="button">
                      <i class="fa fa-trash-o "></i>
                  </button>
                  <button type="button" class="btn btn-tbl-edit btn-xs updateEditedAmenity" title="Update Amenity" data-toggle="modal" data-target="#myModal" data-id="{{$amcTask->amc_schedule_id}}"  datas-id = "{{$amcTask->id}}" datas-idd="{{$amcTask->amenities_type_id}}"><i class="fa fa-pencil"></i></button>
                </td>
              </tr>  
              @empty
              <tr>
                <td colspan="5" >
                  <p>No record</p>
                </td>
              </tr>
              @endforelse  
              @endif
            </tbody>
          </table>
        </div>
      </div>  
    </div>
  </div>
</div>
</div>
</div>
<div class="clearfix"></div>
<!--end of subcontractor div -->
<div class="row technician" id="technician">
<input type="hidden" name="contractTypeTech" value="technician">
  <div class="col-sm-6 building_select">
    <div class="form-group">
      <label>Engineer <small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-building icn-add" aria-hidden="true"></i>
       <select class="form-control" id="vendor_name" name="vendor_name" required>
        <option value="{{ isset($amcSchedule) ? old('user_id',$amcSchedule->user_id): '' }}">{{ isset($amcSchedule->user_id)?  old('user_id',$amcSchedule->technician->username): 'Select' }}</option>
        @foreach($technicians as $technician)
        <option  {{(old('id', isset($amcSchedule->user_id)?  $amcSchedule->id : 0) == $technician->id) ? 'selected' : '' }} value="{{$technician->id}}">{{$technician->username}}</option>
        @endforeach
      </select> 
    </div>
  </div> 
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="building_text">Building<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
     <input  type="text" {{  isset($amcSchedule)?'disabled' : '' }} class="form-control read" id="building_text"  name="building_text" value="{{ old('building_name', isset($amcSchedule)? $amcSchedule->building->building_name : '' )}}"  placeholder="Enter Building Name" required>
   </div>
 </div>
</div>
<input type="hidden" name="building_text_id" id="building_text_id" value="{{ old('building_name', isset($amcSchedule)? $amcSchedule->building_id : '' )}}" >
<div class="col-sm-6 building_select">
  <div class="form-group">
    <label>Unit</label> 
    <div class="p-relative">
     <i class="fa fa-building icn-add" aria-hidden="true"></i>
     <select class="form-control unitExist" id="unit_text_id" name="unit_text_id" {{  isset($amcSchedule->taskStatus)?'disabled' : '' }}>
     <option value = "">Select Unit</option>
    @if(isset($amcSchedule))

   @foreach($units as $unitt)
    <option {{(old('unit_id',isset($amcSchedule->unit_id)?  $amcSchedule->unit_id : '') == $unitt->id)?  'selected':''  }}   value="{{$unitt->id}}"  >{{$unitt->unit_code}}</option>
    @endforeach
   @endif
      
    </select> 
  </div>
</div> 
</div>
<!-- <input type="hidden" name="building_id" id="building_id" value="" > -->
<div class="col-sm-6">
  <div class="form-group">
    <label for="amc_schedule_period_from_text">Start Date<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
     <input  type="date" class="form-control date_range" id="amc_schedule_period_from_text" name="amc_schedule_period_from_text" value="{{ old('amc_schedule_period_from', isset($amcSchedule)? $amcSchedule->amc_schedule_period_from->format('Y-m-d') : ''  )}}"  placeholder="Enter Start Date" required onkeydown="return false">
     
   </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="amc_schedule_period_to_text">End Date<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
     <input  type="date" class="form-control date_range" id="amc_schedule_period_to_text" name="amc_schedule_period_to_text" value="{{ old('amc_schedule_period_to', isset($amcSchedule)? $amcSchedule->amc_schedule_period_to->format('Y-m-d') : ''  )}}"  placeholder="Enter End Date" required onkeydown="return false">
   </div>
 </div>
</div>

<div class="col-sm-6 building_select">
  <div class="form-group">
  <input type="hidden" name="method" id="method" value="{{ old('method', isset($amcSchedule)? $amcSchedule->payment_method_id : '' )}}">

   <input type="hidden" name="method_text" id="method_text" value="{{ old('method_text', isset($amcSchedule)? $amcSchedule->paymentMethod->payment_method_code : '' )}}">

    <label>Frequency <small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-building icn-add" aria-hidden="true"></i>
     <select class="form-control" id="payment_method_id_text" name="payment_method_id_text" required>
       <!-- <option value="{{ isset($amcSchedule) ? old('payment_method_id',$amcSchedule->payment_method_id): '' }}">{{ isset($amcSchedule)?  old('payment_method_id',$amcSchedule->paymentMethod->payment_method_code): 'Select' }}</option> -->
     </select> 
   </div>
 </div> 
</div>
<input type="hidden" name="frequency_type" id="frequency_type" value="">
<input type="hidden" name="no_days" id="no_days" value="{{ old('no_days', isset($amcSchedule)? $days : ''  )}}">
<div class="col-sm-6">
  <div class="form-group">
    <label for="amc_schedule_description_text">Description<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
     <textarea class="form-control" name="amc_schedule_description_text" id="amc_schedule_description_text"  cols="55" placeholder="Enter Description" required>{{ old('amc_schedule_description', isset($amcSchedule)? $amcSchedule->amc_schedule_description : ''  )}}</textarea>
   </div>
 </div>
</div>


<div class="dataSearchBox panel-heading-lightblue">
  <h4>Select  Amenity</h4>
  <div class="row bb-1 mb-3">
    <div class="col-sm-4">
      <label for="simpleFormEmail">Amenity</label>
      <select class="form-control" id="amentity_types_id" name="amentity_types_id" >
        <option value="">Select Amenity </option>
      </select>
    </div>

    <div class="col-sm-4">
      <div class="dataSearchLabel w-100"></div>
      <button type="button" class="btn btn-primary dataSearchLabel add_amenity btnDisable">Add</button>
    </div>
    <div class="col-sm-4">
    </div>
  </div>
</div>
<div class="clearfix"></div>
<div class="dataSearchBox panel-heading-lightblue">
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box">
      <div class="card-body ">
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table display product-overview mb-30" id="support_table5">
              <thead>
                <tr>
                  <th>Serial No</th>
                  <th>Amenity</th>
                  <th>Amenity Code</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="amenity_form">
               @if(isset($amcSchedule))
               @forelse ($amcScheduleAmenities as $amcScheduleAmenity)
               <tr class="tr" id="{{$loop->iteration}}"><input type="hidden" name="amc_contract_aminities_id[]" value="{{$amcScheduleAmenity->id}}">
                <td id="no{{$loop->iteration}}" class="counters"></td>
                <td id="work_id{{$loop->iteration}}">{{$amcScheduleAmenity->amenityType->amentity_types_name}}<input type="hidden"  name="amentity_types_tech_id[]" id="amentity_types_tech_id" value="{{$amcScheduleAmenity->amenityType->id}}" class="amentity_types_tech_id"></td>
                <td id="work_id{{$loop->iteration}}">{{$amcScheduleAmenity->amenityType->amentity_types_code}}<input type="hidden" name="amenities_type_idss[]" value="{{$amcScheduleAmenity->amenityType->id}}"></td>
                <td id="action{{$loop->iteration}}">
                  <!-- <a href="{{route('amcSchedule.destroy',$amcScheduleAmenity->id)}}" class="btn btn-tbl-delete btn-xs delete_type" type="button">
                    <i class="fa fa-trash-o "></i>
                  </a> -->
                  <!-- <button class="btn btn-tbl-delete btn-xs remove_amenity"  data-id = "{{$amcScheduleAmenity->amenityType->id}}"  type="button">
                      <i class="fa fa-trash-o "></i>
                  </button> -->
                </td>                            

              </tr>  
              @empty
              <tr>
                <td colspan="4" align="center">
                  <p>No record</p>
                </td>
              </tr>
              @endforelse  
              @endif 
            </tbody>
          </table>
        </div>

        <div class="dataSearchLabel w-100"></div>
        <button type="button" class="btn btn-primary dataSearchLabel process_amenity">process</button>


      </div>  
    </div>
  </div>
</div>
</div>
<!--process amenity -->



<div class="dataSearchBox panel-heading-lightblue">
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box">
      <div class="card-body ">
         <button type="button" class="btn btn-primary AddTask align-right" data-toggle="modal" data-target="#myModal">Add </button>
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table display product-overview mb-30 " id="process_tech">
              <thead>
                <tr>
                  <th>Start Dt</th>
                  <th>End Dt</th>
                  <th>Amenity</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="process_form">
               @if(isset($amcSchedule))
               @forelse ($amcTasks as $amcTask)
               <tr class="tr" id="{{$amcTask->amenities_type_id}}"><input type="hidden" name="amc_contract_aminities_id[]" value="{{$amcTask->id}}">
                <td id="work_id{{$loop->iteration}}">{{$amcTask->amc_schedule_from_date->format('d/m/Y')}}<input type="hidden" name="amc_schedule_from_date[]" value="{{$amcTask->amc_schedule_from_date->format('Y-m-d')}}"></td>

                <td id="work_id{{$loop->iteration}}">{{$amcTask->amc_schedule_to_date->format('d/m/Y')}}<input type="hidden" name="amc_schedule_to_date[]" value="{{$amcTask->amc_schedule_to_date->format('Y-m-d')}}"></td>

                <td id="work_id{{$loop->iteration}}">{{$amcTask->amenityType->amentity_types_name}}<input type="hidden" name="amentity_types_idd[]" value="{{$amcTask->amenities_type_id}}" id="amentity_types_idd"></td>

                <td id="amc_schedule_status{{$loop->iteration}}">@if($amcTask->amc_schedule_task_status==0)<button type="button" class="btn label label-primary label-mini">OPEN</button>@endif
                  @if($amcTask->amc_schedule_task_status==1)<button type="button" class="btn label label-danger label-mini">CLOSED</button>@endif
                </td>
                <td id="action{{$loop->iteration}}">
                  <!-- <a href="{{route('amcTask.destroy',$amcTask->id)}}" class="btn btn-tbl-delete btn-xs delete_type" type="button">
                    <i class="fa fa-trash-o "></i>
                  </a> -->
                  <button class="btn btn-tbl-delete btn-xs remove_amenity" type="button" data-id = "{{$amcTask->amenities_type_id}}">
                      <i class="fa fa-trash-o "></i>
                  </button>
                  <button type="button" class="btn btn-tbl-edit btn-xs updateEditedAmenity" title="Update Amenity" data-toggle="modal" data-target="#myModal" data-id="{{$amcTask->amc_schedule_id}}"  datas-id = "{{$amcTask->id}}" datas-idd="{{$amcTask->amenities_type_id}}"><i class="fa fa-pencil"></i></button>


                  <!-- <button type="button" class="btn btn-tbl-edit btn-xs updateAmenity" title="Update Amenity" data-toggle="modal" data-target="#myModal" data-id="{{$amcTask->id}}"  datas-id = "{{$amcTask->id}}"><i class="fa fa-pencil"></i></button> -->
                </td>                            

              </tr>  
              @empty
              <tr>
                <td colspan="4" align="center">
                  <p>No record</p>
                </td>
              </tr>
              @endforelse  
              @endif
            </tbody>
          </table>
        </div>
      </div>  
    </div>
  </div>
</div>
</div>
<!-- -->
</div>
<!--</div> -->
</div>

<div class="clearfix"></div>
<div class="col"><button type="submit" class="btn btn-primary submitBtn">{{ (isset($amcSchedule))? 'Update' : 'Save'}}</button></div>
</form>
</div>
{{-- @endif --}}
</div>
</div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@section('scripts')
@include('sales::add_sub_complaint_js')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script>

  /************************************************************/
  $(function() {
    //$("#schedule-form").validate();
    $('#subcontractor').show(); 
    $('#technician').hide(); 
    if($('#contract_type').val() == 'sub_contractor') {
      $('#subcontractor').show();
      $(".read").attr('readonly',true);  
      //$("#contract_type").attr('disabled',true);  
      $('#technician').hide();
    }else{
      $('#technician').show(); 
       //$("#contract_type").attr('disabled',true);
      $('#subcontractor').hide();
    }

    $('#contract_type').change(function(){
      if($('#contract_type').val() == 'sub_contractor') {
        $('#subcontractor').show(); 
        $('#technician').hide(); 
      } else {
        $('#technician').show(); 
        $('#subcontractor').hide(); 
      } 
    });
/************************************************************/
      $("#schedule-form").on('submit',function(e){
       
       var el = $(this);
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);
        var rowcCountSub = $('#process_form_sub tr').length;
        var rowcCount = $('#process_form tr').length;
        if($('#contract_type').val() == 'sub_contractor') {
          if(rowcCountSub < 1 ){          
            alert("Cannot Accept AMC Schedule Without Process/Task");
             return false;
          }
        } else{
          if(rowcCount < 1 ){          
            alert("Cannot Accept AMC Schedule Without Process/Task");
             return false;
          }
        }

      });
  });
  /************************************************************/



//AutoComplete For Amc Contract No-Subcontractor
/************************************************************/ 
$('#amc_contract_no').autocomplete({
  source : '{!!URL::route('amcContractNoAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#amc_contract_no").val('');
      $('#create_build_span').hide();
      $('#amc_contract_no-error').show();
    }else {
      $('#amc_contract_num').val(ui.item.ids);         
      $('#amc_contract_id').val(ui.item.amc_contract_id);         
      $('#building_id').val(ui.item.building);         
      $('#vendor_code').val(ui.item.vendor);         
      $('#frequency').val(ui.item.frequency);         
      $('#amc_schedule_period_from').val(ui.item.from);       
      $('#amc_schedule_period_to').val(ui.item.to);         
      $('#create_build_span').show();  
      $.ajax({
        method: "POST",
        url: "{{route('amcDetailsByContractNo')}}",
        data: { 'contract_no': ui.item.ids, 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          var results = $.parseJSON(data);
          $('#building_name').val(results[1].building_name); 
          if(results[2]!=null){
            $('#vendor_name').val(results[2].vendor_name);
          }
          if(results[3]!=null){
            $('#payment_method_id').val(results[3].payment_method_code);
          }
          $(".read").attr('readonly',true);
          var status = results[0].status;
        }
      }); 

      //fetching units
      if(ui.item.building !=null){
        var buil = ui.item.building;
        var uselected = '';
        $.ajax({
          method: "POST",
          url: "{{route('unitByBuilding')}}",
          data: {"id":buil,"_token": "{{ csrf_token() }}"},
          cache: false,
          dataType: "json",
          success: function(data){
            if(data.length > 0){
              $('#unit_id').empty();
              $('#unit_id').append('<option value = "">'+ 'Select Unit' +'</option>')
              $.each(data, function(key, value) {
                $('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
              });
            }
            else{
              $('#unit_id').html('<option value="">No Available Units</option>');
            }

          }
        });
      }   
      //fetching amenities
      $.ajax({
        method: "POST",
        url: "{{route('amenitiesByContractNo')}}",
        data: {"id":ui.item.ids,"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          console.log(data);
          if(data.length > 0){
            $('#complaint_form').empty();
            var i=0;
            $.each(data, function(key, value) {
              i=i+1;
              $('#complaint_form').append('<tr><td>'+ i +'</td><td>'+value['amenity_type']["amentity_types_name"]+'<input type="hidden" name="amentities_types_id" id="amentities_types_id" value="'+value['amenities_type_id']+'"></td><td>'+value['amenity_type']["amentity_types_code"]+'<input type="hidden" name="amentities_types_id" id="amentities_types_id" value="'+value['amenities_type_id']+'"></td></tr>');    
            });
          }
          else{
            $("#complaint_form").html('<tr><td colspan="3"><p>No Record</p></td></tr>');
          }

        }
      }); 
      //calculating no days
      var d1 = $('#amc_schedule_period_from').val();
      var d2 = $('#amc_schedule_period_to').val();

      var date1 = new Date(d1);
      var date2 = new Date(d2);

      var date1_ms = date1.getTime();
      var date2_ms = date2.getTime();

      var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days_sub').val(no_days);
        }



      }
    }); 
//building auotocomplete-Technician
/**************************************************************************************/

$('#building_text').autocomplete({
  source : '{!!URL::route('amcBuildingAutocomplete')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if(ui.item.ids !=null){
      $('#building_text_id').val(ui.item.ids);
      var buil = ui.item.ids;
      var uselected = '';
      //unit selecting
      $.ajax({
        method: "POST",
        url: "{{route('unitByBuilding')}}",
        data: {"id":buil,"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          if(data.length > 0){
            $('#unit_text_id').empty();
            $('#unit_text_id').append('<option value = "">'+ 'Select Unit' +'</option>')
            $.each(data, function(key, value) {
              $('#unit_text_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
            });
          }
          else{
            $('#unit_text_id').html('<option value="">No Available Units</option>');
          }

        }
      });
      //amenity selecting
      $.ajax({
       type: "GET",
       url: "{!!URL::route('getBuildingAmenity')!!}",
       data:'building_id='+ ui.item.ids,
       success: function(data){
        var selected = "";
          var result = $.parseJSON(data);
          //$("#amentity_types_id").html(data);
          $('#amentity_types_id').empty();
            $('#amentity_types_id').append('<option value="">'+ 'Select Amenity' +'</option>')
              $.each(result[0], function(key, value) {
                  $('#amentity_types_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
            });
         if(result[1] != ""){
            location.reload();
          }else{
            var data = "<tr><td colspan='4'><p>No Record</p></td></tr>";
            $('#amenity_form').html(data);
            
          }
      }
    }); 
 $('#amenity_form').html(""); 
 $('#process_form').html(""); 
    }

  }
});
/***************************************************************************************/
//adding amenity
$(document).on('click','.add_amenity',function(){ 
 /* $(".btnDisable").attr("disabled", true);*/
  var el = $(this);
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);

  var amentity_types_id = $("#amentity_types_id").val();
  var building_id = $("#building_id").val();
  if(building_id == "") building_id = $("#building_text_id").val();
  var unit_id = $("#unit_id").val();
  if(unit_id == "") unit_id = $("#unit_text_id").val();
  
  var no = $('#complaint_form tr').length+1;
  var td = $('#amenity_form').children('tr').children('td').length;
    var x=[];
  
  var inputs = document.getElementsByClassName( 'amentity_types_tech_id' ),
  names  = [].map.call(inputs, function( input ) {
    x.push(input.value);
      return input.value;
  }).join( ',' );
  if(x.indexOf(amentity_types_id) != -1){
    alert('Amenity Exists');

  }else{
  if(amentity_types_id !=""){
     $.ajax({
      method: "POST",
      url: "{{route('unitExist')}}",
      data: {building_id:building_id,unit_id:unit_id,amentity_types_id:amentity_types_id, "_token" : $('meta[name="csrf-token"]').attr('content')},
      success: function(data){
        if(data >0){
          alert("Contract Already Exist Against Unit");
        }else{
          $.ajax({
            method: "POST",
            url: "{{route('addAmcAmenity')}}",
            data: {no:no,amentity_types_id:amentity_types_id, "_token" : $('meta[name="csrf-token"]').attr('content')},
            success: function(data){                            
              if(data != 0){
                if(td == 1){
                  $('#amenity_form').html(data);
                  $('#amentity_types_id').val(""); 
                }else{
                  $('#amenity_form').append(data); 
                  $('#amentity_types_id').val("");
                }
                                          
              }               
            }           
          });
        }
      }
    });
    
  }else{
    alert("Please Select Amenity");
  }  
  }            
});
/***************************************************************************/
//date range
$('.date_range').change(function(){
  var d1 = $('#amc_schedule_period_from_text').val();
  var d2 = $('#amc_schedule_period_to_text').val();

  var date1 = new Date(d1);
  var date2 = new Date(d2);

  var date1_ms = date1.getTime();
  var date2_ms = date2.getTime();

  var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days').val(no_days); 
          if(no_days>=30)
          {
            $('#payment_method_id_text').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
           $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
         }
         if(no_days>=90)
         {
          $('#payment_method_id_text').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
          $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
        }
        if(no_days>=180)
        {
          $('#payment_method_id_text').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
          $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
          $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
        } 
        if(no_days>=360)
        {
          $('#payment_method_id_text').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
         $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
         $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
         $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
         $('#payment_method_id_text').append('<option value="7">'+ 'Yearly' +'</option>')
       }
       if(no_days<30)
       {
        alert('No Of days must be atleast 30 days !');
        $('#amc_schedule_period_to_text').empty();
        $('#amc_schedule_period_to_text').val("");
      }
      var sel=  $('#payment_method_id_text option:selected').html();
      $('#frequency_type').val(sel);

    });

/***************************************************************************/
$('#payment_method_id_text').change(function(){
 var sel=  $('#payment_method_id_text option:selected').html();
 $('#frequency_type').val(sel);   
});
     /*$( document ).ready(function() {
       var sel=  $('#payment_method_id_text option:selected').html();
     $('#frequency_type').val(sel);    
   });*/
   /***************************************************************************/
//PROCESS-Technician
$(document).on('click','.process_amenity',function(){ 
  myArr = [];
  
  var amentity_types_id = $(".amentity_types_tech_id").val();
  
      var inputs = document.getElementsByClassName( 'amentity_types_tech_id' ),
    names  = [].map.call(inputs, function( input ) {
      myArr.push(input.value);
        return input.value;
    }).join( ',' );
 
  
  
  var amc_schedule_period_from_text = $("#amc_schedule_period_from_text").val();
  var amc_schedule_period_to_text = $("#amc_schedule_period_to_text").val();
  var frequency_type = $("#frequency_type").val();
  //alert(frequency_type);
  var no_days = $("#no_days").val();
  var no = $('#process_form tr').length+1;
  var rowcCount = $('#process_form tr').length;  
  if(rowcCount < '1'){
    if(myArr.length > 0 && amc_schedule_period_from_text !="" &&  amc_schedule_period_to_text !="" &&  frequency_type !="Select Frequency" ){
      $.ajax({
        method: "POST",
        url: "{{route('processAmcAmenityTechnician')}}",
        data: {no:no,amentity_types_id:myArr,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){   
                             
          if(data != 0){
            $('#process_form').html(data);                           
          }               
        }           
      });
    }else{
      alert("Please Select Amenity, From Date , To date And Frequency");
    }
  }else{
    if(confirm('Do You Want To Reset The Task ..?')){
      if(myArr.length > 0 && amc_schedule_period_from_text !="" &&  amc_schedule_period_to_text !="" ){
        $.ajax({
          method: "POST",
          url: "{{route('processAmcAmenityTechnician')}}",
          data: {no:no,amentity_types_id:myArr,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){   
                               
            if(data != 0){
              $('#process_form').html(data);                           
            }               
          }           
        });
      }else{
        alert("Please Select Amenity, From Date And To date");
        $('#process_form').html("");
      }
    }
    return false;
        
  }
               
});

/***************************************************************************/
//edit-technician
$(document).on('click','.AmenityEdit',function(){

  var amentity_types_id = $(this).attr('data-id');
  var amc_schedule_period_from = $(this).attr('data-frm');
  var amc_schedule_period_to = $(this).attr('data-to');
  var from = $(this).attr('data-f');
  
  var to = $(this).attr('data-t');
  var no = $(this).attr('data-idNo');
  var tr_num = $(this).attr('data-num');

  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
           url: "{{route('amenityEdit')}}",
            data: {'tr_num' : tr_num,'amentity_types_id' : amentity_types_id,'amc_schedule_period_from' : amc_schedule_period_from, 'amc_schedule_period_to' : amc_schedule_period_to,'from' : from,'to' : to,'no' : no,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
//edit-subcontractor
$(document).on('click','.AmenityEditSub',function(){

  var amentity_types_id = $(this).attr('data-id');
  var amc_schedule_period_from = $(this).attr('data-frm');
  var amc_schedule_period_to = $(this).attr('data-to');
  var from = $(this).attr('data-f');
  var to = $(this).attr('data-t');
  var no = $(this).attr('data-idNo');
  var tr_num = $(this).attr('data-num');

  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
           url: "{{route('amenityEditSub')}}",
            data: {'tr_num' : tr_num,'amentity_types_id' : amentity_types_id,'amc_schedule_period_from' : amc_schedule_period_from, 'amc_schedule_period_to' : amc_schedule_period_to,'from' : from,'to' : to,'no' : no,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
 function pad(s) { return (s < 10) ? '0' + s : s; }
    //update amenity-technician
    $(document).on('click','.update_amenity',function(){ 
     var amentity_types_id = $("#amentity_types_edit_id").val();
     var tr_num = $("#tr_num").val();

     var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
     var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
     var from = $("#from").val();
     var to = $("#to").val();
     var no = $('#No').val();
     if(amentity_types_id !=""){
        if(amc_schedule_period_from_text >= from && amc_schedule_period_from_text <= to && amc_schedule_period_to_text >= from && amc_schedule_period_to_text <= to && amc_schedule_period_to_text >= amc_schedule_period_from_text){
      $.ajax({
        method: "POST",
        url: "{{route('updateScheduleAmenity')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,from:from,to:to, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){ 
         
        let current_datetime = new Date(data['amc_schedule_period_from_text']) ;
           let from_textt = pad(current_datetime.getDate()) + "/" + pad((current_datetime.getMonth() + 1)) + "/" + pad(current_datetime.getFullYear());

           let current_datetimes = new Date(data['amc_schedule_period_to_text']) ;
           let to_textt = pad(current_datetimes.getDate()) + "/" + pad((current_datetimes.getMonth() + 1)) + "/" + pad(current_datetimes.getFullYear());                           
           //and there for the td elements
           $("#amentity_types_id"+tr_num).html(data['amentity_types_name']+'<input type="hidden" name="amentity_types_idd[]" value="'+data['amentity_types_id']+'">');

           $("#amc_schedule_period_from_text"+tr_num).html(from_textt+'<input type="hidden" name="amc_schedule_from_date[]" value="'+data['amc_schedule_period_from_text']+'">');

           $("#amc_schedule_period_to_text"+tr_num).html(to_textt+'<input type="hidden" name="amc_schedule_to_date[]" value="'+data['amc_schedule_period_to_text']+'">');

           $("#amc_schedule_status"+tr_num).html(' <button type="button" class="btn label label-primary label-mini">OPEN</button>');






           $("#action"+tr_num).html('<button class="btn btn-tbl-delete btn-xs remove_amenity" type="button"><i class="fa fa-trash-o "></i></button> <button type="button" class="btn btn-tbl-edit btn-xs AmenityEdit" data-toggle="modal" data-target="#myModal" data-num = "'+tr_num+'"data-id = "'+data['amentity_types_id']+'" data-frm = "'+data['amc_schedule_period_from_text']+'" data-to = "'+data['amc_schedule_period_to_text']+'" data-idNo="'+data['no']+'" data-f="'+data['from']+'" data-t="'+data['to']+'"><i class="fa fa-pencil"></i></button>');

          //$('tbody#complaint_form tr#'+no).html(data);
          $('#myModal').modal('toggle');                    

        }       
      });
      }
    else{
       alert("Date From and Date To must be Between Start Date and End Date");
    }
    }else{
     // alert("Please Select Category");
    }             
  });
/***************************************************************************/
    //update amenity-subcontractor
    $(document).on('click','.update_amenity_sub',function(){ 
     var amentity_types_id = $("#amentity_types_edit_id").val();
     var tr_num = $("#tr_num").val();

     var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
     var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
     var from = $("#from").val();
     var to = $("#to").val();
     var no = $('#No').val();
     if(amentity_types_id !=""){
if(amc_schedule_period_from_text >= from && amc_schedule_period_from_text <= to && amc_schedule_period_to_text >= from && amc_schedule_period_to_text <= to && amc_schedule_period_to_text >= amc_schedule_period_from_text){
      $.ajax({
        method: "POST",
        url: "{{route('updateScheduleAmenity')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,from:from,to:to, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){     
                             
           //and there for the td elements
           let current_datetime = new Date(data['amc_schedule_period_from_text']) ;
           let from_text = pad(current_datetime.getDate()) + "/" + pad((current_datetime.getMonth() + 1)) + "/" + pad(current_datetime.getFullYear());

           let current_datetimes = new Date(data['amc_schedule_period_to_text']) ;
           let to_text = pad(current_datetimes.getDate()) + "/" + pad((current_datetimes.getMonth() + 1)) + "/" + pad(current_datetimes.getFullYear());

           $("#amentity_types_id"+tr_num).html(data['amentity_types_name']+'<input type="hidden" name="amentity_types_idd_sub[]" value="'+data['amentity_types_id']+'">');

           $("#amc_schedule_period_from_text"+tr_num).html(from_text+'<input type="hidden" name="amc_schedule_period_from_text_sub[]" value="'+data['amc_schedule_period_from_text']+'">');

           $("#amc_schedule_period_to_text"+tr_num).html(to_text+'<input type="hidden" name="amc_schedule_period_to_text_sub[]" value="'+data['amc_schedule_period_to_text']+'">');

           $("#amc_schedule_status"+tr_num).html(' <button type="button" class="btn label label-primary label-mini">OPEN</button>');






           $("#action"+tr_num).html('<button class="btn btn-tbl-delete btn-xs remove_amenity" type="button"><i class="fa fa-trash-o "></i></button> <button type="button" class="btn btn-tbl-edit btn-xs AmenityEditSub" data-toggle="modal" data-target="#myModal" data-num = "'+tr_num+'"data-id = "'+data['amentity_types_id']+'" data-frm = "'+data['amc_schedule_period_from_text']+'" data-to = "'+data['amc_schedule_period_to_text']+'" data-idNo="'+data['no']+'" data-f="'+data['from']+'" data-t="'+data['to']+'"><i class="fa fa-pencil"></i></button>');

          //$('tbody#complaint_form tr#'+no).html(data);
          $('#myModal').modal('toggle');                    

        }       
      });
    }
    else{
       alert("Date From and Date To must be Between Start Date and End Date");
    }
    }else{
     // alert("Please Select Category");
    }             
  });
//remove amenity-technician
/***************************************************************************/
$(document).on('click','.remove_amenity',function(){
      var row = $(this).closest('tr').attr('id'); // Or continue to use the invalid ID selector: '#'+id
      var amenity_type_id = $(this).attr('data-id');

      var siblings =  $(this).closest('td').siblings('td.selected').text();

      
      if(amenity_type_id == undefined){
              
        $(this).closest('tbody .tr').remove();
      }else{
        var length  = $('#process_tech tbody tr.tr'+amenity_type_id).length;
        if(length > 0){
          alert("Delete Task Against This Amenity");
          //$(this).closest('tbody .tr').remove();
        }else{
          $(this).closest('tbody .tr').remove();
        }
        
      }
      

        
      });

/***************************************************************************/
//add new schedule-technician
$(document).on('click','.AddTask',function(){
  
  myArr = [];
  
  var amentity_types_id = $(".amentity_types_tech_id").val();
  var inputs = document.getElementsByClassName( 'amentity_types_tech_id' ),
    names  = [].map.call(inputs, function( input ) {
      myArr.push(input.value);
        return input.value;
    }).join( ',' );

  var rowcCount = $('#process_form tr:last').attr('id'); 
  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
           url: "{{route('addTask')}}",
            data: {'rowcCount' : rowcCount,'amentity_types_id' : myArr,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
    //PROCESS add amenity-technician
    $(document).on('click','.process_addamenity',function(){ 

      myArr = [];
  
      var amentity_types_id = $(".amentity_types_tech_id").val();
      var inputs = document.getElementsByClassName( 'amentity_types_tech_id' ),
        names  = [].map.call(inputs, function( input ) {
          myArr.push(input.value);
            return input.value;
        }).join( ',' );

      var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
      var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
      var frequency_type = $("#frequency_type").val();
      var no_days = $("#no_days").val();
      var no = $('#process_form tr').length+1;
      if(myArr.length > 0){
        $.ajax({
          method: "POST",
          url: "{{route('processAmcAmenity')}}",
          data: {no:no,amentity_types_id:myArr,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){                            
            if(data != 0){
              $('#process_form').html(data);                           
            }               
          }           
        });
      }else{
        alert("Please Select Amenity");
      }             
    });
    /***************************************************************************/
//PROCESS-Subcontractor
$(document).on('click','.process_amenity_subcontractor',function(){ 
 var amc_contract_num = $('#amc_contract_num').val();
  var amentity_types_id = $("#amentities_types_id").val();
  var amc_schedule_period_from_text = $("#amc_schedule_period_from").val();
  var amc_schedule_period_to_text = $("#amc_schedule_period_to").val();
  var amc_contract_id = $("#amc_contract_id").val();
  var frequency_type = $("#payment_method_id").val();
  var no_days = $("#no_days_sub").val();
  var no = $('#process_form_sub tr').length+1;
  var rowcCount = $('#process_form_sub tr').length;
  if(rowcCount < 1 ){
    if(amentity_types_id !=""){
      $.ajax({
        method: "POST",
        url: "{{route('processAmcAmenity')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days,amc_contract_id:amc_contract_id, amc_contract_num:amc_contract_num,"_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
          if(data != 0){
            $('#process_form_sub').html(data);                           
          }               
        }           
      });
    }else{
      alert("Please Select Amenity");
    }
  }else{
  if(confirm('Do You Want To Reset The Task..?')){
    if(amentity_types_id !=""){
      $.ajax({
        method: "POST",
        url: "{{route('processAmcAmenity')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days,amc_contract_id:amc_contract_id, amc_contract_num:amc_contract_num,"_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
          if(data != 0){
            $('#process_form_sub').html(data);                           
          }               
        }           
      });
    }else{
      alert("Please Select Amenity");
    }
  }
  return false;
      
  }             
});
/***************************************************************************/
//add new schedule-subcontractor
$(document).on('click','.AddTaskSub',function(){
  var amc_contract_num = $('#amc_contract_num').val();
  var rowcCount = $('#process_form_sub tr:last').attr('id'); 
  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
           url: "{{route('addTaskSubContractor')}}",
            data: {'amc_contract_num' : amc_contract_num,'rowcCount' : rowcCount,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
    //PROCESS add amenity-subcontractor
    $(document).on('click','.process_addamenity_sub',function(){ 
      var amentity_types_id = $("#amentity_types_idd").val();
      var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
      var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
      var frequency_type = $("#payment_method_id").val();
      var no_days = $("#no_days_sub").val();
      var no = $('#process_form_sub tr').length+1;
      if(amentity_types_id !=""){
        $.ajax({
          method: "POST",
          url: "{{route('processAmcAmenity')}}",
          data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){                            
            if(data != 0){
              $('#process_form_sub').html(data);                           
            }               
          }           
        });
      }else{
        alert("Please Select Amenity");
      }             
    });
    /***************************************************************************/
    //fetching amenity for edit 
    $( document ).ready(function() {
      
        $.validator.addMethod("greaterThan", 
        function(value, element, params) {

          if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) > new Date($(params).val());
          }

          return isNaN(value) && isNaN($(params).val()) 
          || (Number(value) > Number($(params).val())); 
        },'Must be greater than End Date.');
        $("#schedule-form").validate({
            rules: {
              amc_schedule_period_to_text: { greaterThan: "#amc_schedule_period_from_text" ,

              }


            }
        });
/************************************************************/ 
    /*var dtToday = new Date();
    
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
        month = '0' + month.toString();
    if(day < 10)
        day = '0' + day.toString();
    
    var maxDate = year + '-' + month + '-' + day;
    
    $('#amc_schedule_period_from_text').attr('min', maxDate);
    $('#amc_schedule_period_to_text').attr('min', maxDate); */
  
/***************************************************************************/
  var d1 = $('#amc_schedule_period_from_text').val();
  var d2 = $('#amc_schedule_period_to_text').val();
  var pname = $('#method_text').val();
  var pid = $('#method').val();
  var date1 = new Date(d1);
  var date2 = new Date(d2);

  var date1_ms = date1.getTime();
  var date2_ms = date2.getTime();

  var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days').val(no_days); 
          if(no_days>=30)
          {
           $('#payment_method_id_text').empty().append('<option value="6">'+ 'Monthly' +'</option>')
         }
         if(no_days>=90)
         {
          // $('#payment_method_id_text').empty().append('<option value='+ pid +' selected>'+ pname +'</option>')
          $('#payment_method_id_text').empty().append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
        }
        if(no_days>=180)
        {
          /*$('#payment_method_id_text').empty().append('<option value='+ pid +' selected>'+ pname +'</option>')*/
          $('#payment_method_id_text').empty().append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
          $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
        } 
        if(no_days>=360)
        {
         /*$('#payment_method_id_text').empty().append('<option value='+ pid +' selected>'+ pname +'</option>')*/
         $('#payment_method_id_text').empty().append('<option value="6">'+ 'Monthly' +'</option>')
         $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
         $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
         $('#payment_method_id_text').append('<option value="7">'+ 'Yearly' +'</option>')
       }
       if(no_days<30)
       {
        alert('No Of days must be atleast 30 days !');
        $('#amc_schedule_period_to_text').empty();
        $('#amc_schedule_period_to_text').val("");
        
      }
      if(pid == 6){
        $("#payment_method_id_text option[value="+6+"]").prop('selected', true);
      
      }else if(pid == 1){
        $("#payment_method_id_text option[value="+1+"]").prop('selected', true);
      }else if(pid == 2){
        $("#payment_method_id_text option[value="+2+"]").prop('selected', true);
      }else if(pid == 7){
        $("#payment_method_id_text option[value="+7+"]").prop('selected', true);
      }
    /***************************************************************************/
    /* if ($("#amc_schedule_id").val()=='') {
        $("#contract_type").removeAttr("disabled");
    }
    else {
        $("#contract_type").attr('disabled', 'disabled');
    }*/
    /***************************************************************************/

      $.ajax({
       type: "GET",
       url: "{!!URL::route('getBuildingAmenity')!!}",
       data:'building_id='+ $('#building_text_id').val(),
       success: function(data){
       var selected = "";
          var result = $.parseJSON(data);
          //$("#amentity_types_id").html(data);
          $('#amentity_types_id').empty();
            $('#amentity_types_id').append('<option value="">'+ 'Select Amenity' +'</option>')
              $.each(result[0], function(key, value) {
                  $('#amentity_types_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
            });
      }
    });
     /*$.ajax({
       type: "GET",
       url: "{!!URL::route('getBuildingUnits')!!}",
       data:'building_id='+ $('#building_text_id').val(),
       success: function(data){
        $('#unit_text_id').html(data);
      }
    });*/   
      //payment method type
      var sel=  $('#payment_method_id_text option:selected').html();
      $('#frequency_type').val(sel);

      //no of days
      var d1 = $('#amc_schedule_period_from_text').val();
      var d2 = $('#amc_schedule_period_to_text').val();

      var date1 = new Date(d1);
      var date2 = new Date(d2);

      var date1_ms = date1.getTime();
      var date2_ms = date2.getTime();

      var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days').val(no_days);

        }); 
    /***************************************************************************/
    //delete schedule task
//delete schedule task
jQuery('.delete_type').click(function (event) {
  var action = $(this).attr("href");
  event.preventDefault();
  if (confirm('Do you want to Delete this Amenity Task?')) {
    jQuery("#delete-form").attr('action', action);
    jQuery("#delete-form").submit();
  } else {
    return false;
  }
});
/***************************************************************************/
$(document).on('click','.addamenity_sub',function(){ 

    var amentity_types_id = $("#amentity_types_idd").val();
    var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
    var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
    var from = $("#amc_schedule_period_from").val();
    var to = $("#amc_schedule_period_to").val();
    var no = $("#No").val();

    if(amentity_types_id !="" && amc_schedule_period_from_text !="" && amc_schedule_period_to_text !="" ){
      if(amc_schedule_period_from_text >= from && amc_schedule_period_from_text <= to && amc_schedule_period_to_text >= from && amc_schedule_period_to_text <= to && amc_schedule_period_to_text >= amc_schedule_period_from_text){
      $.ajax({
        method: "POST",
        url: "{{route('addAmenitySubcontractorForm')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,from:from,to:to, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
          if(data != 0){
            $('#process_form_sub').append(data); 
            $('#myModal').modal('hide');                          
          }               
        }           
      });
      }
    else{
       alert("Date From and Date To must be Between Start Date and End Date");
    }
    }else{
      alert("Please Select Amenity ,Date From And Date To ");
    }             
  });
/***************************************************************************/
$(document).on('click','.process_addamenity_technician',function(){ 

    var amentity_types_id = $("#amentity_types_idd").val();
    var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
    var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
    var from = $("#amc_schedule_period_from_text").val();
    var to = $("#amc_schedule_period_to_text").val();
    var no = $("#No").val();
    if(amentity_types_id !="" && amc_schedule_period_from_text !="" && amc_schedule_period_to_text !="" ){
        if(amc_schedule_period_from_text >= from && amc_schedule_period_from_text <= to && amc_schedule_period_to_text >= from && amc_schedule_period_to_text <= to && amc_schedule_period_to_text >= amc_schedule_period_from_text){
      $.ajax({
        method: "POST",
        url: "{{route('addAmenityTechnicianForm')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,from:from,to:to,"_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
          if(data != 0){
            $('#process_form').append(data); 
            $('#myModal').modal('hide');                          
          }               
        }           
      });
      }else
      {
        alert("Date From and Date To must be Between Start Date and End Date");
      }
    }else{
      alert("Please Select Amenity ,Date From And Date To ");
    }             
  });
/***************************************************************************/
$(document).on('click','.updateEditedAmenity',function(){

  var amc_schedule_id = $(this).attr('data-id');
  var amc_task_id = $(this).attr('datas-id');
  var amenities_type_id = $(this).attr('datas-idd');

  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
            url: "{{route('editTaskAmenity')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'amc_schedule_id' : amc_schedule_id,'amc_task_id' : amc_task_id,'amenities_type_id' : amenities_type_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
$("#myModal").on("hidden.bs.modal", function(){
    $("#myModal").html("");
    $(this).removeData('bs.modal');
});
$(document).on('change','.unitExist',function(){ 
  var building_id = $("#building_text_id").val();
  if(building_id == ""){building_id = $("#building_id").val();}
  var unit_id = $("#unit_text_id").val();
  if(unit_id == ""){unit_id = $("#unit_id").val();}
  $.ajax({
       type: "GET",
       url: "{!!URL::route('getBuildingAmenity')!!}",
       data:{building_id:building_id,unit_id:unit_id},
       success: function(data){
        var selected = "";
          var result = $.parseJSON(data);
          //$("#amentity_types_id").html(data);
          $('#amentity_types_id').empty();
            $('#amentity_types_id').append('<option value="">'+ 'Select Amenity' +'</option>')
              $.each(result[0], function(key, value) {
                  $('#amentity_types_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
            });
         if(result[1] != ""){
            location.reload();
          }else{
            var data = "<tr><td colspan='4'><p>No Record</p></td></tr>";
            $('#amenity_form').html(data);
            $('#process_form').html(data);
            
          }
      }
    });
});
</script>

@endsection





@endsection
