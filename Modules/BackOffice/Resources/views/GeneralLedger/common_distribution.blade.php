               @if(isset($generalLedger) && count($generalLedger->generalLedgerDim)>0)

                  @foreach($generalLedger->generalLedgerDim as  $key=>$dimDetails)

                    <tr  id="first_row{{$key+1}}" >
                       <td>
                        <a href='#'  class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i>
                       </a>
                      </td>
                      <td>
			
                        <input type="text" class="account_code type"  name="account_code[]" value="{{isset($dimDetails->account_id)?$dimDetails->accountCode->acc_code_val.'-'.$dimDetails->accountCode->acc_code_desc:''}}" id="" >
                        <input type="hidden" class="account_id type"  name="account_id[]" value="{{isset($dimDetails->account_id)?$dimDetails->account_id:''}}" id="" >
                      </td>
                      <td>

                        <input type="text" class="building_name type"  name="building_name[]" value="{{isset($dimDetails->building_id)?$dimDetails->building->building_name:''}}" id="" >
                        <input type="hidden" class="building_id"  name="building_id[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:''}}" id="" >
                      </td>

                      <td>
						 
                        <select  name="unit_id[]" class="unit_id">
							
							@if(count($dimDetails->unitByBuilding)>0)
							<option value=''>Select</option>
							@foreach($dimDetails->unitByBuilding as $unit)
								<option value="{{$unit->id}}" {{ ($dimDetails->unit_id == $unit->id)? 'selected': '' }} >{{$unit->unit_code}}</option>
							@endforeach
							@else
								<option value=''>No Unit</option>
							@endif
                        </select>
                        <!--<input type="text" class="unit_id type"  name="unit_id[]" value="{{isset($dimDetails->unit_id)?$dimDetails->unit_id:''}}" id="" > -->
                      </td>                    

                      <td>
                        <input type="text" class=" txt_box "  name="jv_desc[]" value="{{$dimDetails->jv_desc}}">
                      </td>
                    
                     
                      <td>
                        <input type="text" class="text-right debit_amt allownumericwithdecimal" data_name="debit_amt"  name="debit_amt[]" value="{{isset($dimDetails->debit_amt)?numberFormat($dimDetails->debit_amt):0}}" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"

>
                      </td>

                      <td>
                        <input type="text" class="text-right credit_amt allownumericwithdecimal" data_name="credit_amt" name="credit_amt[]" value="{{isset($dimDetails->credit_amt)?numberFormat($dimDetails->credit_amt):0}}" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"

>
                      </td> 

                      <td>
                       <select required name="recovery[]" class=" recovery"> 
                          <option {{ ($dimDetails->recovery == 1)? 'selected': '' }}  value="1">Yes</option>
                          <option {{ ($dimDetails->recovery == 0)? 'selected': '' }} value="0">No</option>                          
                        </select>
                      </td>                      

                      <td>
                        <select required name="dim1[]" class="  dim1">
                          @foreach($dim1 as $dim1_val)
                          <option  {{ ($dimDetails->dim1able_id == $dim1_val->id)? 'selected': '' }}  value="{{$dim1_val->id}}">{{$dim1_val->dim_value}}</option>
                          @endforeach
                        </select>
                      </td>

                       <td>
                        <input type="text" class="dim2_input"  name="dim2_input[]" value="{{isset($dimDetails->building_id)?$dimDetails->building->building_name:''}}" id="" >
                        <input type="hidden" class="dim2"  name="dim2[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:''}}" >
            
                      </td>                     
                    </tr>


                  @endforeach  
               
               @else
               
				 @if(!empty($accountCodes)) 
					 @foreach($accountCodes as $key=>$acc_code)
					 <tr id="first_row{{$key+1}}">
                       <td>
                        <a href='#'  class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i>
                       </a>
                      </td>
                      <td>
						   
                        <input type="text" class="account_code type"  name="account_code[]" value="{{isset($dimDetails->account_id)?$dimDetails->account_id:$acc_code->acc_code_val}}" id="" >
                        <input type="hidden" class="account_id type"  name="account_id[]" value="{{isset($dimDetails->account_id)?$dimDetails->account_id:$acc_code->id}}" id="" >
                      </td>
           

                      <td>
						
                         <input type="text" class="building_name type"  name="building_name[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:$buildings->building_name}}" id="" >
                         <input type="hidden" class="building_id"  name="building_id[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:$buildings->id}}" id="" >
                      </td>

                      <td>
                        <select  name="unit_id[]" class="unit_id">
                          <option value="">Select Unit</option>
                          @foreach($units as $unit)
							<option value="{{$unit->id}}">{{$unit->unit_code}}</option>
                          @endforeach 
                        </select>
                      </td>                    

                      <td>
                        <input type="text" class=" txt_box "  name="jv_desc[]" value="">
                      </td>
                    
                     
                      <td>
                        <input type="text" class="debit_amt allownumericwithdecimal" data_name="debit_amt"  name="debit_amt[]" value="0" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
                      </td>

                      <td>
                        <input type="text" class="credit_amt allownumericwithdecimal" data_name="credit_amt" name="credit_amt[]" value="0" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"
>
                      </td> 

                      <td>
                       <select required name="recovery[]" class=" recovery"> 
                          <option value="1">Yes</option>
                          <option selected value="0">No</option>                          
                        </select>
                      </td>                      

                      <td>
                        <select required name="dim1[]" class="  dim1">
                          @foreach($dim1 as $dim1_val)
                          <option value="{{$dim1_val->id}}">{{$dim1_val->dim_value}}</option>
                          @endforeach
                        </select>
                      </td>

                       <td>
                         <input type="text" class="dim2_input"  name="dim2_input[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:$buildings->building_name}}" id="" >
                         <input type="hidden" class="dim2"  name="dim2[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:$buildings->id}}" >
               
                      </td>                     
                    </tr>
					 @endforeach 
					@else
                  <tr id="first_row1">
                       <td>
                       <a href='#'  class="remove_details"><i class="fa fa-minus" aria-hidden="true"></i>
                       </a>
                      </td>
                      <td>
						   
                        <input type="text" class="account_code type"  name="account_code[]" value="{{isset($dimDetails->account_id)?$dimDetails->account_id:''}}" id="" >
                        <input type="hidden" class="account_id type"  name="account_id[]" value="{{isset($dimDetails->account_id)?$dimDetails->account_id:''}}" id="" >
                      </td>
                      <td>
            
                         <input type="text" class="building_name type"  name="building_name[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:(isset($buildings->building_name)?$buildings->building_name:'')}}" id="" {{((isset($dimDetails->building_id) || isset($buildings->building_name))?'readonly':'') }}>
                         <input type="hidden" class="building_id"  name="building_id[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:(isset($buildings->id)?$buildings->id:'')}}" id="" >
                      </td>

                      <td>
                        <select  name="unit_id[]" class="unit_id">
                          @if(isset($units))
                          <option value="">Select Unit</option>
                          @foreach($units as $unit)
                            <option value="{{$unit->id}}">{{$unit->unit_code}}</option>
                          @endforeach 
                          @endif
                        </select>
                      </td>                         

                      <td>
                        <input type="text" class=" txt_box "  name="jv_desc[]" value="">
                      </td>
                    
                     
                      <td>
                        <input type="text" class="debit_amt allownumericwithdecimal" data_name="debit_amt"  name="debit_amt[]" value="0" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"

>
                      </td>

                      <td>
                        <input type="text" class="credit_amt allownumericwithdecimal" data_name="credit_amt" name="credit_amt[]" value="0" onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"

>
                      </td> 

                      <td>
                       <select required name="recovery[]" class=" recovery"> 
                          <option value="1">Yes</option>
                          <option selected value="0">No</option>                          
                        </select>
                      </td>                      
                       <td>
                         <input type="text" class="dim2_input"  name="dim2_input[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:(isset($buildings->building_name)?$buildings->building_name:'')}}" id="" readonly" id="" readonly>
                         <input type="hidden" class="dim2"  name="dim2[]" value="{{isset($dimDetails->building_id)?$dimDetails->building_id:(isset($buildings->id)?$buildings->id:'')}}" >
               
                      </td> 
                      <td>

                        <select required name="dim1[]" class="dim1">
                          @if(isset($dimDetails->ax_division))
                          @foreach($dim1 as $dim1_val)
                            @if($dimDetails->ax_division== $dim1_val->dim_code)
                            <option value="{{$dim1_val->dim_code}}">{{$dim1_val->dim_value}}</option>
                            @endif
                          @endforeach
                          @else
                          @foreach($dim1 as $dim1_val)
                            
                            <option value="{{$dim1_val->dim_code}}">{{$dim1_val->dim_value}}</option>
                          
                          @endforeach  
                          @endif
                        </select>
                      </td>

                                          
                    </tr>
                    
                   @endif
                    @endisset
