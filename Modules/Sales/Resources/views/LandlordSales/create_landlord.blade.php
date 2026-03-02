<div class="modal-dialog custom-pop assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Landlord Details</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
      <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

            <div class="panel-body">
                <div class="dataSearchBox">
                        <form action="{{route('landlordContract.store')}}" autocomplete="off" method="POST" id="leade_search_pop" class="form-horizontal sbt_form" enctype="multipart/form-data" data-toggle="validator">
                            {{csrf_field()}}
                            <div class="row">
                              
								<div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_codes"> Code<small class="textRed">*</small></label>
                                        <input  type="text" class="form-control" id="vendor_codes"  name="vendor_codes" required  value="{{ old('vendor_code', isset($vendor)?  $vendor->vendor_code : '' )}}">
                                    </div>
                                    <div class="error1" style="display:none">Vendor Code Exists !</div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_name">  Name<small class="textRed">*</small></label>
                                        <input type="text" class="form-control" name="vendor_name" id="vendor_name" placeholder="Enter name" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_contact_address"> Address<small class="textRed">*</small></label>
                                        <textarea class="form-control" name="vendor_contact_address" required=""></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_secondary_address"> Secondary Address</label>
                                       <textarea class="form-control" name="vendor_secondary_address" ></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_pc"> Post-Code<small class="textRed">*</small></label>
                                        <input type="text"  class="form-control" id="vendor_pc" placeholder="Enter Postcode" name="vendor_pc" required>
                                    </div>
                                </div>
                                  @php
                                 foreach($vendorType as $key => $vend_type){
                                        if($vend_type->id == 2)
											$landlord_id = $vend_type->id;
                                 }
                                 @endphp
                                <input type="hidden" value="{{$landlord_id}}" name="vendor_type_id" >
                               
                               
                                <div class="col-sm-6">
                                    <div class="form-group locationpopup">
                                        <label for="work_flow_process_id">Location<small class="textRed">*</small></label>
                                            <div class="w-100">
                                              <select class="form-control" name="location_id" required>
                                                <option value="">Select Location</option>
                                                  @foreach ($location as $key => $loc)
                                                     <option {{ isset($processAssign->location_id)? ((old('location_id',$loc->id) == $processAssign->location_id )? 'selected' : '') :((old('location_id') == $loc->id )? 'selected' : '')}} value="{{$loc->id}}">{{$loc->locations_name.'( '.$loc->locations_code.' )'}}</option>
                                                  @endforeach 
                                               </select>
											</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_contact_person"> Contact Person<small class="textRed">*</small></label>
                                        <input type="text"  class="form-control" id="vendor_contact_person" placeholder="Enter Contact Person" name="vendor_contact_person" required >
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_contact_no">  Contact No<small class="textRed">*</small></label>
                                         <input type="text" class="form-control mob_oman_code" id="vendor_contact_no"  placeholder="Enter Contact No" name="vendor_contact_no" value="{{ isset($data)?  old('vendor_contact_no',$data->vendor_contact_no):'00968'}}" required  onkeypress="return isNumber(event)">
                                 
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_contact_email">  Contact Email<small class="textRed">*</small></label>
                                        <input  type="email" class="form-control" id="vendor_contact_email"  placeholder="Enter Contact Email" name="vendor_contact_email"  value="{{ isset($data->user->email)?  old('vendor_contact_email',$data->user->email): old('vendor_contact_email')}}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_fax_no">  Fax No</label>
                                        <input type="text" class="form-control " name="vendor_fax_no" id="vendor_fax_no" placeholder="Enter Fax No" >
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="vendor_acc_no">Account No<small class="textRed">*</small></label>
                                        <input type="text" class="form-control" name="vendor_acc_no" id="vendor_acc_no" placeholder="Enter Account No" required>
                                    </div>
                                </div>    
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bank_id">Bank<small class="textRed">*</small></label>
                                        <div class="w-100">
                                              <select class="form-control"required name="bank_id"  >
                                                <option value="">Select Bank</option>
                                                  @foreach ($bank as $key => $item): ?>
                                                     <option {{ isset($processAssign->bank_id)? ((old('bank_id',$item->id) == $processAssign->bank_id )? 'selected' : '') :((old('bank_id') == $item->id )? 'selected' : '')}} value="{{$item->id}}">{{$item->bank_name.'( '.$item->bank_code.' )'}}</option>
                                                  @endforeach  
                                                 
                                              </select>

                                          </div>
                                    </div>
                                </div>  
                                <div class="col-sm-6"></div> 
                                <div class="col">
                                  <div class="w-100"></div>
                                    <button type="submit" id="submitBtn" class="savebtn btn btn-primary align-right">SAVE</button>
                              </div>      
                            </div>
                        </form>
                    </div>
            </div>           
          </div>
      </div>
    </div>

    </div>
  
    </div>
</div>

<script>
$(document).ready(function() {
  
    $("#leade_search_pop").validate({
	  rules: {
          sales_email: {
              customEmail: true
              },
          
      }
     
      });
	   $("#vendor_codes").keyup(function(){
      var vendor_codes = $("#vendor_codes").val();
      var vendor_id = $("#vendor_id").val();
      if($("#vendor_codes").val().length >= 3 ){
        $.ajax({
        method: "POST",
        url: "{{route('checkVendorCodeExist')}}",
        data: { vendor_code: vendor_codes, vendor_id: vendor_id,
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
          $(".error1").css("display","block").css("color","red");
          $("#submitBtn").prop('disabled',true);
          }else{
           $(".error1").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }

         }
       });
    }
    });

     $("#vendor_contact_no").addClass("customRegMob");
  
	 $.validator.addClassRules({
			customRegMob: {
				customRegMob: true
			}
	 });
	 jQuery.validator.addMethod("customRegMob", function(value, element) {
			return this.optional(element) || /^\d{13}$/i.test(value);
	 }, "Please Enter 13 Digit Number");
     $('.mob_oman_code').on('keypress, keydown', function(event) {
	
		var $field = $(this);
		var readOnlyLength = 5;
		if ((event.which != 37 && (event.which != 39)) &&
			  ((this.selectionStart < readOnlyLength) ||
				((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
			  return false;
		}
	    
	});
    
	$("#vendor_contact_email").addClass("customEmail");
	$.validator.addClassRules({
		  vendor_contact_email: {
			customEmail: true
		  }
		});
	  jQuery.validator.addMethod("customEmail", function(value, element) {
		return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
	}, "Please enter a valid email address");

})

$(document).on('submit', 'form.sbt_form', function (event) {
	
    event.preventDefault();
    var form = $(this);
    $('#submitBtn').prop('disabled', true);
	
    var data = new FormData(form[0]);
    var url = "{{url('/landlordPopupAction')}}";
    
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
                    if(key=='vendor_name'){
                        $('#vendor_name').val(value);
                    }
                    else{
                      
                         $.ajax({
                          method: 'POST', // Type of response and matches what we said in the route
                          url:"{{url('/vendorNameAjaxCode')}}", 
                          data:{'id' : key,'_token':"{{ csrf_token() }}"}, 
                          success: function(response){ // What to do if we succeed
                              if(response){
								$(".savebtn").attr("disabled", true);
                                $("#vendor_name").val(response); 
                                $("#vendor_code").val(key); 
                                $("#create_build_span").show();
                              }else{
                                $("#vendor_name").val('');
                                  $("#vendor_code").val('');
                                  $("#create_build_span").hide();
                              }
                                    
                          },

                      });
                    }   
                });     
                $('#myModal').modal('hide'); 
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
   
});

</script>
