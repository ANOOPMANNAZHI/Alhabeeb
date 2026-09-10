@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">


@section('content')  
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ (isset($depositReceiptInfo))? 'Edit' : 'Add'}} Deposit Receipt</div>
    </div>
    {{ (isset($depositReceiptInfo))?   Breadcrumbs::render('editDepositReceipt',$depositReceiptInfo,Session::get('current')) :  Breadcrumbs::render('addDepositReceipt') }} 
  </div>
</div>
<form action="{{isset($depositReceiptInfo)?route('updateDepositReceiptAction',$depositReceiptInfo->id):route('addDepositReceiptAction')}}" method="POST" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" id="receipt_generation_form">
    <input type="hidden" name="previous_url" value="{{url()->previous() }}" >
    {{csrf_field()}}
<div class="row">
  <div class="col-sm-12">
       
         <!--Agreement Section starts -->
         <div class="card card-box salesSearchBox">
        
                <h4>
                   <button type="button" title="Scan Qr Code" class="btn btn-circle btn-primary align-right scan" data-id="" datas-id="Tenant" data-toggle="modal" data-target="#myModal" data-placement="top" >Scan QrCode</button> 
                </h4>
            <div class="dataSearchBox">
                <div class="row">
                    <div class="w-100"></div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="simpleFormEmail">Mobile No<small class="textRed">*</small></label>
                                    <div class="p-relative">
                                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                                            <input type="text" name="mob_no" id="mob_no" required placeholder="Enter Mobile No" class="form-control" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->tenantContractInfo->tenant->tenant_contact_no:''}}">
                                    </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="simpleFormEmail">Resident ID</label>
                                <div class="p-relative">
                                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                                    <input type="text" name="resident_card_id" id="resident_card_id" class="form-control" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->tenantContractInfo->tenant->resident_id:''}}" placeholder="Enter Resident ID">
                                </div>
                            </div>
                        </div>
                </div>
            </div> 
            <div class="sub-head">Tenant Details</div>
            <div class="dataSearchBox">    
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="building_name">Building Name :  </label>
                             <div class="p-relative">
                            <i class="fa fa-building icn-add" aria-hidden="true"></i>
                             <input type="text" name="building_name" id="building_name" class="form-control" value="{{ isset($depositReceiptInfo)?  old('building_name',$depositReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}" placeholder="Enter Building Name">
                        </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="building_code">Building Code : </label>
                             <div class="p-relative">
                            <i class="fa fa-building icn-add" aria-hidden="true"></i>
                            <input type="text" name="building_code" id="building_code" class="form-control" value="{{ isset($depositReceiptInfo)?  old('building_code',$depositReceiptInfo->tenantContractInfo->building->building_code): old('building_code','')}}" readonly placeholder="Building Code">
                        </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="unit_name"> Unit No : </label>
                             <div class="p-relative">
                            <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                                <span id="unit_id">
                                    <input type="text" name="unit_name" id="unit_name" class="form-control" value="{{ isset($depositReceiptInfo)?  old('unit_name',$depositReceiptInfo->tenantContractInfo->unit->unit_no): old('unit_name','')}}" readonly placeholder="Unit No">
                                </span>
                            </div>
                        </div>
                    </div>
                   <div class="col-sm-6">
                        <div class="form-group">
                             <label for="unit_code"> Unit Code : </label>
							<div class="p-relative">
                            <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                             <span><input type="text" name="unit_code" id="unit_code" class="form-control" value="{{ isset($depositReceiptInfo)?  old('unit_code',$depositReceiptInfo->tenantContractInfo->unit->unit_code): old('unit_code','')}}" readonly placeholder="Unit Code">
                            </div> 	
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                           <label for="tenant_contract_no">Agreement No   <small class="textRed">*</small>:</label>
                                 <div class="p-relative">
                                    <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                       
                    
                     <select class="form-control tenant_contract_no" name="tenant_contract_no" id="tenant_contract_no" >
                        @if(isset($depositReceiptInfo))
                            <option value="{{$depositReceiptInfo->tenant_contract_id}}">{{$depositReceiptInfo->tenantContractInfo->tenant_contract_no}}</option> 
                        @else
                            <option value="">Enter Mobile No Or Resident ID </option> 
                        
                        @endif                   
                    </select>  

                            
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="tenant_contract_date">Agreement Date :  </label>
               <div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                             <input type="date" name="tenant_contract_date" id="tenant_contract_date" class="form-control" readonly value="{{isset($depositReceiptInfo)?  old('tenant_contract_date',$depositReceiptInfo->tenantContractInfo->created_at->format('Y-m-d')): old('tenant_contract_date','')}}">
                            <input type="hidden" name="tenantContract" value="" id="tenantContract" placeholder="Agreement Date">    
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="tenant_contract_date">Agreement From :</label>
               <div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                             <input type="date" name="tenant_contract_effective_date" id="tenant_contract_effective_date" class="form-control" readonly value="{{isset($depositReceiptInfo)?  old('tenant_contract_date',$depositReceiptInfo->tenantContractInfo->tenant_contract_effective_date->format('Y-m-d')): old('tenant_contract_effective_date','')}}">
                            
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="tenant_contract_date">Agreement To: </label>
               <div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                             <input type="date" name="tenant_contract_valid_to_date" id="tenant_contract_valid_to_date" class="form-control" readonly value="{{isset($depositReceiptInfo)?  old('tenant_contract_date',$depositReceiptInfo->tenantContractInfo->tenant_contract_valid_to_date->format('Y-m-d')): old('tenant_contract_valid_to_date','')}}">
                               
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="building_code">Tenant Name :  </label>
                            <div class="p-relative">
                            <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                             <input type="text" name="tenant_name" id="tenant_name" class="form-control" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->tenantContractInfo->tenant->tenant_name:''}}" readonly placeholder="Tenant Name">
                         	</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="building_code">Tenant Code :</label>
                            <div class="p-relative">
                            <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                            <input type="text" name="tenant_code" id="tenant_code" class="form-control" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->tenantContractInfo->tenant->tenant_code:''}}" readonly placeholder="Tenant Code">
                        </div>
                        </div>
                    </div>
                </div>
             </div>
            <div class="sub-head">Payment Details</div>
            <div class="dataSearchBox">    
                <div class="row">
               		<div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_receipt_no">Receipt No : </label>
							<div class="p-relative">
                            <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>		
                            <input type="text" name="receipts_generation_receipt_no" id="receipts_generation_receipt_no" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->receipts_generation_receipt_no :$chequeReceiptNo}}" class="form-control" readonly="" name="receipts_generation_receipt_no" >
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_receipt_date">Receipt Date :  </label>
							<div class="p-relative">
                            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                            <input type="date" name="receipts_generation_receipt_date" value="{{isset($depositReceiptInfo)?$depositReceiptInfo->receipts_generation_receipt_date->format('Y-m-d'):date('Y-m-d')}}"   id="" class="form-control">
                             </div>
                        </div>
                    </div>
                    
                   <div class="col-sm-6">
                        <div class="form-group">
                          <label for="receipts_generation_payment_method">Payment Method<small class="textRed">*</small></label>
                          <div class="p-relative">
                             <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                            
                            <div class="form-control" > <input type="radio" class="payment_method" name="receipts_generation_payment_method" 	
                            {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'':'disabled'}}  	
                              {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'checked':(($depositReceiptInfo->receipts_generation_payment_method==1)?'checked':'')}} required value="1">Cheque	
                               <input type="radio" class="payment_method" name="receipts_generation_payment_method" {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'':'disabled'}} {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'':(( $depositReceiptInfo->receipts_generation_payment_method==2)?'checked':'')}} required value="2">	
                                Cash
                                <input type="radio" class="payment_method" name="receipts_generation_payment_method" {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'':'disabled'}} {{!isset($depositReceiptInfo->receipts_generation_payment_method)?'':(( $depositReceiptInfo->receipts_generation_payment_method==3)?'checked':'')}} required value="3">  
                                Bank Transfer


                            </div>
                          </div>
                          <label id="receipts_generation_payment_method-error" class="error" for="receipts_generation_payment_method"></label>
                          
                        </div>
                      </div>
                                    
                   
                    <!-- ---------  If cash selected Type div enable ----   -->
                    <div class="col-sm-6 type_div" {{!isset($depositReceiptInfo->receipts_generation_is_bounce_normal)?'style=display:none':(($depositReceiptInfo->receipts_generation_is_bounce_normal==2)?'style=display:block':'style=display:none')}} id ="type_div">
                        <div class="form-group">
                         <label for="receipts_generation_payment_method">Type: <small class="textRed">*</small> </label>
                         <div class="p-relative">
                                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                    <div class="form-control" >
                    <!-- 1 - Normal, 2- Bounce -->                                   
                      <input type="radio" class="pay_type" name="pay_type" {{!isset($depositReceiptInfo->receipts_generation_is_bounce_normal)?'checked':(($depositReceiptInfo->receipts_generation_is_bounce_normal==1)?'checked':'')}} required value="1">
                      Normal
                     
                      <input type="radio" class="pay_type" name="pay_type"  {{!isset($depositReceiptInfo->receipts_generation_is_bounce_normal)?'':(( $depositReceiptInfo->receipts_generation_is_bounce_normal==2)?'checked':'')}} required value="2">
                      Bounce
                      <label id="receipts_generation_payment_method-error" class="error" for="receipts_generation_payment_method"></label>
                        </div>       
                         </div>
                         <label id="pay_type-error" class="error" for="pay_type"></label>
                    </div> </div>
                    <!-- ---------  If Bounce radio selected PDC Of that contract should list ----   -->
                    <div class="col-sm-6" {{!isset($depositReceiptInfo->receipts_generation_is_bounce_normal)?'style=display:none':(($depositReceiptInfo->receipts_generation_is_bounce_normal==2)?'style=display:block':'style=display:none')}} id ="pdc_bounce_div"> 
                        <div class="form-group">
                            
                            <label for="pay_type">Bounce Cheque No:  <small class="textRed">*</small> </label>
                                <div class="p-relative">
                                <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                                <select name="pdc_bounce_cheque" class="form-control" id="pdc_bounce_cheque"  >
                                    

                                @if(isset($depositReceiptInfo->receipts_generation_is_bounce_normal) && $depositReceiptInfo->receipts_generation_is_bounce_normal==2)
                                    @foreach($contract_bounce_pdc as $bounce)

                                    <option value="{{$bounce->id}}" {{($bounce->id==$depositReceiptInfo->receipts_generation_is_pdc_bounce_id)?'SELECTED':''}}>{{$bounce->pdc_check_no}}
                                    </option>

                                    @endforeach
                                @endif
                              </select>   
                            </div>
                        </div>
                    </div>
              <!--- Deposit Receipt Bank Code Manadatory For AX-Post -->
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="bank_id">Bank  <small class="textRed">*</small>: </label>
                              <div class="p-relative">
                                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>      
                                <select name="bank_id" class="form-control" id="bank_id" required>
                                    <option value="">Select Bank</option>
                                    @foreach($bankMaster as $name)
                                      <option value="{{$name->id}}"  {{ isset($depositReceiptInfo)? ((old('bank_id',$depositReceiptInfo->bank_id) == $name->id)? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_name}}</option>
                                    @endforeach
                              </select>      
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="cheque_no" id="lbl_cheque">Cheque No :</label>
                            <div class="p-relative">
                                <!--<i class="fa fa-money icn-add cash-icon-fa" aria-hidden="true"></i>-->
                <span id="bank_code" class="cheq-code">
                                @php
                                $explArray = array();
                                if(isset($depositReceiptInfo)){
                                $explArray = explode('__',$depositReceiptInfo->receipts_generation_cheque_no);
                                }
                                @endphp
                                
                                <select name="pdc_bank_id"  id="pdc_bank_id" >
                                    <option value="">Code</option>
                                    @foreach($bankMaster as $name)
                                      <option value="{{$name->bank_code}}"  {{ isset($depositReceiptInfo)? ((old('pdc_bank_id',$name->bank_code) == $explArray[0])? 'selected' : '') : ''}} data-foo="{{$name->bank_code}}">{{$name->bank_code}}</option>
                                    @endforeach
                                </select> 
                                    
                            </span></b>
                                             
                            <span>
                             @if(count($explArray) == 1)
                                <input type="text" {{(isset($depositReceiptInfo->receipts_generation_payment_method)?(($depositReceiptInfo->receipts_generation_payment_method==1)?'':'readonly'):'')}} name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($depositReceiptInfo)?  old('cheque_no',$depositReceiptInfo->receipts_generation_cheque_no): old('cheque_no','')}}" placeholder="Cheque No" >	
                             @else 	
                                <input type="text" {{(isset($depositReceiptInfo->receipts_generation_payment_method)?(($depositReceiptInfo->receipts_generation_payment_method==1)?'':'readonly'):'')}} name="cheque_no" id="cheque_no" class="form-control cheq-no-box"  value="{{ isset($depositReceiptInfo)?  old('cheque_no',$explArray[1]): old('cheque_no','')}}" placeholder="Cheque No" >  
							 @endif 
                         </span></h5>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                             <label for="receipts_generation_amt">Amount <small class="textRed">*</small>:  </label>
                             <div class="p-relative">
                                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                             <input type="text" required onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" name="receipts_generation_amt" id="receipts_generation_amt" class="form-control read text-right allownumericwithdecimal"   value="{{ isset($depositReceiptInfo)?  old('receipts_generation_amt',numberFormat($depositReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}" placeholder="Enter Amount">
                             </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_amt">Comment :  </label><span>
                        	<div class="p-relative">
                                <i class="fa fa-comment icn-add" aria-hidden="true"></i><textarea class="form-control" name="receipts_generation_description" id="receipts_generation_description" rows="3" cols="60" placeholder="Enter Comment" >{{ isset($depositReceiptInfo)?  old('receipts_generation_description',$depositReceiptInfo->receipts_generation_description): old('receipts_generation_description','')}}</textarea></span>
                            </div>
                            </div>
                    </div>                    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="receipts_generation_amt">Remark :  </label>
                            <div class="p-relative">
                                <i class="fa fa-comment icn-add" aria-hidden="true"></i><span>
							<textarea class="form-control" name="receipts_generation_remark" id="receipts_generation_remark" rows="3" cols="60" readonly="" placeholder="Enter Remark" >{{ isset($depositReceiptInfo)?  old('receipts_generation_remark',$depositReceiptInfo->receipts_generation_remark): old('receipts_generation_remark','')}}</textarea>	
							</span>
                        </div>
                    </div>
                    </div>
                    
                </div>
            </div>
            
            <div class="sub-head">Financial Documentation</div>
            <div class="dataSearchBox">    
                <div class="row">
              
                     <div class="col-sm-6">
                        <div class="form-group">
                            <label for="finance_dim">Division:  </label>
                            <div class="p-relative">
                                <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                                <input type="text" name="finance_dim" id="finance_dim" value="{{ isset($depositReceiptInfo)?  old('finance_dim',($depositReceiptInfo->tenantContractInfo->building->ax_division=='01')?'HO':'PLM'): old('finance_dim','')}}"  class="form-control"  placeholder="Division">
                           
						 </div>
                                
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="building_dim">Building :  </label>
                            <div class="p-relative">
                                <i class="fa fa-building icn-add" aria-hidden="true"></i>
                            <input type="text" name="building_dim" id="building_dim" value="{{ isset($depositReceiptInfo)?  old('building_name',$depositReceiptInfo->tenantContractInfo->building->building_name): old('building_name','')}}"  class="form-control"  placeholder="Building">
                        </div>
                    </div>
                	</div>
                    
                </div>
            </div>

            <div class="sub-head" style="display: none;">Distribution Details</div>
            <div class="dataSearchBox" style="display: none;">    
    
              
            <div class="card-body row">
              <div class="table-wrap">
                <div class="table-responsive">
         
                    <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                        <tr>
                      
                      <th>Account Code</th>
                      <th>Description</th>
                      <th>Type</th>
                      <th>Dr. Amt</th>
                      <th>Cr. Amt</th>
                      <th>Narration</th>
                      
                      
                      </tr> 
                    </thead>
                        <tbody>
                            <tr >      
                                           
                              <td>
                                <input type="hidden" name="acc_code_dr_id" value="{{isset($acc_code_dr)?$acc_code_dr->id:''}}">
                                <input type="hidden" name="acc_code_cr_id" value="{{isset($acc_code_cr->id)?$acc_code_cr->id:''}}">
                                <input type="hidden" class="max-wid" name="acc_dr_first" id="acc_dr_first" required data-rule-pattern="\d{1,9}(,\d{3})*(\.\d+)?" data-msg-pattern="Allowed only Numeric" value="{{isset($acc_parameter->acc_params_dr_acc)?$acc_parameter->acc_params_dr_acc:''}}" ></td>
                              <td><input type="hidden" class="max-wid" required  name="acc_dr_desc" id="acc_dr_desc" value="{{isset($acc_parameter->acc_code_desc)?$acc_code_dr->acc_code_desc:''}}"></td>
                              <td><input type="hidden" class="max-wid" name="acc_dr_type" id="acc_dr_type"  value="{{isset($acc_parameter->acc_params_dr_type)?$acc_parameter->acc_params_dr_type:''}}"></td>
                              <td><input type="hidden" class="max-wid" readonly id="dr_amt_first" name="dr_amt_first" value="{{ isset($depositReceiptInfo)?  old('receipts_generation_amt',numberFormat($depositReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  ></td>
                              <td>
                                <input type="hidden" class="max-wid" readonly id="cr_amt_first" name="cr_amt_first" value="0"  >
                              </td>
                              <td><input type="hidden" class="max-wid" name="dr_narration_first   " id="narration_first"></td>
                            </tr>
                            <tr >      
                                           
                              <td><input type="hidden" class="max-wid" required data-rule-pattern="\d{1,9}(,\d{3})*(\.\d+)?" data-msg-pattern="Allowed only Numeric" name="acc_cr_second" id="acc_cr_second" value="{{isset($acc_parameter->acc_params_cr_acc)?$acc_parameter->acc_params_cr_acc:''}}" ></td>
                              <td><input type="hidden" class="max-wid" name="acc_cr_desc_second" id="acc_cr_desc_second" value="{{isset($acc_parameter->acc_code_desc)?$acc_code_cr->acc_code_desc:''}}"></td>
                              <td><input type="hidden" class="max-wid" name="acc_cr_type_second" id="acc_cr_type_second"  value="{{isset($acc_parameter->acc_params_cr_type)?$acc_parameter->acc_params_cr_type:''}}"></td>
                              <td><input type="hidden" class="max-wid" readonly name="dr_amt_second" id="dr_amt_second" value="0" ></td>
                              <td>
                                <input type="hidden" class="max-wid" readonly name="cr_amt_second"  value="{{ isset($depositReceiptInfo)?  old('receipts_generation_amt',numberFormat($depositReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  id="cr_amt_second" >
                              </td>
                              <td><input type="hidden" class="max-wid" name="cr_narration_second" id="narration_second"></td>
                            </tr>
                             <tr >  
                                <td align="right" colspan="3"><b>Total :</b> </td>
                                <td > <input type="hidden" class="max-wid" readonly name="total_dr"  value="{{ isset($depositReceiptInfo)?  old('receipts_generation_amt',numberFormat($depositReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  id="total_dr" ></td>
                                <td > <input type="hidden" class="max-wid" readonly name="total_cr"  value="{{ isset($depositReceiptInfo)?  old('receipts_generation_amt',numberFormat($depositReceiptInfo->receipts_generation_amt)): old('receipts_generation_amt','')}}"  id="total_cr" ></td>
                                <td></td>
                             </tr> 
                          </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        <div class="clearfix">
           
                  <button type="submit" class="btn btn-primary">SAVE</button>
       
           </div>
        
    </form>
<!--Payment ends -->


<!--Remaining Invoices ends -->
</div>
</div>

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script type="text/javascript">
$(document).ready(function() {
	
	 @if(!isset($depositReceiptInfo) && $isYearChequeCorrect == false)	
        alert("Current Year Is Not Match With the Sequence Year");	
    @elseif(!isset($depositReceiptInfo) && $isYearCashCorrect == false)	
        alert("Current Year Is Not Match With the Sequence Year");	
    @endif
	$("#receipt_generation_form").validate()
});
/**
 * Rebuild the Remark from whatever unit is currently selected.
 *
 * The unit field is sometimes a text input and sometimes a <select> that is
 * only built after the lookup returns, so the remark has to be composed from
 * the field's current value rather than from the ajax payload. Called again on
 * every unit change so picking a unit from the dropdown fills the number in.
 */
function refreshDepositRemark(){
    var $unitField = $('#unit_name');
    var unitNo = '';

    if($unitField.length){
        if($unitField.is('select')){
            unitNo = $.trim($unitField.find('option:selected').text());
            if(unitNo === 'Select Unit'){ unitNo = ''; }
        } else {
            unitNo = $.trim($unitField.val() || '');
        }
    }

    var buildingName = window.depositBuildingName || $.trim($('#building_name').val() || '');

    // Leave the remark alone until a unit is actually known, so a half-built
    // line is never what gets saved and printed.
    if(unitNo === ''){ return; }

    $('#receipts_generation_remark').val('DEPOSIT FOR UNIT NO. ' + unitNo + ' ' + buildingName);
}

// The unit field is replaced at runtime, so bind on the document.
$(document).on('change keyup', '#unit_name', function(){ refreshDepositRemark(); });

function ajaxCall(mobilenumber = '',resident_card_id = '',building_id = '',unit_id = ''){

    if(unit_id != '' || building_id != '')
    eventEl = '';

   if(resident_card_id != '')
    eventEl = 'resident_card_id';
   else if(mobilenumber != '')
     eventEl = 'mob_no';

      $.ajax({
        method: "POST",
        url: "{{route('receiptContractDetails')}}",
        data: { mobile: mobilenumber, resident_card_id:resident_card_id,
             building_id : building_id,unit_id:unit_id,
          "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
              
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);

                  if(result[0].tenant_contract_no){
                     var validator = $( "#receipt_generation_form" ).validate()
                     validator.resetForm();
                    if(result[1].resident_id != null){
                    $('#resident_card_id').val(result[1].resident_id);
                    $("#resident").html("Resident ID");
                }else{
                    $('#resident_card_id').val(result[1].com_reg_no);
                    $("#resident").html("Commercial Reg. No");
                }
                    $('#mob_no').val(result[1].tenant_contact_no);
                    
                   // $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                                          
                     
                        $('#tenant_contract_no').html('<option value="'+ result[0].id +'">'+ result[0].tenant_contract_no +'</option>');
                     
                        var agreeDt = new Date(result[0].created_at);
                        
                        var day = ("0" + agreeDt.getDate()).slice(-2);
                        var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                        var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;

                        var agreeEff = new Date(result[0].tenant_contract_effective_date);
                        var agreeTo = new Date(result[0].tenant_contract_valid_to_date);
                        
                        var day = ("0" + agreeEff.getDate()).slice(-2);
                        var month = ("0" + (agreeEff.getMonth() + 1)).slice(-2);
                        var agreeEffDt = agreeEff.getFullYear()+"-"+(month)+"-"+(day) ;

                        var day = ("0" + agreeTo.getDate()).slice(-2);
                        var month = ("0" + (agreeTo.getMonth() + 1)).slice(-2);
                        var agreeToDt = agreeTo.getFullYear()+"-"+(month)+"-"+(day) ;

                        $("#receipts_generation_amt").val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                        $('#dr_amt_first').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                        $('#cr_amt_first').val(0);
                        $('#dr_amt_second').val(0);
                        $('#total_dr').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                        $('#total_cr').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                        $('#cr_amt_second').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                        $('#tenant_contract_date').val(agreeDtVal);
                        $('#tenant_contract_effective_date').val(agreeEffDt); 
                        $('#tenant_contract_valid_to_date').val(agreeToDt); 
                        $('#tenantContract').val(result[0].id);
                        $('#building_name').val(result[2].building_name);
                        $('#building_dim').val(result[2].building_name);
                        if(result[2].ax_division=='02')
                          var ax_div = 'PLM';
                        else
                          var ax_div ='HO' ;
                        $('#finance_dim').val(ax_div);
                        $('#building_code').val(result[2].building_code);
						 //$('#receipts_generation_remark').val('Deposit '+result[3].unit_no+' , '+result[2].building_name+' @ '+formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3))+'/-');
                        // Building name is known now; the unit is filled in by
                        // refreshDepositRemark() once it has actually been
                        // resolved. Setting the whole line here used to emit
                        // "DEPOSIT FOR UNIT NO.  <building>" whenever the lookup
                        // returned several units, because result[3] is an array
                        // in that case and result[3].unit_no is undefined.
                        window.depositBuildingName = result[2].building_name;
                        refreshDepositRemark();
						if(unit_id == ''){  
                            $('#unit_id').html('');
                            $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-controll">'); 
                            $('#unit_name').val(result[3].unit_no);
                            refreshDepositRemark();
                        }
                      
                        $('#unit_code').val(result[3].unit_code);
                        $('#tenant_name').val(result[1].tenant_name);
                        $('#tenant_code').val(result[1].tenant_code);
                        $('.type_div').hide();
                        $("#pdc_bounce_div").hide();
                  }
                  else if(result[0].length > 1){
                     var validator = $( "#receipt_generation_form" ).validate()
                     validator.resetForm();
                    if(result[1].resident_id != null){
                    $('#resident_card_id').val(result[1].resident_id);
                    $("#resident").html("Resident ID");
                }else{
                    $('#resident_card_id').val(result[1].com_reg_no);
                    $("#resident").html("Commercial Reg. No");
                }
                    $('#mob_no').val(result[1].tenant_contact_no);

                    $('#tenant_contract_no').html('<option value="">Select Agreement No</option>');
                        $.each(result[0], function(key, value) {
                            $('#tenant_contract_no').append('<option value="'+ value['id'] +'">'+ value['tenant_contract_no'] +'</option>');
                        });
                        if(result[2][0].ax_division)
                          $('#ax_division').val(result[2][0].ax_division);
                        else
                          $('#ax_division').val('PLM');
                  }
                  $('#registerd_mob_no-error').hide();


               if(result[3].length > 1  && unit_id == ''){

                      $('#unit_id').html('');
                      var unit_name_txt = '<select name="unit_name" class="form-control" id="unit_name" class="form-controll"><option value="">'+ 'Select Unit' +'</option>';
                          $.each(result[3], function(key, value) {                            
                            unit_name_txt +=  '<option value="'+ value.id +'" '+ '>'+ value.unit_no +'</option>';
                            });
                          unit_name_txt +=  '</select>';
                      $('#unit_id').html(unit_name_txt);    
                      $('#building_code').val(result[2][0].building_code);
                  }else if(result[3]){
                     if(unit_id == ''){  
                        $('#unit_id').html('');
                        $('#unit_id').html('<input type="text" name="unit_name" id="unit_name" class="form-control">');
                        $('#unit_name').val(result[3].unit_no);
                        refreshDepositRemark();
                    }
                        $('#unit_code').val(result[3].unit_code);
                   }

                   $('.type_div').hide();
                   $("#pdc_bounce_div").hide();
                   $('.payment_method').prop('checked', false);
                           
            }else{//alert(data);  

                $('#mob_no').val("");
                $('#resident_card_id').val("");
                $('#tenant_contract_no').html('<option value="">'+ 'Enter Mobile No Or Resident ID ' +'</option>');
                
                
                /*$('#registerd_mob_no-error').show();*/
                var validator = $( "#receipt_generation_form" ).validate();
                if(eventEl == 'mob_no'){
                    validator.showErrors({
                      "mob_no": "It's Not A Registered Mobile Number,Please Try Again!"
                    });
                }
                else if(eventEl == 'resident_card_id'){
                    validator.showErrors({
                        "resident_card_id": "It's Not A Registered Resident Id,Please Try Again!"
              
                  });
                }

               
            }     

        }    

      });
}


$("#mob_no, #resident_card_id").on('change',function(event){
    event.preventDefault();  
    var eventEl      = $(this).attr('id');
    var mobilenumber = $('#mob_no').val();
    var resident_card_id = $('#resident_card_id').val();
    var mobile_size = mobilenumber.length;
    
    var selected = "";
    var uselected = "";
    var oselected = "";
    if(eventEl == 'resident_card_id' || eventEl == 'mob_no'){

        ClearInputDate(eventEl);
        (eventEl == 'resident_card_id')?mobilenumber='':resident_card_id='';

        ajaxCall(mobilenumber,resident_card_id);
    }

});



 $('#building_name').autocomplete({
      source : '{!!URL::route('complaintBuildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var clearTxt = ClearInputDate('building_name');
         if (ui.item != null || ui.item != undefined) {
        
          building_id = ui.item.ids;
          ajaxCall('','',building_id);
          }
        }

  }); 


$(document).on('change',"#unit_name",function(event){
    var unit_id = $(this).val();
    ajaxCall('','','',unit_id);
});


$(document).on('change','#tenant_contract_no',function(){  

    var contractId   = $(this).val();
    //var mobilenumber = $('#mob_no').val();
    //var resident_card_id = $('#resident_card_id').val();
    //var mobile_size = mobilenumber.length;
    if(contractId){
    $.ajax({
        method: "POST",
        url: "{{route('receiptContractDetailsByBuildingUnit')}}",
        data: {contractId:contractId, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
            if(data != 0){
                var result = $.parseJSON(data);
             
                var agreeDt = new Date(result[0].created_at);
                var agreeEff = new Date(result[0].tenant_contract_effective_date);
                var agreeTo = new Date(result[0].tenant_contract_valid_to_date);

                var day = ("0" + agreeDt.getDate()).slice(-2);
                var month = ("0" + (agreeDt.getMonth() + 1)).slice(-2);
                var agreeDtVal = agreeDt.getFullYear()+"-"+(month)+"-"+(day) ;

                var day = ("0" + agreeEff.getDate()).slice(-2);
                var month = ("0" + (agreeEff.getMonth() + 1)).slice(-2);
                var agreeEffDt = agreeEff.getFullYear()+"-"+(month)+"-"+(day) ;

                var day = ("0" + agreeTo.getDate()).slice(-2);
                var month = ("0" + (agreeTo.getMonth() + 1)).slice(-2);
                var agreeToDt = agreeTo.getFullYear()+"-"+(month)+"-"+(day) ;

                $("#receipts_generation_amt").val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                $('#dr_amt_first').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                $('#cr_amt_first').val(0);
                $('#dr_amt_second').val(0);
                $('#cr_amt_second').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)))
                $('#total_dr').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                $('#total_cr').val(formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3)));
                $('#tenant_contract_date').val(agreeDtVal);
                $('#tenantContract').val(result[0].id); 
                $('#tenant_contract_effective_date').val(agreeEffDt); 
                $('#tenant_contract_valid_to_date').val(agreeToDt); 
                $('#building_name').val(result[2].building_name);
                $('#building_dim').val(result[2].building_name);
                $('#building_code').val(result[2].building_code);
                if(result[2].ax_division=='02')
                  var ax_div = 'PLM';
                else
                  var ax_div ='HO' ;
                $('#finance_dim').val(ax_div);
				//$('#receipts_generation_remark').val('Deposit '+result[3].unit_no+' , '+result[2].building_name+' @ '+formatNumber(parseFloat(result[0].tenant_contract_rent).toFixed(3))+'/-');
               $('#receipts_generation_remark').val('DEPOSIT FOR UNIT NO. '+result[3].unit_no+' '+result[2].building_name);
			   $('#unit_id').html('');
                $('#unit_id').append('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
                $('#unit_name').val(result[3].unit_no);
                refreshDepositRemark();
                $('#unit_code').val(result[3].unit_code);
                $('#tenant_name').val(result[1].tenant_name);
                $('#tenant_code').val(result[1].tenant_code);
                $('#mob_no').val(result[1].tenant_contact_no);
                $('#resident_card_id').val(result[1].resident_id);
                $('.pay_type').prop('checked', false);
                $('.payment_method').prop('checked', false);
                
            }
        }
    });
    }
    else{

        ClearInputDate('tenant_contract_no');
        
    }
});

$(document).on('click','.payment_method',function(){  
    
    var payment_method = $(this).val();
   
    if(payment_method == 1){ // Cheque
            $("#cheque_no").prop("disabled", false);
            $('#cheque_no').attr("required", true);
			      $("#pdc_bank_id").prop("disabled", false);
            $('#pdc_bank_id').attr("required", true);
            $('.pay_type').prop('checked', false)
            $('.type_div').hide();
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();

             $("#lbl_cheque").html("Cheque No :");
             $("#cheque_no").attr("placeholder","Cheque no"); 

             @if(isset($chequeReceiptNo))	
                $("#receipts_generation_receipt_no").val("{{$chequeReceiptNo}}");	
            @endif	
            @if(!isset($depositReceiptInfo) && $isYearChequeCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @elseif(!isset($depositReceiptInfo) && $isYearCashCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @endif
    }
    else if(payment_method == 2){ // Cash
            $('.type_div').show();
            $('#cheque_no').val('');           
            $("#cheque_no").prop("disabled", true);            
            $('#cheque_no').removeAttr('required');    
			$('#pdc_bank_id').prop('selectedIndex',0);
            $("#pdc_bank_id").prop("disabled", true);
            $('#pdc_bank_id').removeAttr('required'); 
            $('.pay_type').prop('checked', false)
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();
            @if(isset($cashReceiptNo))	
                $("#receipts_generation_receipt_no").val("{{$cashReceiptNo}}");	
            @endif	
            @if(!isset($depositReceiptInfo) && $isYearChequeCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @elseif(!isset($depositReceiptInfo) && $isYearCashCorrect == false)	
                alert("Current Year Is Not Match With the Sequence Year");	
            @endif
    }
    else if(payment_method == 3){ // Bank Transfer
            $("#cheque_no").prop("disabled", false);
            $('#cheque_no').attr("required", true);
            $("#pdc_bank_id").prop("disabled", false);
            $('#pdc_bank_id').attr("required", true);
            $('.pay_type').prop('checked', false)
             $('.type_div').show();
            $("#pdc_bounce_cheque").val('');
            $("#pdc_bounce_div").hide();
            $("#lbl_cheque").html("Transaction No :");
            $("#cheque_no").attr("placeholder","Transaction no");

             @if(isset($chequeReceiptNo))   
                $("#receipts_generation_receipt_no").val("{{$chequeReceiptNo}}");   
            @endif  
            @if(!isset($depositReceiptInfo) && $isYearChequeCorrect == false)   
                alert("Current Year Is Not Match With the Sequence Year");  
            @elseif(!isset($depositReceiptInfo) && $isYearCashCorrect == false) 
                alert("Current Year Is Not Match With the Sequence Year");  
            @endif
    }
         
});
$(document).on('click','.pay_type',function(){  
    
    var cash_type = $(this).val();
    
    if(cash_type == 1){ // Normal
            $("#pdc_bounce_cheque").removeAttr('required');
            $("#pdc_bounce_div").hide();
    }
    else if(cash_type == 2){ // Bounce

        var contractId = $("#tenant_contract_no").val();
        $("#pdc_bounce_cheque").attr('required','required');
        $("#pdc_bounce_cheque-error").hide();
        $("#pdc_bounce_cheque").val('');
        if(contractId ==''){ 
            
            $("#pdc_bounce_cheque").html('<option value=""> Select the Agreement No</option>')
        }  
        else{
   
            $.ajax({
                method: "POST",
                    url: "{{route('pdcNoListWithContractNo')}}",
                    data: { contractId: contractId,
                  "_token" : $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        var parsed_data = JSON.parse(data);
                        $('#pdc_bounce_cheque').html(parsed_data.option_html);
                    } 
            });
            
        }
        $("#pdc_bounce_div").show();
    }
        
});

$(document).on('click','.scan',function(){       

    $.ajax({
      method: 'POST', // Type of response and matches what we said in the route
      url: "{{route('keyScanForPaymentReceipt')}}", // This is the url we gave in the route
      data: {"_token": "{{ csrf_token() }}"}, // a JSON object to send back
      success: function(response){ // What to do if we succeed
          $("#myModal").html(response); 
      },
    });
    return true; 
         
});

/***************************************************************************************/
$(document).ready(function() {
   $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    }); 


    
/***************************************************************************************/
    $(document).on('blur','#receipts_generation_amt',function(){  
      
            var amt = $('#receipts_generation_amt').val();
            $("#dr_amt_first").val(amt);
           // $("#receipts_generation_remark").val("Deposit "+$("#unit_name option:selected").text() +' , '+$("#building_name").val()+' @ '+$("#receipts_generation_amt").val()+'/-')
			$("#receipts_generation_remark").val("DEPOSIT FOR UNIT NO. "+$("#unit_name option:selected").text() +' '+$("#building_name").val());
            $("#cr_amt_second").val(amt);
            $("#total_dr").val(amt);
            $("#total_cr").val(amt);
    });
});
function ClearInputDate(att_el){
 
    $('#mob_no').val('');
    $('#resident_card_id').val('');      
    $("#receipts_generation_amt").val(''); 
    $('#dr_amt_first').val('');
    $('#cr_amt_first').val('');
    $('#dr_amt_second').val('');
    $('#cr_amt_second').val('');
    $('#total_dr').val('');
    $('#total_cr').val('');
    $('#tenant_contract_date').val('');
    $('#tenantContract').val(''); 
    $('#tenant_contract_effective_date').val(''); 
    $('#tenant_contract_valid_to_date').val(''); 
    if(att_el != 'building_name')
        $('#building_name').val('');

    $('#building_dim').val(''); 
    $('#building_dim').val('');
    $('#building_code').val(''); 
    $('#unit_name').val(''); 
    $('#unit_code').val(''); 
    $('#tenant_name').val(''); 
    $('#tenant_code').val(''); 
    $('#tenant_contract_no').html('<option value="">Enter Mobile No Or Resident ID </option>'); 
    $('#unit_id').html('');
    $('#unit_id').html('<input type="text" name="unit_name" id="unit_name" class="form-control">'); 
    $('#bank_id').prop('selectedIndex',0);
	  $('#pdc_bank_id').prop('selectedIndex',0);
    $('#cheque_no').val('');
    $('#receipts_generation_remark').val('');
    $("#cheque_no").prop("disabled", true);  
    $('.pay_type').prop('checked', false)
    $('.payment_method').prop('checked', false);
    $('#ax_division').prop('selectedIndex',0);
    return true;
}
</script>
@endsection
