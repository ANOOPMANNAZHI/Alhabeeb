 <div class="panel-body">
		       
                    <form autocomplete="off" action="{{route( 'tenantPdcBounce')}}" method="GET" id="search_form" class="form-horizontal"  data-toggle="validator">
                        <div class="dataSearchBox ">
                            <div class="row">
                                 <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fromdate">Start Cheque Date</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
            
                                        <input  type="date" class="form-control check_dt" name="fromdate" id="fromdate" placeholder="Enter Value"  value="{{isset($request->fromdate)?$request->fromdate:''}}" >
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="todate">End Cheque Date </label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="date" class="form-control check_dt" name="todate" id="todate" placeholder="Enter Value" value="{{isset($request->todate)?$request->todate:''}}" >
                                    </div>
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-primary margin pdc_search top-align">Search</button>
                                    </div>
                                </div>  

                                </div>

                            </div>



                        <div class="row"> 
                            <div class="col-sm-6"></div>
                             <div class="col-xs-2 col-center-block"><strong>OR</strong> </div>           
                            <div class="col-sm-6"></div>
                         </div>

                          <div class="dataSearchBox ">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label for="fromdate">From Cheque No</label>
                                    <div class="p-relative">
                                     <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="number" class="form-control check_no" id="fromcheque_no" name="fromcheque_no"  value="{{isset($request->fromcheque_no)?$request->fromcheque_no:''}}" placeholder="Enter Value">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="todate">To Cheque No </label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="number" class="form-control check_no" name="tocheque_no" id="tocheque_no" placeholder="Enter Value" value="{{isset($request->tocheque_no)?$request->tocheque_no:''}}" >
                                    </div>
                                    </div>
                            </div>

                            <div class="col-sm-2">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-primary margin pdc_search top-align">Search</button>
                                    </div>
                                </div>  


                        </div>
                    </div>
                        <div class="row">
                            <div class="col-sm-6"></div>
                            <div class="col-xs-2 col-center-block"><strong>OR</strong> </div>
                            <div class="col-sm-6"></div>
                        </div>

                         <div class="dataSearchBox ">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label for="pdc_check_no">Bank Name</label>
                            <select class="form-control" name="bank_id" id="bank_id">
                               <option value="">Select </option>
                            @foreach($banks as $bank)
                            <option  value="{{$bank->id}}" {{isset($request->bank_id)?($request->bank_id==$bank->id)?'SELECTED':'':''}}>{{$bank->bank_name }} - {{ $bank->bank_code}}</option>
                            @endforeach
                             </select>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                 </div>
                                 <div class="col-sm-2">
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-primary margin pdc_search top-align">Search</button>
                                    </div>
                                </div>  
                           
                         </div>
                     </div>


                    <div class="col-sm-3"></div>
                       
                    </form>
                            
                </div>
