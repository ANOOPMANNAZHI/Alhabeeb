 <div class="panel-body">
  <div class="dataSearchBox ">
    <form autocomplete="off" action="{{route( 'rentalIncomePosting')}}" method="post" id="search_form" class="form-horizontal"  data-toggle="validator">
      @csrf
      <div class="row">
       <div class="col-sm-6">
        <div class="form-group">
          <label for="fromdate">Date From</label>
          <div class="p-relative">
           <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
           <input  type="date" class="form-control" name="fromdate" id="fromdate" placeholder="Enter Value"  value="{{isset($request->fromdate)?$request->fromdate :''}}">
           
         </div>
         <div class="error1" style="display:none;"><p style="font-size: 11px;">Date From should Not Be Empty</p></div>
       </div>
       
     </div>
     
     <div class="col-sm-6">
      <div class="form-group">
        <label for="todate">Date To</label>
        <div class="p-relative">
         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
         <input  type="date" class="form-control" name="todate" id="todate" placeholder="Enter Value"  value="{{isset($request->todate)?$request->todate :''}}">
         
       </div>
       <div class="error" style="display:none;"><p style="font-size: 11px;">Date To should Not Be Empty</p></div>
     </div>
     
   </div>
   <!-- @php
   $newArray = array();
   $reqBuilding = isset($request->building_id)?$request->building_id :$newArray;
   @endphp -->
   <!-- <div class="col-sm-4">
    <div class="form-group">
      <label for="fieldName">Buildings:</label>
      <div class="p-relative">
        <input type="button" id="select_all" class="select_all" name="select_all"  value=" --- Select All Buildings --- " >
        <select class="form-control"  name="building_id[]" multiple id="select-meal-type">
          @foreach($buildings as $building)
          <option {{(in_array($building->id,$reqBuilding))? 'selected' : ''}} value="{{$building->id}}">{{$building->building_name }} - {{ $building->building_code}}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div> -->


  <div class="col-sm-12">
    <div class="form-group autocomplete-cls">
      <label>Building Name</label>

      @php
      $newArray = array();
      $reqBuilding = isset($request->building_id)?$request->building_id :$newArray;
      @endphp


      <div class="p-relative">
        <i class="fa fa fa-building-o icn-add" aria-hidden="true"></i>
        <select id="building_id"  name="building_id[]" class="tokenize-remote-demo1 form-group" multiple> 

         @foreach($buildings as $building)
         <option {{(in_array($building->id,$reqBuilding))? 'selected' : ''}} value="{{$building->id}}">{{$building->building_name }} - {{ $building->building_code}}</option>
         @endforeach

       </select>
       <label class="error" for="tokenize_demo" id="tokenize_demo-error"></label>
     </div>
   </div>
 </div> 
 <div class="col-sm-2">
  <div class="dataSearchLabel w-100"></div>
  <button type="submit" class="btn btn-primary margin rental_search" id="submitBtn">Search</button>
</div>
</div>
</form>
</div>
</div>
