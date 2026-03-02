 

   <div class="panel-body " >
            <div class="dataSearchBox "><!-- @yield('search_url') -->
                    <form autocomplete="off"  action="@yield('search_url')" method="GET" id="leade_search" class="form-horizontal"  da ta-toggle="validator">
                       
                            <div class="row">

                              <div class="col-sm-12">

                              <div id="search_form" class="">  
                               

                              @if(old('fieldName', null) != null)

                               @php
                                $size = count(old('fieldName'));
                               @endphp

                               @for($i = 0; $i < $size ; $i++) 

                               <div class="row">                           
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fieldName">Field</label>
                                        <div class="p-relative">
                                         <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i>
                                        <select class="form-control fieldName" required name="fieldName[]">
                                          <option value="">Select </option>
                                          @foreach($enquiry_fields as $key=>$val)
                                            <option  {{ (old('fieldName')[$i] == $key)? 'selected' : '' }}   value="{{$key}}">{{$val}}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                </div>


                               <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="fieldValue"> Operation</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-center icn-add" aria-hidden="true"></i>
                                        <select class="form-control operation" required name="operation[]">
                                        <option  value="">Select </option>  
                                         @foreach($operations as $operation_key=>$operation_val)
                                          <option  {{ (old('operation')[$i] == $operation_key)? 'selected' : '' }}   value="{{$operation_key}}">{{$operation_val}}</option>
                                         @endforeach                                          
                                        </select>  
                                        </div>                                      
                                    </div>
                                </div>
								@php
								if(old('fieldName')[$i] == 'sales_move_in_date' || old('fieldName')[$i] =='created_at' )
								  $inputType = "date";
								else
									$inputType = "text";
								@endphp
		
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fieldValue"> Value</label>
                                       <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input autocomplete="off" required type="{{$inputType}}" class="form-control fieldValue" name="fieldValue[]" placeholder="Enter Value" value="{{old('fieldValue')[$i]}}">
                                      </div>
                                    </div>
                                </div>

                                <div class="col-sm-2">                                    
                                    
                                     <div class="form-group">
                                        <label for="fieldValue"> AND/OR</label>
                                         <div class="p-relative">
                                         <i class="fa fa-sort icn-add" aria-hidden="true"></i>
                                        <select class="form-control and_search logic" name="logic[]">                                         
                                        <option {{ (old('logic')[$i] == 'and')? 'selected' : '' }}  value="and">AND</option>
                                        <option {{ (old('logic')[$i] == 'or')? 'selected' : '' }}  value="or">OR</option>
                                        </select>
                                        </div>                                     
                                    </div> 
                                    
                                </div>  
                                
                                
                               <div class="col-sm-1">   
                                 @if($i > 0)
                                       <div class="dataSearchLabel w-100 margin"></div>
                                       <button type="button"  class="btn btn-primary remove_search margin">Remove</button>
                                 @else                                  
                                    <div class="dataSearchLabel w-100"></div>
                                    <button type="button" class="btn btn-primary add_search margin">ADD </button> 
                                 @endif
                               </div>                             


                                <div class="w-100"></div>
                                </div>  

                                @endfor 

                               @else 
                               




                              <div class="row">                           
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fieldName">Field</label>
                                        <select class="form-control fieldName" required name="fieldName[]">
                                          <option value="">Select </option>
                                          @foreach($enquiry_fields as $key=>$val)
                                            <option  value="{{$key}}">{{$val}}</option>
                                          @endforeach
                                        </select>
                                    </div>
                                </div>


                               <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="fieldValue"> Operation</label>
                                        <select class="form-control operation" required name="operation[]">
                                         <option value="">Select </option>   
                                        @foreach($operations as $operation_key=>$operation_val)
                                            <option value="{{$operation_key}}">{{$operation_val}}</option>
                                          @endforeach    
                                        </select>                                        
                                    </div>
                                </div>


                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fieldValue"> Value</label>
                                        <input autocomplete="off" required type="text" class="form-control fieldValue" name="fieldValue[]"  placeholder="Enter Value" value="">
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    
                                     <div class="form-group">
                                        <label for="fieldValue"> AND/OR</label>
                                        <select class="form-control and_search logic" name="logic[]">                                       
                                        <option value="and">AND</option>
                                        <option value="or">OR</option>
                                        </select>                                        
                                    </div>
                                </div>
                                
                                
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 "></div>
                                    <button type="button" class="btn btn-primary add_search margin">ADD </button>
                                </div>
                                
                                
                                


                                <div class="w-100"></div>
                                </div>
                                

                                @endif
                                </div>                             

                                </div>    
 

                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100"></div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 "></div>
                                    <a href="@yield('search_reset')" class="btn btn-primary">Reset</a>
                                </div>
                            </div>
                            
                    </form>
                </div>
        </div>

