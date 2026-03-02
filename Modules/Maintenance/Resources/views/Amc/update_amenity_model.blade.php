         
<div class="modal-dialog modal-lg assign">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">{{ (isset($amcTask))? 'Edit' : 'Add'}} Task </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <div class="modal-body">
      <div class="dataSearchBox panel-heading-lightblue">
        <form method="post" autocomplete="off" id="update-amenity-form" action="{{isset($amcTask)? route( 'amcTask.update',$amcTask->id) : route( 'amcTask.store')}}" data-toggle="validator" onsubmit="return dateCheck()">
         {{csrf_field()}} @if(isset($amcTask)){{method_field('PUT')}}@endif
         <div class="row bb-1 mb-3">
          <div class="col-sm-4">
            <div class="form-group">             
                
                  <label for="amc_schedule_period_from_text">Amenity<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <input  type="text" class="form-control" id="amentity_types_idd" name="amentity_types_name" required value="{{ old('amentity_types_idd', isset($amcTask)? $amcTask->amenityType->amentity_types_name : ''  )}}" readonly>

                   <input  type="hidden" class="form-control" id="amentity_types" name="amentity_types_id" required value="{{ old('amentity_types_idd', isset($amcTask)? $amcTask->amenities_type_id : ''  )}}" readonly>

                     <input type="hidden" name="from_date" id="from_date" value="{{$amc_schedule_period_from}}">
                <input type="hidden" name="todate" id="todate" value="{{$amc_schedule_period_to}}">
                   
                 </div>           

             <input type="hidden" name="amc_schedule_id" id="amc_schedule_id" value="{{$amc_schedule_id}}">

             <input type="hidden" name="amc_schedule_period_from" id="amc_schedule_period_from" value="{{ old('amc_schedule_period_from', isset($amcTask)? $amcTask->amcSchedule->amc_schedule_period_from : ''  )}}">

             <input type="hidden" name="amc_schedule_period_to" id="amc_schedule_period_to" value="{{ old('amc_schedule_period_to', isset($amcTask)? $amcTask->amcSchedule->amc_schedule_period_to : ''  )}}">
           </div>
         </div>
         <div class="col-sm-4">
          <div class="form-group">
            <label for="amc_schedule_period_from_text">Start Date<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
             <input  type="date" class="form-control date_range" id="amc_schedule_period_from_textt" name="amc_schedule_period_from_text" value="{{ old('amc_schedule_from_date', isset($amcTask)? $amcTask->amc_schedule_from_date->format('Y-m-d') : ''  )}}"  placeholder="Enter Start Date" required onkeydown="return false">
           </div>
         </div>
       </div>
       <div class="col-sm-4">
        <div class="form-group">
          <label for="amc_schedule_period_to_text">End Date<small class="textRed">*</small></label>
          <div class="p-relative">
           <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
           <input  type="date" class="form-control date_range" id="amc_schedule_period_to_textt" name="amc_schedule_period_to_text" value="{{ old('amc_schedule_to_date', isset($amcTask)? $amcTask->amc_schedule_to_date->format('Y-m-d') : ''  )}}"  placeholder="Enter End Date" required onkeydown="return false">
         </div>
       </div>
     </div>
     <input type="hidden" name="url" id="url" value="{{ isset($amcTask)? old('url',$nowUrl):$nowUrl}}">
     <div class="col-sm-4">
      <div class="dataSearchLabel w-100"></div>
      <input type="hidden" name="no" value="" id="No">
      <button type="submit" class="btn btn-primary">{{ (isset($amcTask))? 'Update' : 'Add'}}</button>
    </div>
  </div>
</form>

</div>
</div>
</div>
</div>
<script type="text/javascript">

/*$( document ).ready(function() {
 $.validator.addMethod("greaterThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {

            return new Date(value)  > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value)  > Number($(params).val())); 
    },'Must be Between Scheduled Start And End Date.');

    $.validator.addMethod("lessThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) <  new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value) <  Number($(params).val())); 
    },'Must be Between Scheduled Start And End Date.');
    $("#update-amenity-form").validate({
        rules: {
            
            amc_schedule_period_from_text: { greaterThan: "#from_date" ,lessThan: "#todate"}, 
            amc_schedule_period_to_text: { greaterThan: "#from_date" ,lessThan: "#todate"},

        },
        
    });
    });*/
function dateCheck() {
        var fDate,lDate,cDate,dDate;
        fDate = $("#amc_schedule_period_from").val(); // firstdate startdate
        cDate = $("#amc_schedule_period_from_textt").val(); // date from form
        dDate = $("#amc_schedule_period_to_textt").val(); // date from form
        lDate = $("#amc_schedule_period_to").val(); // lastdate enddate
        
        if(cDate <= lDate && cDate >= fDate && dDate <= lDate && dDate >= fDate && dDate >= cDate){
            return true;
        }

        alert("Date From and Date To must be Between Start Date and End Date");
        return false;
    }
 </script>