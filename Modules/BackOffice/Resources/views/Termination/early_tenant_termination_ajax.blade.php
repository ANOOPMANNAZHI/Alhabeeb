<!--Agreement Section starts -->            
  
         <div class="card card-box salesSearchBox " id="agdiv">
            <div class="dataSearchBox">
                <div class="card-body row">
                   
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
                                <input type="hidden" name="tenant_contract_id" id="tenant_contract_id" value="">
                                <input type="hidden"  name="contract_id" id="contract_id" value="{{$tenantContract->id}}">
                            </div>
                        </div>
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Agreement Date  :  </b><span>{{isset($tenantContract->created_at)?$tenantContract->created_at->format('d/m/Y'):""}}</span></h5>
                            </div>
                        </div>
                </div>
            </div>
            <div class="sub-head">Building Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
               
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
							 <input type="hidden" class="unit_id" name="unit_id" id="unit_id" value="{{ $tenantContract->unit->id}}">
						</div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
                        </div>
                    </div>
                    @if($tenantContract->occupant_id)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Name :  </b><span>{{$tenantContract->occupant->occupant_name}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Mob No:  </b><span>{{$tenantContract->occupant->occupant_primary_contact_no?? "NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Email:  </b><span>{{$tenantContract->occupant->occupant_email ?? "NA"}}</span></h5>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="sub-head"></div>
            <div class="dataSearchBox" >    
                <div class="card-body row">
                
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>TakeOver Date :  <small class="textRed">*</small></b><span><input required type="date" required name="termination_takenover_date" class="form-controll" value="{{isset($termination->termination_takenover_date)? $termination->termination_takenover_date->format('Y-m-d') : ''}}" id="termination_takenover_date" ></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Termination Date:  <small class="textRed">*</small></b><span><input required type="date" name="termination_date" class="form-controll" value="{{isset($termination->termination_date)? $termination->termination_date->format('Y-m-d') : ''}}" id="termination_date" ></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-12 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark:  <small class="textRed">*</small></b><span>
								<textarea required cols="35"  name="termination_remark" class="form-controll full">{{isset($termination->termination_remark)? $termination->termination_remark :''}}</textarea>
								<!--<input type="text"  name="termination_remark" class="form-controll" value="{{--isset($termination->termination_remark)? $termination->termination_remark : ''--}}"></span></h5> -->
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="sub-head">Contract Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Valid To :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
                        </div>
                    </div>
                   
                    @if(isset($tenantContract->tenant_contract_duration_countdown))
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                                 <h5 class="details"><b>Duration :  </b><span>
                                  @php 
                                        $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
                                    @endphp
                                   

                                        {{$duration[0]}} Year
                                        {{$duration[1]}} Month
                                        {{$duration[2]}} Days

                                   
                                </span></h5>
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContract->tenant_contract_value)?numberFormat($tenantContract->tenant_contract_value):"NA"}} OMR</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Rent (P.M) :  </b><span>{{ numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?numberFormat($tenantContract->vaccant_date):"NA"}} </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Rent Paid by Previous Tenant :  </b><span>{{isset($tenantContract->last_rent)? numberFormat($tenantContract->last_rent)." OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Last Paid Rent:  </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? numberFormat($tenantContract->tenant_contract_last_paid_amt)." OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                    @if(isset($tenantContract->tenant_contract_muncipality_agr_no))
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if(isset($tenantContract->tenant_contract_electric_water))
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$tenantContract->tenant_contract_electric_water?? "NA"}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if($tenantContract->tenant_contract_registered_in)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if($tenantContract->tenant_contract_payment_type)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractPaymentName}}</span></h5>
                        </div>
                    </div>
                    @endif
                  
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>PDC  :  </b><span>{{($tenantContract->pdc_check ==null)?"No":"Yes"}} </span></h5>
                        </div>
                    </div>
              
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Invoice Generate  :  </b><span>{{($tenantContract->invoice_check ==null)?"No":"Yes"}} </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Registered in Municipality  :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}} </span></h5>
                        </div>
                    </div>    
                    @if($tenantContract->tenant_contract_note)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark :  </b><span>{{$tenantContract->tenant_contract_note}} </span></h5>
                        </div>
                    </div>
                    @endif
                </div>
             </div>
            <div class="sub-head">Payment Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
              
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Deposit Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt)." OMR":"NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?numberFormat($tenantContract->tenant_contract_guarantee_cheque_details)." OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt No:  </b><span>{{$tenantContract->tenant_contract_receipt_no ?? 'NA' }}</span></h5>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt date:  </b><span>{{isset($tenantContract->tenant_contract_receipt_date)?$tenantContract->tenant_contract_receipt_date->format('d/m/Y'):"NA"}}</span></h5>
                        </div>
                    </div>
                   
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?numberFormat($tenantContract->tenant_contract_receipt_amt)." OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                    
                </div>
            </div>
            @if(!empty($tenantContract->tenantDocument))
            <div class="sub-head">Document  </div>
            <div class="dataSearchBox">    
                <div class="card-body row">
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Document :  </b><span>
                            @if(!empty($tenantContract->tenantDocument)) 
                              @foreach ($tenantContract->tenantDocument  as $doc) 
                                <a href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}" target="_blank">
                                    {{$doc->tenant_documents_name}}  
                                </a>
                              @endforeach    
                  
                            @endif
                            </span></h5>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col"><button type="submit" class="btn btn-primary">Save</button> 
        </div>

<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
  $(document).ready(function() {

        var dtToday = new Date();

        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();
        if(month < 10)
            month = '0' + month.toString();
        if(day < 10)
            day = '0' + day.toString();
        
        var maxDate = year + '-' + month + '-' + day;
		/*
        @if(isset($termination->termination_takenover_date))
            $('#termination_takenover_date').attr('min',maxDate);
			$('#termination_date').attr('min', maxDate);
        @else
            $('#termination_takenover_date').attr('min', maxDate);
            $('#termination_date').attr('min', maxDate);
        @endif
		*/

      $("#termination_takenover_date").on('change',function(e){
          
          var takeover_dt   = $(this).val();
          var terminate_dt  = $('#termination_date').val();
          var dtToday = new Date();
    
          var month = dtToday.getMonth() + 1;
          var day = dtToday.getDate();
          var year = dtToday.getFullYear();
          if(month < 10)
              month = '0' + month.toString();
          if(day < 10)
              day = '0' + day.toString();
          
          var maxDate = year + '-' + month + '-' + day;
      
          

      });
      
      $('#termination_date').on('focusout',function(){
       
          var terminate_dt   = $(this).val();
          var takeover_dt    = $('#termination_takenover_date').val();
          var dtToday = new Date();
    
          var month = dtToday.getMonth() + 1;
          var day = dtToday.getDate();
          var year = dtToday.getFullYear();
          if(month < 10)
              month = '0' + month.toString();
          if(day < 10)
              day = '0' + day.toString();
          
          var minDate = year + '-' + month + '-' + day;          
          if(terminate_dt<takeover_dt)
          {
            alert('Termination Date is Lesser Than Takeover Date ! Are You Sure You Want To Continue?');
          }    
                    
          
      });
   
    })

      </script>

<!--Payment ends -->
