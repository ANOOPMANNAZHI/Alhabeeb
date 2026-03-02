 <div class="panel-body">
		        <div class="dataSearchBox ">
                    <form autocomplete="off" action="" method="GET" id="leade_search" class="form-horizontal"  data-toggle="validator">
                        
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fieldName">Field</label>
                                        <div class="p-relative">
                                         <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i>
                                        
                                        <select class="form-control" required name="fieldName">
                                          <option value="">Select </option>
                                          @foreach($fields as $key=>$val)
                                            <option {{ isset($request->fieldName)?((old('fieldName',$request->fieldName) == $key)? 'selected': ''):'' }} value="{{$key}}">{{$val}}</option>
                                          @endforeach
                                        </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fieldValue"> Value</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input required type="text" class="form-control" name="fieldValue" id="fieldValue" placeholder="Enter Value" value="{{isset($request->fieldName)?(old('fieldValue',$request->fieldValue)):''}}">
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100"></div>
                                    <button type="submit" class="btn btn-primary margin">Search</button>
                                </div>
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 margin"></div>
                                    <a href="{{url()->current()}}" class="btn btn-primary margin">Reset</a>
                                </div>
                            </div>
                    </form>
                </div>
		    </div>
