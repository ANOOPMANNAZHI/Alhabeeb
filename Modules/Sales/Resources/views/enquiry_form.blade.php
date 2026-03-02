<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">

<style>
  body {counter-reset:section 0 sec 0;}
  .counters:before
  {
    counter-increment:section;
    content:counter(section);
  }
  .countn:before
  {
    counter-increment:sec;
    content:counter(sec);
  }
  #grid, #drop {
    border: 1px solid #e1dede;
    width: 42%;
    min-height: 20px;
    list-style-type: none;
    margin: 0;
    padding: 5px 0 0 0;
    float: left;
    min-height: 200px;
    /* margin-right: 10px;*/
    cursor:pointer;
    position: relative;
    overflow-x: hidden;
    max-height: 200px
  }
  #grid li, #drop li {

    padding: 5px 10px;
    font-size: 14px;
    width: 100%;
  }
  #grid li:hover, #drop li:hover {
    background-color: #32ab56;
    color: #fff;
  }


  #grid1, #drop1 {
    border:  1px solid #e1dede;
    width: 42%;
    min-height: 20px;
    list-style-type: none;
    margin: 0;
    padding: 5px 0 0 0;
    float: left;
    min-height: 200px;
    /*margin-right: 10px;*/
    cursor:pointer;
    position: relative;
    overflow-x: hidden;
    max-height: 200px
  }
  #grid1 li, #drop1 li {

    padding: 5px 10px;
    font-size: 14px;
    width: 100%;
  }
  #grid1 li:hover, #drop1 li:hover {
    background-color: #32ab56;
    color: #fff;
  }
  .token-search {
    margin-left: 10px !important;
  }

</style>

<div class="row">  

  <!-- activities -->
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <header class="panel-heading panel-heading-gray custom-tab ">
        @if(isset($type))
        
        <ul class="nav nav-tabs">
          @if($type==1)
          <li class="nav-item">
            <a href="#tenant" data-toggle="tab" class="active" >Tenant</a>
          </li>
          @elseif($type==2)	
          <li class="nav-item">
            <a href="#landlord" data-toggle="tab" class="active">Landlord</a>
          </li>
          @elseif($type==3)	
          <li class="nav-item">
            <a href="#complaint" data-toggle="tab" class="active">Complaint</a>
          </li>	
          @endif
        </ul>
        
        @else
        
        <ul class="nav nav-tabs">
          @if((isset($enquiry) && $enquiry->sales_type == 1) || !isset($enquiry) && !isset($complaintEnquiry)) 
          <li class="nav-item"><a href="#tenant" data-toggle="tab" class="{{(old('sales_type') <= 1)? 'active': ''}} tenantone" >Tenant</a>
          </li><!-- {{(old('sales_type') <= 1)? 'active': ''}} -->
          @endif

          @if((isset($enquiry) && $enquiry->sales_type == 2) || !isset($enquiry)&& !isset($complaintEnquiry)) 
          <li class="nav-item"><a href="#landlord" data-toggle="tab" class="{{(old('sales_type',isset($enquiry)? $enquiry->sales_type : ''  ) == 2)? 'active': ''}} landlordtwo">Landlord</a>
          </li>
          @endif 
          @if(isset($complaintEnquiry) || !isset($enquiry)&& !isset($complaintEnquiry))
          <li class="nav-item"><a href="#maintenance" data-toggle="tab" class="{{(old('sales_type',isset($complaintEnquiry)? $complaintEnquiry: ''  ) != '')? 'active': ''}} maintenancethree">Maintenance</a>
          </li>  
          @endif                                     
        </ul>
        @endif   
      </header>
      <div class="panel-body">
        <div class="tab-content">                                     
          @if((isset($enquiry) && $enquiry->sales_type == 1 && empty($type)) || (!isset($enquiry) && !isset($complaintEnquiry) && empty($type)) || $type==1 ) 
          <div class="tab-pane  @if(isset($type)) {{($type==1)?'active':''}} @else {{(old('sales_type') <= 1)? 'active': ''}}" @endif id="tenant">
            <form method="post"  id="tenant-form" class="submitClass" autocomplete="off" action="{{isset($enquiry)? route( 'enquiry.update',$enquiry->id) : route( 'enquiry.store')}}">
              @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
              <input type="hidden" name="sales_type" value="1">
              <div class="clearfix"></div>
              <div class="dataSearchBox">
                <div class="row">

                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="sales_enquiry_no">Enquiry No</label>
                      <div class="p-relative">
                       <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                       <input type="text" name="sales_enquiry_no" disabled=""  class="form-control" id="sales_enquiry_no" value="{{ isset($enquiry)? $enquiry->sales_enquiry_no : $nextTenantCode}}">
                     </div>
                   </div>
                 </div>
                 <div class="col-sm-6">
                  <div class="form-group">
                    <label for="simpleFormEmail">Enquiry Date</label>
                    <div class="p-relative">
                     <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                     <input type="text" disabled="" class="form-control" id="simpleFormEmail" value="{{ isset($enquiry)? $enquiry->created_at->format('d/m/Y') : today()->format('d/m/Y') }}" placeholder="">
                   </div>
                 </div>
               </div>
               <div class="w-100"></div>
               <div class="col-sm-4">
                <div class="form-group">
                  <label for="sales_mobile_no">Mobile No<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>

                   <input required type="text" class="form-control mob_validation_13" id="sales_mobile_no" onkeypress="return isNumber(event)" name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no :'')}}"  placeholder="Enter Mobile No">
                 </div>
               </div>
             </div>

             <div class="col-sm-4">
              <div class="form-group">
                <label for="alternative_no">Alternative Mobile No</label>
                <div class="p-relative">
                 <i class="fa fa-sort-amount-desc icn-add" aria-hidden="true"></i>
                 <input type="text" class="form-control mob_validation" id="alternative_no"  name="alternative_no" 
                 value="{{old('alternative_no',isset($enquiry->alternative_no)?trim($enquiry->alternative_no):'00968')}}"  placeholder="Enter Alternative Mobile No" onkeypress="return isNumber(event)">
               </div>
             </div>
           </div>
           
           <div class="col-sm-4">
            <div class="form-group">
              <label for="sales_enquiry_name">Customer Name<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
               <input required pattern="[A-Za-z\s]+" type="text" name="sales_enquiry_name" class="form-control" id="sales_enquiry_name"  value="{{old('sales_enquiry_name',isset($enquiry)? $enquiry->sales_enquiry_name : ''   ) }}" placeholder="Enter Customer Name">
             </div>
           </div>
         </div>

         <div class="w-100"></div>

         <div class="col-sm-6">
          <div class="form-group">
            <label>Region<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-globe icn-add" aria-hidden="true"></i>
             <select required name="sales_region"  class="form-control sales_region" id="sales_region">
              <option value="">Select</option>
              <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 1)? 'selected':'selected' }}   value="1">Local</option>
              <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : '') == 2)? 'selected':'' }}   value="2">International</option>
            </select>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="sales_email">Customer Email</label>
          <div class="p-relative">
           <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
           <input type="email"  name="sales_email" class="form-control" id="sales_email" value="{{old('sales_email',isset($enquiry)? $enquiry->sales_email : '')}}" placeholder="Enter Customer Email">
         </div>
       </div>
     </div>
     <div class="w-100"></div>
     
     
			<!--  <div class="w-100"></div>
			<div class="col-sm-6">
			        <div class="form-group">
			            <label for="sales_contact_address">Customer Address<small class="textRed">*</small></label>
			            <textarea  required name="sales_contact_address" class="form-control" id="sales_contact_address"  placeholder="Contact Address" >{{old('sales_contact_address',isset($enquiry)? $enquiry->sales_contact_address : '')}}</textarea>            
			        </div>
           </div> -->                                                 
           <div class="w-100"></div>
           <div class="col-sm-6">
            <div class="form-group">
              <label class="col no-padding" for="sales_unit_type_id">Unit Type<small class="textRed">*</small></label>         
              
              @php  
              if(old('unit_type_id')){
              
              if(!is_array(old('unit_type_id')))
              $unit_types = explode(',',old('unit_type_id'));

              $source_unitTypes = $unitTypes->whereNotIn('id',old('unit_type_id'));
              $destination_unitTypes = $unitTypes->whereIn('id',old('unit_type_id'));

            }elseif(isset($enquiry)){           
            $units_val = $enquiry->unitTypes()->pluck('unit_type_id');    
            $units_val = (array)array_flatten($units_val); 
            $source_unitTypes = $unitTypes->whereNotIn('id',$units_val);
            $destination_unitTypes = $unitTypes->whereIn('id',$units_val);
          }else{
          $source_unitTypes = $unitTypes; 
        }
        @endphp    
        
        <ul id="grid" class="connectedSortable">
         @foreach($source_unitTypes as $units)
         <li id="{{$units->id}}" class="list_item draggable">{{$units->unit_types_name}}</li>
         @endforeach
         
       </ul> 
       <div class="sort_arrow-l ui-state-disabled">  
         <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
       </div>
       <ul id="drop" class="connectedSortable">
         <li class="ui-state-disabled">(Drag And Drop Unit Type)</li>
         <!-- <li class="list_item"></li> -->
         @if(isset($destination_unitTypes))
         @foreach($destination_unitTypes as $units)
         <li id="{{$units->id}}" class="list_item draggable">{{$units->unit_types_name}}</li>
         @endforeach
         @endif 
         
       </ul>
       <div class="clr"></div>
       <input type="hidden" value="{{ isset($destination_unitTypes)?  $destination_unitTypes->implode('id',',') : ''}}" required id="sales_unit_type_id" name="unit_type_id" class="unittype">
     </div>
   </div>




   <div class="col-sm-6">
    <div class="form-group">
      <label  class="col no-padding">Price Range<small class="textRed">*</small></label> 
      @php 
      if(old('price_range_id')){
      
      $source_priceRanges = $priceRanges->whereNotIn('id',old('price_range_id'));
      $destination_priceRanges = $priceRanges->whereIn('id',old('price_range_id'));

    }elseif(isset($enquiry)){           
    $price_range_val = $enquiry->priceRanges()->pluck('price_range_id');  
    //dd($enquiry->priceRanges()->pluck('price_range_id'));
    $price_range_val = (array)array_flatten($price_range_val);
    $source_priceRanges = $priceRanges->whereNotIn('id',$price_range_val);
    $destination_priceRanges = $priceRanges->whereIn('id',$price_range_val);
  }else{
  $source_priceRanges = $priceRanges; 
}
@endphp
<ul id="grid1" class="connectedPriceRange">
 @foreach($source_priceRanges as $priceRange)
 <li id="{{$priceRange->id}}" class="list_item price_draggable">{{$priceRange->price_ranges_name}}</li>
 @endforeach
              <!--  <div class="sort_arrow-l">  
			        <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
            </div>  -->
          </ul>
          <div class="sort_arrow-l ui-state-disabled">  
           <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
         </div>
         <ul id="drop1" class="connectedPriceRange">
          <li class="ui-state-disabled">(Drag And Drop Price Range)</li>
          <!-- <li class="list_item"></li> -->
          @if(isset($destination_priceRanges))
          @foreach($destination_priceRanges as $priceRange)
          <li id="{{$priceRange->id}}" class="list_item price_draggable">{{$priceRange->price_ranges_name}}</li>
          @endforeach
          @endif
        </ul>
        <div class="clr"></div>
        <input type="hidden" value="{{ isset($destination_priceRanges)?  $destination_priceRanges->implode('id',',') : ''}}" required name="price_range_id" class="pricerange">
      </div>
    </div>
    
    <div class="w-100"></div>




    <div class="col-sm-6">
     <div class="form-group">
      <label for="sales_no_of_unit">No. Of Units<small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="icon icon-unit" aria-hidden="true"></i>
       <input min="1" required type="number" class="form-control" id="sales_no_of_unit" name="sales_no_of_unit" value="{{old('sales_no_of_unit',isset($enquiry)? $enquiry->sales_no_of_unit : '1')}}" placeholder="" data-rule-maxlength="3" data-msg-maxlength="Maximum length Allowed 3" >
     </div>
   </div>
 </div>

 <div class="col-sm-6">
  <div class="form-group autocomplete-cls">
    <label>List Of Predefined Areas<small class="textRed">*</small></label>
    
    @php
    if(old('location_id')){       
    $locations_sales = $locations->whereIn('id',old('location_id'));

  }elseif(isset($enquiry)){
  $location_val = $enquiry->locations()->pluck('location_id');   
  $location_val = (array)array_flatten($location_val); 
  $locations_sales = $locations->whereIn('id',$location_val);
}
@endphp  
<div class="p-relative">
  <i class="fa fa-globe icn-add" aria-hidden="true"></i>
  <select id="location_id" required name="location_id[]" class="tokenize-remote-demo1 form-group" multiple>      
   @isset($locations_sales)
   @foreach($locations_sales as $location)
   <option selected value="{{$location->id}}">{{$location->locations_name}}</option>
   @endforeach
   @endif
 </select>
</div>
</div>
</div>                                              
<div class="w-100"></div>      

<div class="col-sm-4">
  <div class="form-group">
    <label for="sales_size">Square Meter</label>
    <div class="p-relative">
     <i class="fa fa-arrows-h icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control" name=
     "sales_size" id="sales_size" placeholder="Enter Square Meter" value="{{old('sales_size',isset($enquiry)? $enquiry->sales_size : '')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values" >
   </div>
 </div>
</div>

<div class="col-sm-4">
  <div class="form-group">
   <label>Tenant Type</label>
   <div class="p-relative">
     <i class="icon icon-tenant" aria-hidden="true"></i>
     <select name="tenant_type_id" class="form-control">
      @foreach($tenantTypes as $tenant_types)
      <option {{ (old('tenant_type_id',isset($enquiry)?  $enquiry->tenant_type_id : '' ) == $tenant_types->id)?  'selected':''  }}   value="{{$tenant_types->id}}">{{$tenant_types->tenant_types_name}}</option>
      @endforeach
    </select>
  </div> 
</div> 
</div>
<div class="col-sm-4">
  <div class="form-group">
    <label>Source<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-soundcloud icn-add" aria-hidden="true"></i>
     <select required name="sales_mode_id" class="form-control">
      <option value="">Select</option>
      @foreach($enquirySources as $enquirySource)
      <option {{ (old('sales_mode_id',isset($enquiry)?  $enquiry->sales_mode_id : '') == $enquirySource->id)?  'selected':''  }}  value="{{$enquirySource->id}}">{{$enquirySource->enquiry_sources_name}}</option>
      @endforeach
    </select> 
  </div>
</div> 
</div>
<div class="w-100"></div>

<div class="col-sm-3">
  <div class="form-group">
    <label for="sales_referred_by">Referred By</label>
    <div class="p-relative">
     <i class="fa fa-globe icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control" name="sales_referred_by" id="sales_referred_by" placeholder="Enter Referred By" value="{{old('sales_referred_by',isset($enquiry)? $enquiry->sales_referred_by : '')}}">
   </div>
 </div>
</div>
<div class="col-sm-3">
  <div class="form-group">
    <label for="sales_referred_by">Activities</label>
    <div class="p-relative">
     <i class="fa fa-soundcloud icn-add" aria-hidden="true"></i>
     <select  name="activity" class="form-control">
      <option value="">Select</option>
      <option value="retail">Retail</option>
      <option value="office">Office</option>
      <option value="warehouse">Warehouse</option>
      <option value="restaurent">Restaurent</option>
      <option value="coffeeshop">Coffee Shop</option>
      <option value="others">Others</option>
      
    </select> 
  </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="sales_move_in_date">Move In Date<small class="textRed">*</small></label>

    <div class="row">
     <div class="col-sm-6 ">
       <div class="p-relative">
         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
         <select required class="form-control year" name="year" id="year">
          <option value="">Select Year </option>
          @php
          if(isset($enquiry)){
          $year = \Carbon\Carbon::parse($enquiry->sales_move_in_date)->year;                   
        }else $year = 0;
        
        $now = \Carbon\Carbon::now(); @endphp
        @isset($year)
        @if($year != $now->year && $year != ($now->year + 1) &&  $year > 0 )
        <option selected value="{{$year}}">{{$year}}</option>
        @endif
        @endisset


        <option {{(old('year',$year) == $now->year)? 'selected' :'selected'  }} value="{{$now->year}}">{{$now->year}}</option>
        <option {{(old('year',$year) == ($now->year + 1))? 'selected' :''  }} value="{{($now->year + 1)}}">{{($now->year + 1)}}</option>
      </select>
    </div>
  </div>
  <div class="col-sm-6 "> 
   <div class="p-relative">
     <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
     <select required  class="form-control month" name="month" id="month" >
      <option value="">Select Month </option>
      @php
      $mon = date('m');
      if(isset($enquiry)){
      $month = \Carbon\Carbon::parse($enquiry->sales_move_in_date)->month;
      $year = \Carbon\Carbon::parse($enquiry->sales_move_in_date)->year;
      $cur = date('Y'); 
      if($year == $cur)$mon = $mon;else $mon =1;
    }else $month = 0;
    @endphp
    @for($m=$mon; $m<=12; ++$m)
    <option {{(old('month',$month) == $m)? 'selected' :''  }} value="{{$m}}" >{{date('F', mktime(0, 0, 0, $m, 1))}}</option>
    @endfor
  </select>
  
</div>
</div>
</div>
<!--  <input type="date" required name="sales_move_in_date" class="form-control" id="sales_move_in_date" placeholder="Enter Move in date" value="{{old('sales_move_in_date',isset($enquiry)? $enquiry->sales_move_in_date->format('Y-m-d') : '')}}"> -->
</div>

</div>

<div class="w-100"></div>
<div class="col-sm-12">
  <div class="form-group">
    <label for="simpleFormEmail">Remark</label>
    <div class="p-relative">
     <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
     <textarea class="form-control" name="sales_note" rows="2" placeholder="Enter Remark">{{old('sales_note',isset($enquiry)? $enquiry->sales_note : '')}}</textarea>
   </div>
 </div>
</div>
<div class="w-100"></div>
<div class="col">
  <button type="submit" class="btn btn-primary save-enquiry">Submit</button>
</div></div>
</div>
</form>

</div>
@endif

@if((isset($enquiry) && $enquiry->sales_type == 2 && empty($type)) || (!isset($enquiry)&& !isset($complaintEnquiry) && empty($type)) || $type==2 )  

<div class="tab-pane  @if(isset($type)) {{($type==2)?'active':''}} @else {{(old('sales_type',isset($enquiry)? $enquiry->sales_type : ''  ) == 2)? 'active': ''}}   @endif" id="landlord">

 <form method="post" autocomplete="off" id="landlord-form" action="{{isset($enquiry)? route( 'enquiry.update',$enquiry->id) : route( 'enquiry.store')}}">
  @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
  <input type="hidden" name="sales_type" value="2">
  <div class="clearfix"></div>
  <div class="dataSearchBox">
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="landlord_sales_enquiry_no">Enquiry No</label>
          <div class="p-relative">
           <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
           <input type="text" name="sales_enquiry_no" disabled=""  class="form-control" id="sales_enquiry_no" value="{{ isset($enquiry)? $enquiry->sales_enquiry_no : $nextLandlordCode}}">
         </div>
       </div>
     </div>
     <div class="col-sm-6">
      <div class="form-group">
        <label for="landlord_enquiry_date">Enquiry Date</label>
        <div class="p-relative">
         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
         <input type="text" disabled="" class="form-control" id="landlord_enquiry_date" value="{{ isset($enquiry)? $enquiry->created_at->format('d/m/Y') : today()->format('d/m/Y') }}" placeholder="">
       </div>
     </div>
   </div> 
   <div class="w-100"></div>
   <div class="col-sm-4">
    <div class="form-group">
      <label for="landlord_sales_mobile_no">Mobile No<small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
       <input required type="text" class="form-control mob_validation_13" id="landlord_sales_mobile_no"  name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no : '00968'  )}}"  placeholder="Enter Mobile No"  onkeypress="return isNumber(event)">

     </div>
   </div>
 </div>
 <div class="col-sm-4">
  <div class="form-group">
    <label for="alternative_no">Alternative No</label>
    <div class="p-relative">
     <i class="fa fa-sort-amount-desc icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control mob_validation" id="landlord_alternative_no"  name="alternative_no" 
     value="{{old('alternative_no',isset($enquiry)?trim($enquiry->alternative_no):'00968')}}"  placeholder="Enter Alternative Mobile No"  onkeypress="return isNumber(event)">
   </div>
 </div>
</div>

                  <!-- <div class="col-sm-3">
                        <div class="form-group">
                            <label>Region<small class="textRed">*</small></label>
                            <div class="p-relative">
                   <i class="fa fa-globe icn-add" aria-hidden="true"></i>
                    <select required name="sales_region" class="form-control">
                        <option value="">Select</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 1)? 'selected':'selected' }}   value="1">Local</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 2)? 'selected':'' }}   value="2">International</option>
                    </select> 
                  </div>
                </div> 
              </div> -->  


              <div class="col-sm-4">
                <div class="form-group">
                  <label for="landlord_sales_enquiry_name">Landlord Name<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="icon icon-landlord" aria-hidden="true"></i>
                   <input required pattern="[A-Za-z\s]+" type="text" required name="sales_enquiry_name" class="form-control" id="landlord_sales_enquiry_name"  value="{{old('sales_enquiry_name',isset($enquiry)? $enquiry->sales_enquiry_name : ''   ) }}" placeholder="Enter Landlord Name">
                 </div>
               </div>
             </div>                                                      
             
             
             <div class="w-100"></div>
             <!-- enqui -->

             <div class="col-sm-4">
              <div class="form-group">
                <label for="landlord_simpleFormEmail">Email</label>
                <div class="p-relative">
                 <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
                 <input type="email"  name="sales_email" class="form-control" id="landlord_simpleFormEmail" value="{{old('sales_email',isset($enquiry)? $enquiry->sales_email : '')}}" placeholder="Enter Email">
               </div>
             </div>
           </div>     
           <div class="col-sm-4">
            <div class="form-group">
              <label for="sales_email">Company Name</label>
              <div class="p-relative">
               <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
               <input type="text" data-rule-pattern="[a-zA-Z-@\s]*$" name="sales_company_name" class="form-control" id="company_name" value="{{old('sales_company_name',isset($enquiry)? $enquiry->sales_company_name : '')}}" placeholder="Company Name">
             </div>
           </div>
         </div>

         <div class="col-sm-4">
          <div class="form-group">
            <label>Property Type</label>
            <div class="p-relative">
             <i class="fa fa-university icn-add" aria-hidden="true"></i>
             <select  name="building_type_id" class="form-control">
              <option value="">Select</option>
              @foreach($buildingTypes as $buildingType)
              <option {{ (old('building_type_id',isset($enquiry)?  $enquiry->building_type_id : '') == $buildingType->id)?  'selected':''  }}  value="{{$buildingType->id}}">{{$buildingType->building_types_name}}</option>
              @endforeach
            </select> 
          </div>
        </div> 
      </div>
      <div class="w-100"></div>          

      @unlessrole('call_center')
      <div class="w-100"></div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="landlord_sales_building_name">Building Name</label>
          <div class="p-relative">
           <i class="icon icon-building" aria-hidden="true"></i>
           <input type="text" class="form-control" name=
           "sales_building_name" id="landlord_sales_building_name" placeholder="Enter Building Name" value="{{old('sales_building_name',isset($enquiry)? $enquiry->sales_building_name : '')}}">
         </div>
       </div>
     </div>
     <div class="col-sm-6">
      <div class="form-group">
        <label for="sales_referred_by">Referred By</label>
        <div class="p-relative">
         <i class="fa fa-user icn-add" aria-hidden="true"></i>
         <input type="text" class="form-control" name="sales_referred_by" id="landlord_sales_referred_by" placeholder="Enter Referred By" value="{{old('sales_referred_by',isset($enquiry)? $enquiry->sales_referred_by : '')}}">
       </div>
     </div>
   </div>
   <div class="w-100"></div>
   <div class="col-sm-6">
    <div class="form-group">
      <label>Source</label>
      <div class="p-relative">
       <i class="fa fa-futbol-o icn-add" aria-hidden="true"></i>
       <select name="sales_mode_id" class="form-control">
        <option value="">Select</option>
        @foreach($enquirySources as $enquirySource)
        <option {{ (old('sales_mode_id',isset($enquiry)?  $enquiry->sales_mode_id : '') == $enquirySource->id)?  'selected':''  }}  value="{{$enquirySource->id}}">{{$enquirySource->enquiry_sources_name}}</option>
        @endforeach
      </select> 
    </div>
  </div> 
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="sales_size">Square Meter</label>
    <div class="p-relative">
     <i class="fa fa-arrows-h icn-add" aria-hidden="true"></i>
     <input  type="text" class="form-control" name=
     "sales_size" id="sales_size" placeholder="Enter Square Meter" value="{{old('sales_size',isset($enquiry)? $enquiry->sales_size : '')}}" >
   </div>
 </div>
</div>
<div class="w-100"></div>
          <!-- <div class="col-sm-6">
           <div class="form-group">
              <label for="landlord_sales_no_of_unit">No. of units</label>
              <div class="p-relative">
                         <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
              <input type="number" min="1" class="form-control" id="landlord_sales_no_of_unit" name="sales_no_of_unit" value="{{old('sales_no_of_unit',isset($enquiry)? $enquiry->sales_no_of_unit : '1')}}" placeholder="">
            </div>
           </div>
         </div> -->
         <div class="col-sm-4">
          <div class="form-group autocomplete-cls">
            <label >Location</label> 
            @php
            if(isset($enquiry)){
            $location_val = $enquiry->locations()->pluck('location_id');   
            $location_val = (array)array_flatten($location_val); 
          }
          @endphp 
          <div class="p-relative">
           <i class="fa fa-globe icn-add" aria-hidden="true"></i>
           <select  name="location_id[]"  class="form-control">
            <option value="">Select Location</option>
            @foreach($locations as $location)
            <option {{ isset($enquiry)? ((array_search($location->id,$location_val) !== false)? 'selected':'' )  :'' }}  {{ (old('location_id') !== null  && (old('sales_type') == 2))? 
              ((array_search($location->id,old('location_id')) !== false)?  'selected':''  ) : '' }} value="{{$location->id}}">{{$location->locations_name}}</option>
              @endforeach
            </select>    
          </div>             
        </div>
      </div>
      
      <div class="col-sm-4">
        <div class="form-group">
          <label>Region</label>
          <div class="p-relative">
           <i class="fa fa-globe icn-add" aria-hidden="true"></i>
           <select name="sales_region" class="form-control" id="landlord_sales_region">
            <option value="">Select</option>
            <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 1)? 'selected':'selected' }}   value="1">Local</option>
            <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 2)? 'selected':'' }}   value="2">International</option>
          </select> 
        </div>
      </div> 
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label >Price Range</label>
        @php
        if(isset($enquiry)){
        $price_range_val = $enquiry->priceRanges()->pluck('price_range_id');   
        $price_range_val = (array)array_flatten($price_range_val); 
      }
      @endphp 
      <div class="p-relative">
       <i class="fa fa-money icn-add" aria-hidden="true"></i>
       <select  name="price_range_id[]"  class="form-control">
        <option value="">Select Price Range</option>
        @foreach($priceRanges as $priceRange)
        <option {{ isset($enquiry)? ((array_search($priceRange->id,$price_range_val) !== false)? 'selected':'' )  :'' }}  {{ (old('price_range_id') !== null && (old('sales_type') == 2))? 
          ((array_search($priceRange->id,old('price_range_id')) !== false)?  'selected':''  ) : '' }}   value="{{$priceRange->id}}">{{$priceRange->price_ranges_name}}</option>
          @endforeach
        </select>
      </div> 
    </div>
  </div>
  <div class="w-100"></div>
  <div class="col-sm-12">
    <div class="form-group">
     <label for="landlord_sales_contact_address">Customer Address</label>
     <div class="p-relative">
      <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
      <textarea  name="sales_contact_address" class="form-control" id="landlord_sales_contact_address"  placeholder="Enter Customer Address" >{{old('sales_contact_address',isset($enquiry)? $enquiry->sales_contact_address : '')}}</textarea>            
    </div>
  </div>
</div>
@endunlessrole


<div class="w-100"></div>
<div class="col-sm-12">
  <div class="form-group">
    <label for="simpleFormEmail">Remark</label>
    <div class="p-relative">
     <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
     <textarea class="form-control" name="sales_note" rows="2" placeholder="Enter Remark">{{old('sales_note',isset($enquiry)? $enquiry->sales_note : '')}}</textarea>
   </div>
 </div>
</div>

<div class="w-100"></div>
<div class="col">
  <button type="submit" class="btn btn-primary save-enquiry">Submit</button>
</div>


</div>
</div>
</form> <!-- {{(old('sales_type',isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  ) != '')? 'active': ''}}" -->
</div>
@endif 
<!-- -------------------------- Maintenance Div ----------------------------------- -->

@if((isset($complaintEnquiry) ) || (!isset($enquiry)&& !isset($complaintEnquiry)) || $type==3 ) 
@include('maintenance::complaint_form')
@endif
</div>
</div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>

@section('scripts')
@include('sales::add_sub_complaint_js')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script src="{{ asset('public/js/jquery.ui.touch-punch.min.js') }}"></script>
@include('maintenance::complaint_enquiry_js')

<script>
  $(document).ready(function() {

    $(".AccountSelectBox input:checkbox").change(function () {
     if(this.checked)  
       $(this).closest("tr").find('.AccountAmountBox').removeAttr("disabled");
     else
       $(this).closest("tr").find('.AccountAmountBox').prop("disabled",true);

   });
    /***************************************************************************/
    jQuery('.two').hide();
    jQuery('.three').hide();
    jQuery('.one').show();
    /***************************************************************************/
    jQuery('.tenantone').click(function(){
      jQuery('.one').show();
      jQuery('.two').hide();
      jQuery('.three').hide();
    }); 
    jQuery('.landlordtwo').click(function(){
      jQuery('.one').hide();
      jQuery('.two').show();
      jQuery('.three').hide();
    });
    jQuery('.maintenancethree').click(function(){
      jQuery('.one').hide();
      jQuery('.two').hide();
      jQuery('.three').show();
    });
    /***************************************************************************/
    // Read oman code by default
    if($('#sales_mobile_no').val())
      var readOnlyLength = $('#sales_mobile_no').val().length;
    if($('#landlord_sales_mobile_no').val())
      var readOnlyLandlordLength = $('#landlord_sales_mobile_no').val().length;

    $('#sales_mobile_no').on('keypress, keydown', function(event) {
      var $field = $(this);

      if($('#sales_region').val() == 1 ){
       if ((event.which != 37 && (event.which != 39)) &&
         ((this.selectionStart < readOnlyLength) ||
          ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
         return false;
     }
   }

 });
    $('#landlord_sales_mobile_no').on('keypress, keydown', function(event) {
      var $field = $(this);

      if($('#landlord_sales_region').val() == 1 || $('#landlord_sales_region').val() == undefined){
       if ((event.which != 37 && (event.which != 39)) &&
         ((this.selectionStart < readOnlyLength) ||
          ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
         return false;
     }
   }
 });

    $('.sales_region').on('change', function(event) {

      var region = $(this).val();
      if($(this).val() == 2){ 

        $('#sales_mobile_no').removeClass('mob_validation_13');
        $('#sales_mobile_no').removeClass('customRegMob_thirteen_digit');
        $('#sales_mobile_no').addClass('mob_validation');
        $('#sales_mobile_no').addClass('customRegMob');
        $('#sales_mobile_no').val('001');
        $('#alternative_no').val('001');
      }
      else{
       $('#sales_mobile_no').removeClass('mob_validation');
       $('#sales_mobile_no').removeClass('customRegMob');
       $('#sales_mobile_no').addClass('mob_validation_13');
       $('#sales_mobile_no').addClass('customRegMob_thirteen_digit');
       $('#sales_mobile_no').val('00968');
       $('#alternative_no').val('00968');
     }

   });
    $('#landlord_sales_region').on('change', function(event) {

      var region = $(this).val();

      if($(this).val() == 2){ 

        $('#landlord_sales_mobile_no').removeClass('mob_validation_13');
        $('#landlord_sales_mobile_no').removeClass('customRegMob_thirteen_digit');
        $('#landlord_sales_mobile_no').addClass('mob_validation');
        $('#landlord_sales_mobile_no').addClass('customRegMob');
        $('#landlord_sales_mobile_no').val('001');
        $('#landlord_alternative_no').val('001');
      }
      else{
       $('#landlord_sales_mobile_no').removeClass('mob_validation');
       $('#landlord_sales_mobile_no').removeClass('customRegMob');
       $('#landlord_sales_mobile_no').addClass('mob_validation_13');
       $('#landlord_sales_mobile_no').addClass('customRegMob_thirteen_digit');
       $('#landlord_sales_mobile_no').val('00968');
       $('#landlord_alternative_no').val('00968');
     }

   });

    /******************************/


    /*****************************************************************/
    var dtToday = new Date();
    
    var oneMonth = dtToday.setMonth(dtToday.getMonth() - 1);

    var minDate = formatDate(oneMonth);
    var maxDate = formatDate(new Date());
    
    
    /*************************************************************************/
    $('#complaint_date').attr('min', minDate);
    $('#complaint_date').attr('max', maxDate);

    $("#tenant-form").validate({
      rules: {
        sales_email: {
          customEmail: true
        },

      },

      ignore: [], 
      invalidHandler: function(form, validator) {

       for (var i=0;i<validator.errorList.length;i++){
        if(validator.errorList[i].element.id=='sales_mobile_no' || validator.errorList[i].element.id=='sales_enquiry_name'){

          $('html, body').scrollTop(120);
        }
      }
      
      
    },  
    submitHandler: function(form) {
      $('.save-enquiry').prop('disabled', true);
      form.submit();
    }, 
  });



    $("#landlord_simpleFormEmail").addClass("customEmail");
    $("#sales_email").addClass("customEmail");
    $.validator.addClassRules({
      customEmail: {
        customEmail: true
      }
    });
    jQuery.validator.addMethod("customEmail", function(value, element) {
      return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
    }, "Please enter a valid email address");



/***************************************************************************/
$("#landlord-form").validate({

  ignore: [], 
  invalidHandler: function(form, validator) {

    for (var i=0;i<validator.errorList.length;i++){
     if(validator.errorList[i].element.id=='sales_mobile_no' || validator.errorList[i].element.id=='sales_enquiry_name'){

      $('html, body').scrollTop(120);
    }
  }


},
submitHandler: function(form) {
  $('.save-enquiry').prop('disabled', true);
  form.submit();
},
});
/**************************************************************************************/


/**************************************************************************************/
   /* $(".read").attr('readonly',true);
    $("#maintenance-form").validate()
    $("#maintenance-form").on('submit',function(e){
      var rowCount = $('#complaint_form tr').length;
      var tstatus = $("#tenantStatus").val();

      if(tstatus == 2){
        alert("Cannot Accept Complaint Due To ON HOLD Status ");
        return false;
      }
      if(rowCount < 1){
        alert("Cannot Accept Complaint Without Tickets ");
        return false;
      }
      

    });*/
    /**************************************************************************************/
    
    /*************************************************************************/
    $(document).on('change',".year", function(){
      var year = $('#year').val();
      var monthArray = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
      var today = new Date();
      var fullyear = today.getFullYear();

      if(year == fullyear){
        today.setMonth(today.getMonth() - 1);
        var cur_month = today.getMonth() + 1;
        var i;
        $('#month').empty();
        $('#month').append('<option value="">'+ 'Select Month' +'</option>')
        for (i=cur_month; i<12; i++) {

          var optn = document.createElement("OPTION");
          optn.text = monthArray[i];
          optn.value = (i+1);
          document.getElementById('month').options.add(optn);
        }
      }else{
        var i;
        $('#month').empty();
        $('#month').append('<option value="">'+ 'Select Month' +'</option>')
        for (i=0; i<12; i++) {

          var optn = document.createElement("OPTION");
          optn.text = monthArray[i];
          optn.value = (i+1);
          document.getElementById('month').options.add(optn);
        }
      }
    });
    /*************************************************************************/




    /*************************************************************************/
    $('#sales_mobile_no').bind("change keyup input",function() {   

      var mobilenumber = $('#sales_mobile_no').val();
       // var name = $('#sales_enquiry_name').val();
       var mobile_size = mobilenumber.length;

       if(mobile_size  >  7 ){

        $.ajax({
          method: "POST",
          url: "{{route('tenantSearchByMobile')}}",
          data: { mobile: mobilenumber, name : name , 
            "_token" : $('meta[name="csrf-token"]').attr('content')},
            success: function(data){                            
              if(data != 0){
                $('#sales_enquiry_name').val(data.tenant_name);
                $('#sales_email').val(data.tenant_contact_email);
                $('#sales_contact_address').val(data.tenant_contact_address);
                $('#sales_mobile_no').val(data.tenant_contact_no);
              }else{
                @empty($enquiry)								
                $('#sales_enquiry_name').val('');
                $('#sales_email').val('');
                $('#sales_contact_address').val('');                               
                @endempty
                
              }               
            }           
          });
      }

    });
    /*************************************************************************/
    $('#landlord_sales_mobile_no').bind("change keyup input",function() {   

      var mobilenumber = $('#landlord_sales_mobile_no').val();
       // var name = $('#sales_enquiry_name').val();
       var mobile_size = mobilenumber.length;

       if(mobile_size  >  7 ){

        $.ajax({
          method: "POST",
          url: "{{route('landlordSearchByMobile')}}",
          data: { mobile: mobilenumber, name : name , 
            "_token" : $('meta[name="csrf-token"]').attr('content')},
            success: function(data){                            
              if(data != 0){
                $('#landlord_sales_enquiry_name').val(data.vendor_name);
                $('#landlord_simpleFormEmail').val(data.vendor_contact_email);
                $('#landlord_sales_contact_address').val(data.vendor_contact_address);
                $('#landlord_sales_mobile_no').val(data.vendor_contact_no);
              }else{
                @empty($enquiry)                
                $('#landlord_sales_enquiry_name').val('');
                $('#landlord_simpleFormEmail').val('');
                $('#landlord_sales_contact_address').val('');                               
                @endempty
                
              }               
            }           
          });
      }

    });
    /*************************************************************************/
    $('.tokenize-remote-demo1').on("tokenize:tokens:add", function (event, value, text){

      if(value){

       $("#location_id-error").hide();
     }


   });
    /*************************************************************************/
    $('.tokenize-remote-demo1').on("tokenize:tokens:remove", function (event, value, text){
     $('#location_id').valid();
   });
  });


 //Unit Type

 /***sortablePriceRange1***/









 $('.tokenize-remote-demo1').tokenize2({

  placeholder: " &nbsp;&nbsp; Type The Letter For Preferred Location",
  dataSource: function(term, object){
    $.ajax('{{route("location.locationAutocomplete")}}', {
      data: { search: term, start: 0 },
      dataType: 'json',
      success: function(data){
        var $items = [];
        $.each(data, function(k, v){
          $items.push(v);
        });
        object.trigger('tokenize:dropdown:fill', [$items]);
        
      }
    });
  }
});
 $("#myModal").on("hidden.bs.modal", function(){
  $("#myModal").html("");
  $(this).removeData('bs.modal');
});


 @if(old('sales_type') == 1 )
 $('#landlord-form').find("input[type=text], textarea , select").val("");
 @elseif(old('sales_type') == 2)  
 $('#tenant-form').find("input[type=text], textarea , select").val("");
 @endif

 $(".draggable").draggable({
  revert: "invalid"
})
 .dblclick(function(){
  var myarr = [];
  if ($(this).parent().attr("id") != "drop") { 
   $(this).hide().appendTo("#drop").show('fast');
   $("#drop .list_item").each(function(){
    if($(this).attr('id'))

      myarr[myarr.length] = $(this).attr('id');       
  });
   $('.unittype').val(myarr.toString());
   if($('.unittype').val() != '')
    $('#sales_unit_type_id-error').hide();
  else
   $('#sales_unit_type_id-error').show();
} else {
 $(this).hide().appendTo("#grid").show('fast');
 $("#drop .list_item").each(function(){
  if($(this).attr('id'))

    myarr[myarr.length] = $(this).attr('id');       
});
 $('.unittype').val(myarr.toString());
 if($('.unittype').val() != '')
  $('#sales_unit_type_id-error').hide();
else
 $('#sales_unit_type_id-error').show();
}
});
 $("#drop").droppable({
  accept: ".draggable",
  drop: function (event, ui) {
        	//alert(1222);
          var myarr = [];
         // console.log("drop");
          $(this).addClass("over");
          var dropped = ui.draggable;
          var droppedOn = $(this);
          
          $(dropped).detach().css({
            top: 0,
            left: 0
          }).appendTo(droppedOn);
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))

              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.unittype').val(myarr.toString());
          if($('.unittype').val() != '')
            $('#sales_unit_type_id-error').hide();
          else
           $('#sales_unit_type_id-error').show();

       },
       over: function (event, elem) {
        	//alert(222);
          $(this).addClass("over");
         // console.log("over");
          var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))

              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.unittype').val(myarr.toString());
          if($('.unittype').val() != '')
            $('#sales_unit_type_id-error').hide();
          else
           $('#sales_unit_type_id-error').show();
         
       },
       out: function (event, elem) {
        	//alert(555);
        	var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))
              //alert($(this).attr('id'));
            myarr[myarr.length] = $(this).attr('id');       
          });
          $('.unittype').val(myarr.toString());
          if($('.unittype').val() != '')
            $('#sales_unit_type_id-error').hide();
          else
           $('#sales_unit_type_id-error').show();
         $(this).removeClass("over");
       }
     });
 $("#drop").sortable();

 $("#grid").droppable({
  accept: ".draggable",
  drop: function (event, ui) {
        	//alert(1111);
        	
          if($('#drop img').length == 1)                 
           // console.log("drop");
          $(this).removeClass("over");
          var dropped = ui.draggable;
          var droppedOn = $(this);
          $(dropped).detach().css({
            top: 0,
            left: 0
          }).appendTo(droppedOn);
          var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))
              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.unittype').val(myarr.toString());
          if($('.unittype').val() != '')
            $('#sales_unit_type_id-error').hide();
          else
           $('#sales_unit_type_id-error').show();

         if ($(this).find('.draggable').length === 0) {
          $(this).append($(ui.draggable));

        }

      }
    });
 
 $(".price_draggable").draggable({
  revert: "invalid"
})
 .dblclick(function(){
  var myarr = [];
  if ($(this).parent().attr("id") != "drop1") { 
   $(this).hide().appendTo("#drop1").show('fast');
   $("#drop1 .list_item").each(function(){
    if($(this).attr('id'))
      myarr[myarr.length] = $(this).attr('id');       
  });
   $('.pricerange').val(myarr.toString());
   if($('.pricerange').val()  != '')
    $('#price_range_id-error').hide();
  else
   $('#price_range_id-error').show();
} else {
 $(this).hide().appendTo("#grid1").show('fast');
 $("#drop1 .list_item").each(function(){
  if($(this).attr('id'))
    myarr[myarr.length] = $(this).attr('id');       
});
 $('.pricerange').val(myarr.toString());
 if($('.pricerange').val()  != '')
  $('#price_range_id-error').hide();
else
 $('#price_range_id-error').show();
}
});
 $("#drop1").droppable({
  accept: ".price_draggable",
  drop: function (event, ui) {


    //console.log("drop1");
    $(this).addClass("over");
    var dropped = ui.draggable;
    var droppedOn = $(this);
    $(dropped).detach().css({
      top: 0,
      left: 0
    }).appendTo(droppedOn);
    var myarr = [];
    $("#drop1 .list_item").each(function(){
      if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
    $('.pricerange').val(myarr.toString());
    if($('.pricerange').val()  != '')
      $('#price_range_id-error').hide();
    else
     $('#price_range_id-error').show();

 },
 over: function (event, elem) {

  $(this).addClass("over");
  //console.log("over");
  var myarr = [];
  $("#drop1 .list_item").each(function(){
    if($(this).attr('id'))
      myarr[myarr.length] = $(this).attr('id');       
  });
  $('.pricerange').val(myarr.toString());
  if($('.pricerange').val()  != '')
    $('#price_range_id-error').hide();
  else
   $('#price_range_id-error').show();
},
out: function (event, elem) {

  $(this).removeClass("over");
  var myarr = [];
  $("#drop1 .list_item").each(function(){
    if($(this).attr('id'))
      myarr[myarr.length] = $(this).attr('id');       
  });
  $('.pricerange').val(myarr.toString());
  if($('.pricerange').val()  != '')
   $('#price_range_id-error').hide();
 else
   $('#price_range_id-error').show();
}
});
 $("#drop1").sortable();

 $("#grid1").droppable({
  accept: ".price_draggable",
  drop: function (event, ui) {

    if($('#drop1 img').length == 1)
      //console.log("drop1");
    $(this).removeClass("over");
    
    var dropped = ui.draggable;
    var droppedOn = $(this);
    $(dropped).detach().css({
      top: 0,
      left: 0
    }).appendTo(droppedOn);
    var myarr = [];
    $("#drop1 .list_item").each(function(){
      if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
    $('.pricerange').val(myarr.toString());
    if($('.pricerange').val() != '')
      $('#price_range_id-error').hide();
    else
     $('#price_range_id-error').show();
   if ($(this).find('.price_draggable').length === 0) {
    $(this).append($(ui.draggable));

  }

}
});

</script>
@endsection


