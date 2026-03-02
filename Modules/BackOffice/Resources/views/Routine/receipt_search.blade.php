 <div class="panel-body">
  <div class="dataSearchBox ">
    <form autocomplete="off" action="{{route( 'tenantReceiptPosting')}}" method="post" id="search_form" class="form-horizontal"  data-toggle="validator">
        @csrf
        <div class="row">
           <div class="col-sm-5">
            <div class="form-group">
                <label for="fromdate">Date From</label>
                <div class="p-relative">
                   <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                   <input  type="date" class="form-control" name="fromdate" id="fromdate" placeholder="Enter Value" required value="{{isset($request->fromdate)?$request->fromdate :''}}">
               </div>
           </div>
       </div>
       <div class="col-sm-5">
        <div class="form-group">
            <label for="todate">Date To</label>
            <div class="p-relative">
               <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
               <input  type="date" class="form-control" name="todate" id="todate" placeholder="Enter Value" required  value="{{isset($request->todate)?$request->todate :''}}">
           </div>
       </div>
   </div>
   <div class="col-sm-1">
    <div class="dataSearchLabel w-100"></div>
    <button type="submit" class="btn btn-primary margin">Search</button>
</div>
</div>
</form>
</div>
</div>
