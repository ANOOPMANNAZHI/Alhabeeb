         
<div class="modal-dialog modal-lg assign">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Add Amenity </h4>
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
              <select class="form-control" id="amentity_types_idd" name="amentity_types_id" required>
                <option value="">Select</option>
                @foreach($amenities as $amenity)
                <option value="{{$amenity->id}}">{{$amenity->amentity_types_name}}</option>
                @endforeach
              </select> 
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="amc_schedule_period_from_text">Start Date<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
               <input  type="date" class="form-control date_range" id="amc_schedule_period_from_textt" name="amc_schedule_period_from_text" value=""  placeholder="Enter Start Date">
             </div>
           </div>
         </div>
         <div class="col-sm-4">
          <div class="form-group">
            <label for="amc_schedule_period_to_text">End Date<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
             <input  type="date" class="form-control date_range" id="amc_schedule_period_to_textt" name="amc_schedule_period_to_text" value=""  placeholder="Enter End Date">
           </div>
         </div>
       </div>
       <div class="col-sm-4">
        <div class="dataSearchLabel w-100"></div>
        <input type="hidden" name="no" value="{{$rowcCount}}" id="No">
        <button type="button" class="btn btn-primary dataSearchLabel process_addamenity_technician">Add</button>
      </div>
    </div>


  </div>
</div>
</div>
</div>