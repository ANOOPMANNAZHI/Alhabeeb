        <!-- The Modal -->
<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Add PDC</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{ !isset($pdcInfo)? route('salesActivities.store'): route('salesActivities.update',$pdcInfo->id)}}" method="POST" id="sales_activity_form" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($pdcInfo)){{method_field('PUT')}}@endif
        <input  type="hidden" class="form-control" name="tenant_contract_id" value="45">
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="pdc_details_check_no">Check No<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-bars icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="pdc_details_check_no"  placeholder="Enter Check No" name="pdc_details_check_no" required  value="{{ isset($pdcInfo)?  old('pdc_details_check_no',$pdcInfo->pdc_details_check_no): old('sales_activities_name')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
                    </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="pdc_details_check_date">Check Date<small class="textRed">*</small></label>
                        <div class="p-relative">
                    	<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="pdc_details_check_date"  placeholder="Enter Check Date" name="pdc_details_check_no" required  value="{{ isset($pdcInfo)?  old('pdc_details_check_date',$pdcInfo->pdc_details_check_date): old('sales_activities_name')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
                    </div>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="pdc_details_amt">Amount<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-fa-window-restore icn-add" aria-hidden="true"></i>
                          <input  type="number" class="form-control" id="pdc_details_amt"  placeholder="Enter Amount" name="pdc_details_amt" required  value="{{ isset($pdcInfo)?  old('pdc_details_amt',$pdcInfo->pdc_details_amt): old('sales_activities_name')}}" data-rule-maxlength="25" >
                    </div>
                    </div> 
                </div>
              
                <div class="col-sm-6">
                    <div class="form-group">
                        <div class="wid-100">
                           <label for="bank_id">Bank Debit</label>
                           <div class="p-relative">	
                             <i class="fa fa-calendar-times-o icn-add" aria-hidden="true"></i>
						    <select class="form-control" name="bank_id" required>
	                            <option value="">Select Bank Debit</option>
	                            @forelse ($bankList as $bank)
									 <option value="{{$bank->id}}">{{$bank->bank_name}}</option>
								@empty
									 <option value="">No Bank</option>
	 							@endforelse	
							</select>
						 </div>
                        </div>
                    </div>
                </div>
               	<div class="col-sm-6">
                    <div class="form-group">
                        <div class="wid-100">
                            <label for="pdc_details_period">Period in month<small class="textRed">*</small></label>
                             <div class="p-relative">
                    		<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="number" required name="pdc_details_period" class="form-control" id="pdc_details_period" placeholder="Enter Period" value="{{ isset($pdcInfo)?  old('pdc_details_period',$pdcInfo->pdc_details_period): old('pdc_details_period')}}" >
                        </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                  	<div class="wid-100">
                     <label for="pdc_details_stage">Stage<small class="textRed">*</small></label>
                         <div class="p-relative">
                    		<i class="fa fa-sticky-note-o icn-add" aria-hidden="true"></i>
                        <input  type="text"  required name="pdc_details_period" class="form-control" id="pdc_details_period" placeholder="Enter Period" value="{{ isset($pdcInfo)?  old('pdc_details_period',$pdcInfo->pdc_details_period): old('pdc_details_period')}}" >
                    	</div>
                    </div>
                  </div>
                  </div>
                <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_check_date">Rec Date<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="date"  required name="pdc_details_check_date" class="form-control" id="pdc_details_check_date" placeholder="Enter Recieve date" value="{{ isset($pdcInfo)?  old('pdc_details_check_date',$pdcInfo->pdc_details_check_date): old('pdc_details_check_date')}}" >
                    </div>
                    </div>
                  </div>
				  <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_recieve_from">Rec From Date<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="date"  required name="pdc_details_recieve_from" class="form-control" id="pdc_details_recieve_from" placeholder="Enter Recieve date from" value="{{ isset($pdcInfo)?  old('pdc_details_recieve_from',$pdcInfo->pdc_details_recieve_from): old('pdc_details_recieve_from')}}" >
                    </div>
                    </div>
                  </div>
				 <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_recieve_to">Rec To Date<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="date"  required name="pdc_details_recieve_to" class="form-control" id="pdc_details_recieve_to" placeholder="Enter Recieve date from" value="{{ isset($pdcInfo)?  old('pdc_details_recieve_to',$pdcInfo->pdc_details_recieve_to): old('pdc_details_recieve_to')}}" >
                    </div>
                    </div>
                  </div>
				 <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_remark">Remark<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="text"  required name="pdc_details_remark" class="form-control" id="pdc_details_remark" placeholder="Enter Recieve date from" value="{{ isset($pdcInfo)?  old('pdc_details_remark',$pdcInfo->pdc_details_remark): old('pdc_details_remark')}}" >
                    </div>
                    </div>
                  </div>
				  <div class="col-sm-6">
	                  <div class="form-group">
	                     <label for="deposite">Deposite<small class="textRed">*</small></label>
	                       <label><input id="deposite" type="checkbox" value="">Deposite</label>
	                    </div>
                  </div>
                  <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_deposit_date">Deposite Date<small class="textRed">*</small></label>
                       <div class="p-relative">
	                    	<i class="fa fa-calendar-o" aria-hidden="true"></i>
	                        <input  type="text"  required name="pdc_details_deposit_date" class="form-control" id="pdc_details_deposit_date" placeholder="Enter Deposite date" value="{{ isset($pdcInfo)?  old('pdc_details_deposit_date',$pdcInfo->pdc_details_deposit_date): old('pdc_details_deposit_date')}}" >
                    	</div>
                    </div>
                  </div>
				  <div class="col-sm-6">
	                  <div class="form-group">
	                     <label for="pdc_details_remark">Clear<small class="textRed">*</small></label>
	                       <label><input id="clear" type="checkbox" value="">Clear</label>
	                    </div>
                  </div>
                  <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_clear_date">Clear Date<small class="textRed">*</small></label>
                       <div class="p-relative">
	                    	<i class="fa fa-calendar-o" aria-hidden="true"></i>
	                        <input  type="text"  required name="pdc_details_clear_date" class="form-control" id="pdc_details_clear_date" placeholder="Enter Clear date" value="{{ isset($pdcInfo)?  old('pdc_details_clear_date',$pdcInfo->pdc_details_clear_date): old('pdc_details_clear_date')}}" >
                    	</div>
                    </div>
                  </div>
                 <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_voucher_no">Voucher No<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="text"  required name="pdc_details_voucher_no" class="form-control" id="pdc_details_voucher_no" placeholder="Enter Voucher No" value="{{ isset($pdcInfo)?  old('pdc_details_voucher_no',$pdcInfo->pdc_details_voucher_no): old('pdc_details_voucher_no')}}" >
                    </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
	                  <div class="form-group">
	                     <label for="pdc_details_remark">Cancel<small class="textRed">*</small></label>
	                       <label><input id="cancel" type="checkbox" value="{{ isset($pdcInfo)?  old('pdc_details_clear_date',$pdcInfo->pdc_details_clear_date): old('pdc_details_clear_date')}}">Cancel</label>
	                    </div>
                  </div>
                  <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_clear_date">Cancel Date<small class="textRed">*</small></label>
                       <div class="p-relative">
	                    	<i class="fa fa-calendar-o" aria-hidden="true"></i>
	                        <input  type="text"  required name="pdc_details_cancel_date" class="form-control" id="pdc_details_cancel_date" placeholder="Enter Clear date" value="{{ isset($pdcInfo)?  old('pdc_details_clear_date',$pdcInfo->pdc_details_clear_date): old('pdc_details_clear_date')}}" >
                    	</div>
                    </div>
                  </div>
                 <div class="col-sm-6">
                  <div class="form-group">
                     <label for="pdc_details_cancel_remark">Cancel Remark<small class="textRed">*</small></label>
                         <div class="p-relative">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <input  type="text"  required name="pdc_details_cancel_remark" class="form-control" id="pdc_details_cancel_remark" placeholder="Enter Cancel Remark" value="{{ isset($pdcInfo)?  old('pdc_details_cancel_remark',$pdcInfo->pdc_details_cancel_remark): old('pdc_details_cancel_remark')}}" >
                    </div>
                    </div>
                  </div>
                <div class="w-100"></div>
                <div class="col">
                  <div class="w-100"></div>
                      <button type="submit" class="btn btn-primary">SAVE</button>
                </div>
                   
              </div>
            
        </div>
        <div class="clearfix"></div>
        </form>
            
        </div>
    </div>
</div> 

    </div>

    </div>
</div>

<script>
  $(document).ready(function() {
    $("#sales_activity_form").validate({
      rules: {
        'sales_activities_due_date': { required: true, maxDate: new Date() }               
      },
      messages: { // optional message
          'sales_activities_due_date': {
              maxDate: 'Due date must be on or after today'
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

    $('#sales_activities_time').datetimepicker({
   // dateFormat: 'dd-mm-yy',
    format:'DD/MM/YYYY HH:mm:ss',
    minDate: getFormattedDate(new Date())
    });
    function getFormattedDate(date) {
      var day = date.getDate();
      var month = date.getMonth() + 1;
      var year = date.getFullYear().toString().slice(2);
      return day + '-' + month + '-' + year;
    }
  });
</script>
