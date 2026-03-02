        <!-- The Modal -->
<div class="modal-dialog assign modal-lg">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Sales Activity</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form autocomplete = "off" action="{{ !isset($salesActivity)? route('salesActivities.store'): route('salesActivities.update',$salesActivity->id)}}" method="POST" id="sales_activity_form" class="form-horizontal sales_note_modal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($salesActivity)){{method_field('PUT')}}@endif
        <input  type="hidden" class="form-control" name="sales_id" value="{{ !isset($salesActivity)? $sales_id: $salesActivity->sales_id}}">
        <input  type="hidden" class="form-control" name="enquiry_id" value="{{ !isset($salesActivity)? $enquiry_id: $salesActivity->sales->sales_enquiry_id}}">
        <input  type="hidden" class="form-control" name="stage" value="{{ !isset($salesActivity)? $stage: $salesActivity->sales->work_flow_processes_code}}">
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="sales_activities_name">Subject<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-bars icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="sales_activities_name"  placeholder="Enter Subject" name="sales_activities_name" required  value="{{ isset($salesActivity)?  old('sales_activities_name',$salesActivity->sales_activities_name): old('sales_activities_name')}}" data-rule-maxlength="200" data-msg-maxlength="Only allowes 200 Characters">
                    </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label>Activity Type<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-fa-window-restore icn-add" aria-hidden="true"></i>
                        <select class="form-control" name="sales_activity_type" required>
                            <option value="">Select Activity Type</option>                            
                            <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Email')? 'selected' : '') : ''}} value="1" >Email</option>
                            <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Phone')? 'selected' : '') : ''}} value="2" >Phone</option>
                            <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Task')? 'selected' : '') : ''}} value="3" >Task</option>
                            <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Appointment')? 'selected' : '') : ''}} value="4" >Appointment</option>
                            
                        </select>
                    </div>
                    </div> 
                </div>
               <div class="w-100"></div>
               <div class="col-sm-6">
                    <div class="form-group">
                        <div class="wid-100">
                            <label for="sales_activities_due_date">Due Date<small class="textRed">*</small></label>
                             <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="date" required name="sales_activities_due_date" class="form-control" id="sales_activities_due_date" placeholder="Enter Due Date" value="{{old('sales_move_in_date',isset($salesActivity)? $salesActivity->sales_activities_due_date->format('Y-m-d') : '')}}">
                        </div>
                        </div>
                    </div>
                </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                        <div class="wid-100">
                            <label for="sales_activities_time">Time</label>
                             <div class="p-relative">
                    <i class="fa fa-calendar-times-o icn-add" aria-hidden="true"></i>
                            <input type="time"  name="sales_activities_time" class="form-control {{isset($salesActivity)?'':'sales_activities_time'}}" id="sales_activities_time" placeholder="Enter Time" value="{{old('sales_move_in_date',isset($salesActivity)? $salesActivity->sales_activities_time: 'now')}}" min="00:00" max="23:59">
                        </div>
                        </div>
                    </div>
                </div>
                   
                <div class="w-100"></div>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="sales_activities_note">Note<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-sticky-note-o icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="sales_activities_note"  placeholder="Enter Note" name="sales_activities_note" required value="{{ isset($salesActivity)?  old('sales_activities_note',$salesActivity->sales_activities_note): old('sales_activities_note')}}" data-rule-maxlength="200" data-msg-maxlength="Only allowes 200 Characters">
                    </div>
                    </div>
                  </div>
                
                <!-- <div class="w-100"></div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Activity Status<small class="textRed">*</small></label>
                        <select class="form-control" name="sales_activity_type" required>
                            <option value="">Select Activity Type</option>                            
                            <option {{ isset($workLink)? ((old('vendor_id',$workLink->vendor_id) == $vendor->id)? 'selected' : '') : ''}} value="1" >Not Attend</option>
                            <option {{ isset($workLink)? ((old('vendor_id',$workLink->vendor_id) == $vendor->id)? 'selected' : '') : ''}} value="2" >Attend</option>
                            <option {{ isset($workLink)? ((old('vendor_id',$workLink->vendor_id) == $vendor->id)? 'selected' : '') : ''}} value="3" >Close</option>
                           
                            
                        </select>
                    </div> 
                </div> -->
                 <div class="w-100"></div>
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormRemark">Description</label>
                         <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                        <textarea name="sales_activities_summary_note" id="simpleFormRemark" class="form-control" placeholder="Enter Summary Note" data-rule-maxlength="200" data-msg-maxlength="Only allowes 200 Characters">{{ isset($salesActivity)?  old('sales_activities_summary_note',$salesActivity->sales_activities_summary_note): old('sales_activities_summary_note')}}</textarea>
                    </div>
                        
                    </div>
                  </div>
                
                <div class="w-100"></div>
                <div class="col">
                  <div class="w-100"></div>
                      <button type="submit" class="btn btn-primary close_note">SAVE</button>
                </div>
                   
              </div>
            
        </div>
        <div class="clearfix"></div>
        </form>
            
        </div>
    </div>
</div> 

    </div>
    
    <!-- Modal footer -->
   <!-- <div class="modal-footer">
      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div> -->

    </div>
</div>

<script>
	@if(isset($salesActivity))
		var dt = new Date("{{$salesActivity->sales_activities_due_date->format('Y-m-d')}}");
	@else
		var dt = new Date();
	@endif	
  $(document).ready(function() {

    $(".sales_note_modal").validate({
    submitHandler: function(form) {
          $('.close_note').prop('disabled', true);
          form.submit();
     }
   });

    
    $("#sales_activities_time").on("focusout",function(e){
        var currentTime = new Date();
        var userTime = $("#sales_activities_time").val().split(":"); 
        var Time = $("#sales_activities_time").val(); 
        var userDates = $("#sales_activities_due_date").val();
        userDate = new Date(userDates);//alert(currentTime);
        var val = new Date(Date.parse(userDates+' '+Time));
        if(val.getTime() <= currentTime.getTime())
        {   
            if(currentTime.getHours() > parseInt(userTime[0])){
              alert("The Time Must Be Greater than Now");
              $("#sales_activities_time").val('');
              return true;
              $(this).focus();                
          }else if(currentTime.getHours() <= parseInt(userTime[0])){
              if(currentTime.getMinutes() > parseInt(userTime[1])){
                alert("The Time Must Be Greater than Now");
                $("#sales_activities_time").val('');
                return true;
                $(this).focus();
              }
          }
        }
        
       
    });
    
    $(function(){     
		  var d = new Date(),        
			  h = d.getHours(),
			  m = d.getMinutes();
		  if(h < 10) h = '0' + h; 
		  if(m < 10) m = '0' + m; 
		  $('.sales_activities_time').each(function(){ 
			$(this).attr({'value': h + ':' + m});
		  });
		});
	
	
    $("#sales_activity_form").validate({
		  rules: {
			'sales_activities_due_date': { required: true, maxDate: dt }               
		  },
		  messages: { // optional message
			  'sales_activities_due_date': {
				  maxDate: 'Due Date Must Be On Or After Today Or In Edit Case On date And After'
			  }
		  }
    });

    jQuery.validator.addMethod('maxDate', function (v, el, maxDate) {
    if (this.optional(el)) {
        return true;
    }
    var selectedDate = new Date($(el).val());
    maxDate = new Date(maxDate.setHours(0));
    maxDate = new Date(maxDate.setMinutes(0));
    maxDate = new Date(maxDate.setSeconds(0));
    maxDate = new Date(maxDate.setMilliseconds(0));

    return maxDate <= selectedDate;
    }, 'Date is greater than {0}.');
/*
    $('#sales_activities_time').datetimepicker({
   // dateFormat: 'dd-mm-yy',
    format:'DD/MM/YYYY HH:mm:ss',
    minDate: getFormattedDate(new Date())
    });*/
    function getFormattedDate(date) {
      var day = date.getDate();
      var month = date.getMonth() + 1;
      var year = date.getFullYear().toString().slice(2);
      return day + '-' + month + '-' + year;
    }


    
  });
</script>
