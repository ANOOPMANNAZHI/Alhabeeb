         
<div class="modal-dialog modal-lg assign">
    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Edit </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <div class="dataSearchBox panel-heading-lightblue">
            <h4>Select </h4>

            <div class="row bb-1 mb-3">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="simpleFormEmail">Amenity</label>
                                <input type="text" name="" class="form-control" rows="2" placeholder="Enter Description" name="checklist_desc" id="checklistDesc" value="{{$amenity->amentity_types_name}}" readonly>
                                <input type="hidden" name="amentity_types_id" id="amentity_types_edit_id" value="{{$amenity->id}}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                          <div class="form-group">
                            <label for="amc_schedule_period_from_text">Start Date<small class="textRed">*</small></label>
                            <div class="p-relative">
                               <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                               <input  type="date" class="form-control date_ranges" id="amc_schedule_period_from_textt" name="amc_schedule_period_from_textt" value="{{$amc_schedule_period_from}}"  placeholder="Enter Start Date">
                           </div>
                       </div>
                   </div>
                   <input type="hidden" name="amc_schedule_period_from" id="amc_schedule_period_from" value="{{$amc_schedule_period_from}}">
                   <input type="hidden" name="amc_schedule_period_to" id="amc_schedule_period_to" value="{{$amc_schedule_period_to}}">
                   <input type="hidden" name="from" id="from" value="{{$from}}">
                   <input type="hidden" name="to" id="to" value="{{$to}}">
                   <div class="col-sm-4">
                      <div class="form-group">
                        <label for="amc_schedule_period_to_text">End Date<small class="textRed">*</small></label>
                        <div class="p-relative">
                           <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                           <input  type="date" class="form-control date_ranges" id="amc_schedule_period_to_textt" name="amc_schedule_period_to_textt" value="{{$amc_schedule_period_to}}"  placeholder="Enter End Date">
                       </div>
                   </div>
               </div>
               <div class="col-sm-4">
                <div class="dataSearchLabel w-100"></div>
                <input type="hidden" name="no" value="{{ isset($no)?  old('no',$no): old('no')}}" id="No">
                <input type="hidden" name="tr_num" value="{{ isset($tr_num)?  old('tr_num',$tr_num): old('tr_num')}}" id="tr_num">
                <button type="button" class="btn btn-primary dataSearchLabel update_amenity_sub">Update</button>
            </div>
        </div>


    </div>
</div>
</div>
</div>