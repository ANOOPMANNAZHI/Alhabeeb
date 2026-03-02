<div class="modal-dialog custom-pop assign">
  <div class="modal-content">
    
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Tenant Details</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
      
     <div class="row">
      <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

          <div class="panel-body">
            <div class="dataSearchBox">
             <form action="{{route('tenants.store')}}" autocomplete="off" method="POST" id="form_sample_2" class="form-horizontal sbt_form" enctype="multipart/form-data" data-toggle="validator">
              {{csrf_field()}} 

              <!-- <div class="sub-head">Building Type Details</div> -->

              <div class="dataSearchBox ">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_type_id">Tenant Type </label>
                      <div class="p-relative">
                        <i class="fa fa-university icn-add" aria-hidden="true"></i>
                        <select class="form-control"  id="tenant_type_id"  name="tenant_type_id" >
                          <option value=""> Select Tenant Type </option>
                          @foreach($tenantTypes as $tenantType)
                          <option  {{(old('tenant_type_id', isset($tenant)?  $tenant->tenant_type_id : 1) == $tenantType->id) ? 'selected' : '' }} value="{{$tenantType->id}}">{{$tenantType->tenant_types_name}}</option>
                          @endforeach                  
                        </select> 
                      </div>               
                    </div>
                  </div>
                  
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="simpleFormCode">Name<small class="textRed">*</small></label>
                      <div class="p-relative">
                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Tenant Name" name="tenant_name" required patten="[ A-Za-z_@./#&+-]+" data-rule-maxlength="60" data-msg-maxlength="Maximum 60 Characters Allowed">
                      </div>
                    </div>
                  </div>
                  <div class="w-100"></div>
                  
                </div>
                <div class="row" id="row_ind" style="display: none">
                  
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="resident_id">Residence ID No<small class="textRed">*</small></label>
                      <div class="p-relative">
                        <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="resident_id"  placeholder="Enter Residence ID No" name="resident_id"  data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed" required>
                      </div>
                      <div class="error2" style="display:none">Residence ID No Exists !</div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_resident_exp_date">Residence ID Exp Date</label>
                      <div class="p-relative">
                        <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                        <input  type="date" class="form-control" id="tenant_resident_exp_date"  placeholder="Enter Resident Id" name="tenant_resident_exp_date">
                      </div>
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label>Nationality<small class="textRed">*</small></label>
                      <div class="p-relative">
                        <i class="fa fa-money icn-add" aria-hidden="true"></i>
                        <input type="text" placeholder="Enter Nationality" required name="nationality_name" class="form-control nationality_name" id="nationality_name">

                        <input type="hidden" name="nationality" id="nationality" value="{{ isset($tenant)?  old('nationality',$tenant->nationalities_id): old('nationality','')}}">
                        
                      </div>
                    </div> 
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="passport_no">Passport No</label>
                      <div class="p-relative">
                        <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="passport_no"  placeholder="Enter Passport No" name="passport_no">
                      </div>
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_gender">Gender</label>
                      <div class="p-relative">
                        <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                        <select class="form-control"  id="tenant_gender"  name="tenant_gender" >
                          <option value=""> Select Gender </option>
                          <option value="0" {{(old('tenant_gender', isset($tenant)?  $tenant->tenant_gender : '') == 0) ? 'selected' : '' }}> Male </option>
                          <option value="1" {{(old('tenant_type_id', isset($tenant)?  $tenant->tenant_gender : '') == 1) ? 'selected' : '' }}> Female </option>
                        </select>
                      </div>
                      
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_date_of_birth">Date Of Birth</label>
                      <div class="p-relative">
                        <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                        <input  type="date" class="form-control" id="tenant_date_of_birth"  placeholder="Enter Date Of Birth" name="tenant_date_of_birth">
                      </div>
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="employer_name">Employer Name</label>
                      <div class="p-relative">
                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="tenant_employer_name"  placeholder="Enter Employer Name" name="tenant_employer_name">
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="location_id">Office Location<small class="textRed">*</small></label>
                      <div class="p-relative">
                        <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                        <input type="text" placeholder="Enter Office Location" required name="location_name" class="form-control location_name" id="location_name">

                        <input type="hidden" name="location_id" id="location_id" value="{{ isset($tenant->location_id)?  old('location_id',$tenant->location_id): old('location_id','')}}">
                        
                      </div>               
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="designation">Designation</label>
                      <div class="p-relative">
                        <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                        <input type="text" class="form-control" id="designation"   placeholder="Enter Designation" name="designation"> 
                      </div>               
                    </div>
                  </div>
                  @if(isset($tenant))
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label>Status</label>
                      <div class="p-relative">
                        <i class="fa fa-money icn-add" aria-hidden="true"></i>
                        <select class="form-control" id="Status" name="Status" >
                          <option {{(old('Status', isset($tenant)?  $tenant->status : '0') == '0') ? 'selected' : '' }} value="0">Normal</option>
                          <option {{(old('Status', isset($tenant)?  $tenant->status : '1') == '1') ? 'selected' : '' }} value="1">VIP</option>
                        </select>
                      </div>
                    </div> 
                  </div>
                  @endif
                </div>

                <div class="row" id="row_comp" style="display: none">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_company_name">Company Name</label>
                      <div class="p-relative">
                        <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                        <input type="phone" class="form-control" id="tenant_company_name" placeholder="Enter Company Name" name="tenant_company_name">
                      </div>
                    </div>
                  </div> 
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tenant_contact_person">Contact Person</label>
                      <div class="p-relative">
                        <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="tenant_contact_person"  placeholder="Enter Contact Person" name="tenant_contact_person">
                      </div>
                    </div>
                  </div>
                  <div class="w-100"></div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="com_reg_no">Commercial Reg. No<small class="textRed">*</small></label>
                      <div class="p-relative">
                        <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                        <input type="text" class="form-control" id="com_reg_no" required  placeholder="Enter CR No" name="com_reg_no">
                      </div>
                      <div class="error3" style="display:none">Commercial Reg. No Exists !</div>
                    </div>
                  </div>
                </div> 

     <!--  <div class="w-100"></div>
     <button type="submit" class="btn btn-primary">SAVE</button> -->
     
   </div>
   <div class="clearfix"></div>
   <h4>Contact Details</h4>
   <div class="dataSearchBox">
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_contact_address">Contact Address</label>
          <div class="p-relative">
            <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
            <textarea name="tenant_contact_address" class="form-control" placeholder="Enter Contact Address "></textarea>
          </div>
        </div>
      </div> 
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_contact_address">Secondary Address</label>
          <div class="p-relative">
            <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
            <textarea name="tenant_secondary_address" class="form-control" placeholder="Enter Secondary Address "></textarea>
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_post_box">Postal Box</label>
          <div class="p-relative">
            <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
            <input  type="text" class="form-control" id="tenant_post_box"  placeholder="Enter Postal Box" name="tenant_post_box">
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="simpleFormCode">Postal Code</label>
          <div class="p-relative">
            <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
            <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Postal Code" name="tenant_pc" data-rule-pattern="^(0|[1-9][0-9]*)$" data-msg-pattern="Allowed only Numeric Values" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed">
          </div>
        </div>
      </div>
      
      <div class="w-100"></div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="gsm_no">Mobile No<small class="textRed">*</small></label>
          <div class="p-relative">
            <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
            <input  type="text" required class="form-control mob_validation_13 mob_oman_13" id="tenant_contact_no"  onkeypress="return isNumber(event)" placeholder="Enter Mobile No" name="tenant_contact_no" value="00968"> 
          </div>
          <div class="error1" style="display:none">Mobile No Exists !</div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="gsm_no">Office No</label>
          <div class="p-relative">
            <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
            <input  type="text" class="form-control mob_validation" id="gsm_no" onkeypress="return isNumber(event)" placeholder="Enter Office No" name="gsm_no">
          </div>
        </div>
      </div>
      
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_residence_tel">Residence Tel</label>
          <div class="p-relative">
            <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" id="tenant_residence_tel" onkeypress="return isNumber(event)"  placeholder="Enter Residence Tel" name="tenant_residence_tel">
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_personal_email">Personal Email</label>
          <div class="p-relative">
            <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
            <input type="email" class="form-control" id="tenant_personal_email"   placeholder="Enter Personal Email" name="tenant_personal_email">
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_fax_no">Fax No</label>
          <div class="p-relative">
            <i class="fa fa-fax icn-add" aria-hidden="true"></i>
            <input  type="text" class="form-control" id="tenant_fax_no"  placeholder="Enter Fax No" name="tenant_fax_no">
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_ice_name">ICE Name</label>
          <div class="p-relative">
            <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" id="tenant_ice_name" placeholder="Enter ICE Name" name="tenant_ice_name">
          </div>
        </div>
      </div> 
      
      <div class="col-sm-6">
        <div class="form-group">
          <label for="tenant_ice_contact_no">ICE Contact No</label>
          <div class="p-relative">
            <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" id="tenant_ice_contact_no"  placeholder="Enter ICE Contact No" name="tenant_ice_contact_no" data-rule-pattern="^((\+)?(\d{2,2}))?(\d{13}){1}?$" data-msg-pattern="Allowed only 13 Or 15 Digit Numeric Values">
          </div>
        </div>
      </div>
      <div class="w-100"></div>
      
      
    </div>
  </div>
  <div class="clearfix"></div>
  <h4>Bank Details</h4>
  <div class="dataSearchBox">
    <div class="row">
     <div class="col-sm-6">
      <div class="form-group">
        <label for="unit_id">Bank</label>
        <div class="p-relative">
          <i class="fa fa-university icn-add" aria-hidden="true"></i>
          <select class="form-control" id="bank_id"  name="bank_id">
            <option value=""> Select Bank </option>
            @foreach($banks as $bank)
            <option  {{(old('bank_id', isset($tenant)?  $tenant->bank_id : 0) == $bank->id) ? 'selected' : '' }} value="{{$bank->id}}">{{$bank->bank_name}}</option>
            @endforeach                  
          </select>  
        </div>              
      </div>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <label for="tenant_acc_no">Account No</label>
        <div class="p-relative">
          <i class="fa fa-credit-card icn-add" aria-hidden="true"></i>
          <input  type="text" class="form-control" id="tenant_acc_no"  placeholder="Enter Account No" name="tenant_acc_no">
        </div>
      </div>
    </div>
    <div class="w-100"></div>
    



  </div>
</div>
<div class="clearfix"></div>
<div class="sub-head">Docs Upload (Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
  <div class="row">
    <div class="col-sm-5">
      <div class="form-group">
       <label for="tenant_doc_category">Doc Category</label>
     </div>
   </div>
   <div class="col-sm-5">
    <div class="form-group">
     <label for="tenant_doc_path_name">Docs </label>
   </div>
 </div>
</div>


<div class="field_wrapper_docs">
  <div class="row" id="1">
    <div class="col-sm-5">
      <div class="form-group">
        
        <div class="p-relative">
          <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
          <select class="form-control" id="tenant_doc_category"  name="tenant_doc_category[1]" >
            <option value="">Select </option>                  
            <option value="0">Resident Card</option>
            <option value="1">Passport</option> 
            <option value="2">CR</option>
            <option value="3">Others</option>                               
          </select>  
        </div>              
      </div>
    </div> 
    <div class="col-sm-5">
      <div class="form-group">
       
        <div class="p-relative">
          <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
          <input type="file" class="form-control upload"  id="tenant_doc_path_name"  name="tenant_doc_path_name[1]" >
        </div>
      </div>
    </div>
    <div class="col-sm-2">
      <div class="dataSearchLabel w-100" style="margin-top: 2px"></div>
      <button type="button" class="btn btn-primary add_docs_button">Add</button>
      <button title="Delete" type="button" class="btn btn-warning remove_doc_button"  style="display:none;"><i class="fa fa-trash-o "></i></button>
    </div>
    <div class="w-100"></div>
  </div>
  @if(!empty($tenant->tenantDocs)) 

  
  @foreach ($tenant->tenantDocs  as $doc) 
  <div class="row ro ">
    <div class="col-sm-4">
      {{$doc->TenantDocCategoryName }}
    </div>
    <div class="col-sm-5">
      <div class="form-group">
       <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_doc_path_name)}}">
        {{$doc->tenant_doc_name}}  </a>
        
      </div>
    </div>
    <div class="col-sm-2">
      <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
      <input type="hidden" class="doc_id" name="doc_id" id="doc_id" value="{{$doc->id}}">
      <button type="button" title="Delete" class="btn btn-warning remove_button img-closed"><i class="fa fa-trash-o "></i></button>
    </div>
    <div class="w-100"></div>
  </div>
  @endforeach    
  
  @endif
</div>    

</div>
<div class="row">
  <div class="col">
   <div class="w-100"></div>
   <button type="submit" class="btn btn-primary save-enquiry" id="submitBtn">Save</button>
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
</div>
</div>

<script>
  $(document).ready(function() {
    
    $("#leade_search_pop").validate();

    $(".mob_validation").addClass("customRegMob");

      $.validator.addClassRules({
        customRegMob: {
          customRegMob: true
        }
      });
      jQuery.validator.addMethod("customRegMob", function(value, element) {
        return this.optional(element) || /^\d{1,13}$/i.test(value);
      }, "Please Enter Less Than 13 Digit Number");



      $('.mob_oman_code').on('keypress, keydown', function(event) {

        var $field = $(this);
        var readOnlyLength = $('.mob_oman_code').val().length;
        if ((event.which != 37 && (event.which != 39)) &&
         ((this.selectionStart < readOnlyLength) ||
          ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
         return false;
     }

   });

      $('.mob_oman_13').on('keypress, keydown', function(event) {

        var $field = $(this);
        var readOnlyLength = 5;
        if ((event.which != 37 && (event.which != 39)) &&
         ((this.selectionStart < readOnlyLength) ||
          ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
         return false;
     }

   });
      $(".mob_validation_13").addClass("customRegMob_thirteen_digit");

      $.validator.addClassRules({
       customRegMob_thirteen_digit: {
        customRegMob_thirteen_digit: true
      }
    });
      jQuery.validator.addMethod("customRegMob_thirteen_digit", function(value, element) {
       return this.optional(element) || /^\d{13}$/i.test(value);
     }, "Please Enter 13 Digit Number");


  })

  $(document).on('submit', 'form.sbt_form', function (event) {
    event.preventDefault();
    var form = $(this);
    var data = new FormData($(this)[0]);
    var url = "{{url('/tenantPopupAction')}}";
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
          //  alert(value);
            if(key=='tenant_name'){
              $('#tenants_id').val(value); ///done till here
            }
            else{
              
            $.ajax({
                          method: 'POST', // Type of response and matches what we said in the route
                          url:"{{url('/tenantNameAjaxCode')}}",
                          data:{'id' : key,'_token':"{{ csrf_token() }}"},
                          cache: false,
                          dataType: "json",
                          success: function(response){ // What to do if we succeed
                            if(response){
                              $("#tenants_id").val(response.tenant_name);
                              $("#tenant_name").val(response.tenant_name);
                              $("#tenant_id").val(key);
                              $("#occupant_name").val(response.tenant_name);
                              //$("#occupant_id").val(response.id);
                              $("#occupant_primary_contact_no").val(response.tenant_contact_no);
                              $("#occupant_email").val(response.tenant_personal_email);
                            }else{
                              $("#tenants_id").val('');
                              $("#tenant_name").val('');
                              $("#tenant_id").val('');
                              $("#occupant_email").val('');
                              $("#occupant_primary_contact_no").val('');
                              $("#occupant_id").val('');
                              $("#occupant_name").val('');
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
/**********************starts *****************************/
$(document).on("change",".upload",function(){
            
            fileUpload($(this));
  });  
  $(document).ready(function() {
    $('#nationality_name').autocomplete({
      source : '{!!URL::route('nationalityAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        $('#nationality').val(ui.item.ids);
        }
    });
    $('#location_name').autocomplete({
      source : '{!!URL::route('locationsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        $('#location_id').val(ui.item.ids);
        }
    });
/*******************************************************************/
    $("#form_sample_2").validate({
    
    rules: {
          tenant_personal_email: {
              customEmail: true
              },
           tenant_residence_tel : {
             maxlength: 8,
             minlength: 8,
             number: true
          }       
       },
	   
	    messages: {
        tenant_residence_tel: {
          maxlength:"Please Enter 8 Digit Number",
          minlength:"Please Enter 8 Digit Number",
        }
      },
       submitHandler: function(form) {
          $('#submitBtn').prop('disabled', true);
          $(".tenant_contact_no").valid();
          form.submit();
     }, 
       
  });
  jQuery.validator.addMethod("customEmail", function(value, element) {
    return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
  }, "Please enter a valid email address");
    /*$('.selectpicker').selectpicker();*/
    var Tenant_type = $('#tenant_type_id').val();
    if(Tenant_type == 1){

        $('#row_comp').hide();
        $('#row_ind').show(); 
         $("#resident_id").show().prop('required',true);
      }else{

        $('#row_comp').show();
        $('#row_ind').hide(); 
         $("#resident_id").hide().prop('required',false);
      }
    $('#tenant_type_id').change(function(){
      var type = $(this).val();
      if(type == 1){

        $('#row_comp').hide();
        $('#row_ind').show(); 
        $("#resident_id").show().prop('required',true);
      }else{

        $('#row_comp').show();
        $('#row_ind').hide(); 
        $("#resident_id").hide().prop('required',false);
      }
          
    });


  });
  function fileUpload(file){
   
      
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
     
      return this.optional(element) || (element.files[0].size <= param) 

    });
/********************************Add Image multiple*************************************/    
    var maxField = 40; //Input fields increment limitation
    var addDocButton = $('.add_docs_button'); //Add button selector
    var wrapperDoc = $('.field_wrapper_docs'); //Input field wrapper
     
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(wrapperDoc).on('click', '.add_docs_button',function(){
    
      var lastRowId  = parseInt($(".field_wrapper_docs .row").first().attr("id")); 
      var values = $("input[name='tenant_doc_path_name["+lastRowId+"]']")
              .map(function(){
                if($(this).val())return $(this).val();}).get();

     if (($(".upload").valid() != 1 ) || ($("select[name='tenant_doc_category["+lastRowId+"]']").val() =='') || values =='') { 
         
          alert("Select Doc Category And Docs Upload ");

      }else{
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
            var lastRowId       = lastRowId + 1;
            var fieldDocHTML = '<div class="row ro" id="'+lastRowId+'" ><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-anchor icn-add" aria-hidden="true"></i><select class="form-control margin-top-8" id="tenant_doc_category"  name="tenant_doc_category['+lastRowId+']" ><option value="">Select </option><option value="0">Resident Card</option><option value="1">Passport</option><option value="2">CR</option><option value="3">Others</option></select></div></div></div><div class="col-sm-5"><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control"  id="tenant_doc_path_name"  name="tenant_doc_path_name['+lastRowId+']" ></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 2px"></div><button type="button" class="btn btn-primary add_docs_button">Add</button><button title="Delete" type="button" class="btn btn-warning remove_doc_button"  style="display:none;"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html
            $(wrapperDoc).prepend(fieldDocHTML); //Add field html
            $('.field_wrapper_docs .row:not(:first)').find('.add_docs_button').remove();
            $('.field_wrapper_docs .row:not(:first)').find('.remove_doc_button').show();
        }
      }
    });
    
    //Once remove button is clicked
    $(wrapperDoc).on('click', '.remove_doc_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        
             $(this).closest('.row').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          
        x--; //Decrement field counter

    });
    $(wrapperDoc).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();
        var doc_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Docs')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('tenants') }}' + '/' + doc_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'doc_id': doc_id }
              });
             $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        x--; //Decrement field counter

    });

/*****************************************************************************************/ 
    $("#tenant_contact_no").keyup(function(){
      var tenant_contact_no = $("#tenant_contact_no").val();
      var tenant_id = $("#tenant_id").val();
      
      if($("#tenant_contact_no").val().length > 5 ){
        $.ajax({
        method: "POST",
        url: "{{route('checkMobileExist')}}",
        data: { tenant_contact_no: tenant_contact_no, tenant_id: tenant_id, 
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
    /*****************************************************************************************/ 
     $("#resident_id").keyup(function(){
      var resident_id = $("#resident_id").val();
      var tenant_id = $("#tenant_id").val();
      //alert(resident_id);
      $.ajax({
        method: "POST",
        url: "{{route('checkResidenceExist')}}",
        data: { resident_id: resident_id, tenant_id: tenant_id, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
            $(".error2").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
          }else{
           $(".error2").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }
 
       }
     });
    });
    /*****************************************************************************************/    
       $("#com_reg_no").keyup(function(){
      var com_reg_no = $("#com_reg_no").val();
      var tenant_id = $("#tenant_id").val();
      //alert(com_reg_no);
      $.ajax({
        method: "POST",
        url: "{{route('checkCommercialExist')}}",
        data: { com_reg_no: com_reg_no, tenant_id: tenant_id, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
            $(".error3").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
          }else{
           $(".error3").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }

       }
     });
    });
</script>
