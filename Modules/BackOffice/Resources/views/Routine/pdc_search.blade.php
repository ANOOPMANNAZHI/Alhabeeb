 <div class="panel-body">
		        <div class="dataSearchBox ">
                    <form autocomplete="off" action="{{route( 'pdcPosting')}}" method="post" id="search_form" class="form-horizontal"  data-toggle="validator">
                        @csrf
                            <div class="row">
                            <div class="col-sm-1"></div>
                                 <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="fromdate">Date From</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="date" class="form-control" name="fromdate" id="fromdate" value="{{isset($request->fromdate)?$request->fromdate :''}}" placeholder="Enter Value" >
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="todate">Date To</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="date" class="form-control" name="todate" id="todate" value="{{isset($request->todate)?$request->todate :''}}" placeholder="Enter" >
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="pdc_check_no">Cheque No</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input  type="text" class="form-control" name="pdc_check_no" id="pdc_check_no" value="{{isset($request->pdc_check_no)?$request->pdc_check_no:''}}"  placeholder="Enter Value">
                                    </div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100"></div>
                                    <button type="submit" class="btn btn-primary margin pdc_search">Search</button>
                                </div>
                                <div class="col-sm-1"></div>
                                </div>
                                 
                                </form>
                            </div>
                </div>
