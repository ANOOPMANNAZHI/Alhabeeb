 <div class="panel-body">
  <div class="dataSearchBox ">
    <form autocomplete="off" action="{{route( 'massMailList')}}" method="post" id="search_form" class="form-horizontal"  data-toggle="validator">
        @csrf
        <div class="row">
           <div class="col-sm-8">
            <div class="form-group">
                <label for="email">To</label>
                <div class="p-relative">
                   <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                   <input  type="text" class="form-control" name="email" id="email" placeholder="Enter Email Address" required  value="{{isset($request->email)?$request->email :''}}">
                   <input  type="hidden" class="form-control" name="user_id" id="user_id" placeholder="Enter Email id" required  value="{{isset($request->user_id)?$request->user_id :''}}">
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
