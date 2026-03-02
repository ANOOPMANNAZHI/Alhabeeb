                         <div class="row">
                            <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fieldName">Field</label>
                                        <select class="form-control fieldName" required name="fieldName[]">
                                          <option value="">Select </option>
                                          @foreach($enquiry_fields as $key=>$val)
                                            <option value="{{$key}}">{{$val}}</option>
                                          @endforeach
                                        </select>
                                    </div>
                                </div>


                               <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="fieldValue"> Operation</label>
                                        <select class="form-control" required name="operation[]">
                                         <option value="">Select </option>   
                                          @foreach($operations as $operation_key=>$operation_val)
                                          <option  value="{{$operation_key}}">{{$operation_val}}</option>
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
                                        <select class="form-control and_search"  name="logic[]">                                         
                                        <option value="and">AND</option>
                                        <option value="or">OR</option>
                                        </select>                             
                                    </div>  
                                   
                                </div>
                                
                                <div class="col-sm-1">
									  <div class="dataSearchLabel w-100 "></div>
								 <button type="button"  class="btn btn-primary remove_search margin ">Remove</button> 	
									 </div>


                                <div class="w-100"></div>

                           </div>
