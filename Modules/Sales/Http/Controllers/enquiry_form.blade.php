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
          <li class="nav-item"><a href="#tenant" data-toggle="tab"class="{{(old('sales_type') <= 1)? 'active': ''}}" >Tenant</a>
          </li><!-- {{(old('sales_type') <= 1)? 'active': ''}} -->
          @endif

          @if((isset($enquiry) && $enquiry->sales_type == 2) || !isset($enquiry)&& !isset($complaintEnquiry)) 
          <li class="nav-item"><a href="#landlord" data-toggle="tab" class="{{(old('sales_type',isset($enquiry)? $enquiry->sales_type : ''  ) == 2)? 'active': ''}}">Landlord</a>
          </li>
          @endif 
          @if(isset($complaintEnquiry) || !isset($enquiry)&& !isset($complaintEnquiry))
          <li class="nav-item"><a href="#maintenance" data-toggle="tab" class="{{(old('sales_type',isset($complaintEnquiry)? $complaintEnquiry: ''  ) != '')? 'active': ''}}">Maintenance</a>
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

                   <input data-rule-pattern="^((\+)?(\d{2,2}))?(\d{8}){1}?$" data-msg-pattern="Allowed only 8 Digit Numeric Values" required type="text" class="form-control" id="sales_mobile_no"  name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no :'00968')}}"  placeholder="Enter Mobile No">
                 </div>
               </div>
             </div>

             <div class="col-sm-4">
              <div class="form-group">
                <label for="alternative_no">Alternative Mobile No</label>
                <div class="p-relative">
                 <i class="fa fa-sort-amount-desc icn-add" aria-hidden="true"></i>
                 <input type="text" class="form-control" id="alternative_no"  name="alternative_no" 
                 value="{{old('alternative_no',isset($enquiry->alternative_no)?trim($enquiry->alternative_no):'00968')}}"  placeholder="Enter Alternative Mobile No">
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

         <div class="w-100"></div>
           </div>

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

<div class="col-sm-6">
  <div class="form-group">
    <label for="sales_referred_by">Referred By</label>
    <div class="p-relative">
     <i class="fa fa-globe icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control" name="sales_referred_by" id="sales_referred_by" placeholder="Enter Referred By" value="{{old('sales_referred_by',isset($enquiry)? $enquiry->sales_referred_by : '')}}">
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
<div class="col"><button type="submit" class="btn btn-primary save-enquiry">Submit</button></div>
</div>
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
       <input  data-rule-pattern="^((\+)?(\d{2,2}))?(\d{13}){1}?$" data-msg-pattern="Allowed only 13 Or 15 Digit Numeric Values"  required type="text" class="form-control" id="landlord_sales_mobile_no"  name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no : '00968'  )}}"  placeholder="Enter Mobile No">
     </div>
   </div>
 </div>
 <div class="col-sm-4">
  <div class="form-group">
    <label for="alternative_no">Alternative No</label>
    <div class="p-relative">
     <i class="fa fa-sort-amount-desc icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control" id="landlord_alternative_no"  name="alternative_no" 
     value="{{old('alternative_no',isset($enquiry->alternative_no)?trim($enquiry->alternative_no):'00968')}}"  placeholder="Enter Alternative Mobile No">
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
                   <input required pattern="[A-Za-z\s]+" type="text" required name="sales_enquiry_name" class="form-control" id="landlord_sales_enquiry_name"  value="{{old('sales_enquiry_name',isset($enquiry)? $enquiry->sales_enquiry_name :'')}}" placeholder="Enter Landlord Name">
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
            <label>Property Type<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-university icn-add" aria-hidden="true"></i>
             <select required name="building_type_id" class="form-control">
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

<div class="col"><button type="submit" class="btn btn-primary save-enquiry">Submit</button></div>



</div>
</div>
</form> <!-- {{(old('sales_type',isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  ) != '')? 'active': ''}}" -->
</div>
@endif 
<!-- -------------------------- Maintenance Div ----------------------------------- -->

@if((isset($complaintEnquiry) ) || (!isset($enquiry)&& !isset($complaintEnquiry)) || $type==3 ) 
<div class="tab-pane @if(isset($type)) {{($type==3)?'active':''}}@endif" id="maintenance">
 <form method="post" class="js-validation" autocomplete="off" id="maintenance-form" action="{{isset($complaintEnquiry)? route( 'complaint.update',$complaintEnquiry->id) : route( 'complaint.store')}}">
  @csrf  @if(isset($complaintEnquiry)){{method_field('PUT')}}@endif
  <div class="clearfix"></div>
  <div class="dataSearchBox">
    <input type="hidden" name="tenantStatus" id="tenantStatus" value="">
    <div class=" tenantStatus">
      
    </div>
    
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="complaint_no">Complaint No<small class="textRed">*</small></label>
          <div class="p-relative">
           <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
           <input required type="text" class="form-control" id="complaint_no" readonly name="complaint_no" value="{{ old('complaint_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_no : $nextcomplaintCode )}}"  placeholder="Enter Comp No">
         </div>
       </div>
     </div>
     <div class="col-sm-6">
      <div class="form-group">
        <label for="complaint_date">Complaint Date<small class="textRed">*</small></label>
        <div class="p-relative">
         <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
         <input required type="date" class="form-control" id="complaint_date" name="complaint_date" value="{{ old('complaint_date', isset($complaintEnquiry)? $complaintEnquiry->complaint_date->format('Y-m-d') : today()->format('Y-m-d')  )}}"  placeholder="Enter Comp Date">
       </div>
     </div>
   </div>
   <div class="w-100"></div>
   <div class="col-sm-6 row-even">
    <div class="form-group">
      <label for="registerd_mob_no">Registered Mobile No<small class="textRed">*</small></label>
      <div class="p-relative">
       <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
       <input pattern="^((\+)?(\d{2,3}))?(\d{8}){1}?$" required type="text" class="form-control" id="registerd_mob_no"  name="registerd_mob_no" value="{{ old('registerd_mob_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  )}}"  placeholder="Enter Registerd Mobile No">
     </div>
   </div>
 </div>
 <div class="col-sm-6 row-even">
  <div class="form-group">
    <label for="resident_card_id">Resident Card ID</label>
    <div class="p-relative">
     <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
     <input  type="text" class="form-control" id="resident_card_id"  name="resident_card_id" value="{{ old('resident_card_id', isset($complaintEnquiry)? $complaintEnquiry->tenant->resident_id : ''  )}}"  placeholder="Enter Resident Card ID">
   </div>
 </div>
</div>
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="complainer_name">Complainer Name<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
     <input required type="text" class="form-control" id="complainer_name"  name="complainer_name" value="{{ old('complainer_name', isset($complaintEnquiry)? $complaintEnquiry->complainer_name : ''  )}}"  placeholder="Enter Comp Name">
   </div>
 </div>
</div>

<div class="col-sm-6">
  <div class="form-group">
    <label for="complaint_mob_no">Complainer Mobile No<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
     <input pattern="^((\+)?(\d{2,3}))?(\d{8}){1}?$" required type="text" class="form-control" id="complaint_mob_no"  name="complaint_mob_no" value="{{ old('complaint_mob_no', isset($complaintEnquiry)? $complaintEnquiry->complaint_mob_no : ''  )}}"  placeholder="Enter Comp Mobile No">
   </div>
 </div>
</div>
<div class="w-100"></div>
<div class="col-sm-6 building_text">
  <div class="form-group">
    <label>Building <small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="icon icon-building" aria-hidden="true"></i>
     <input type="text" name="building_text" id="building_text" class="form-control building_text " placeholder="Enter Building Name" value="{{ old('building_text', isset($complaintEnquiry)? $complaintEnquiry->building->building_name : ''  )}}"> 
     <input type="hidden" name="building_text_id" id="building_text_id" class="form-control building_text" placeholder="Enter Building Name" value="{{ old('building_text_id', isset($complaintEnquiry)? $complaintEnquiry->building_id : ''  )}}">

     
   </div>
 </div> 
</div>
<div class="col-sm-6 building_select" style="display: none">
  <div class="form-group">
    <label>Building <small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="icon icon-building" aria-hidden="true"></i>
     <select name="building_id" required class="form-control Building " id="building_id">
      <option value="">Select</option>
      @foreach($buildings as $building)
      <option {{ (old('building_id',isset($complaintEnquiry)?  $complaintEnquiry->building_id : '') == $building->id)?  'selected':''  }}  value="{{$building->id}}">{{$building->building_code}}</option>
      @endforeach
    </select> 
  </div>
</div> 
</div>
<input type="hidden" name="buildings" id="buildings" class="form-control" placeholder="Enter Building Name" value="{{ old('buildings', isset($complaintEnquiry)? $complaintEnquiry->building_id : ''  )}}"> 

<div class="col-sm-6 unit_select">
  <div class="form-group">
    <label>Unit <small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="icon icon-unit" aria-hidden="true"></i>
     <select name="unit_id" required class="form-control complaintUnit" id="unit_id">
      <option value="">Select</option>
      @if(isset($complaintEnquiry))
      @foreach($com_unit as $unit)
      <option {{ (old('unit_id',isset($complaintEnquiry)?  $complaintEnquiry->unit_id : '') == $unit->id)?  'selected':''  }}  value="{{$unit->id}}">{{$unit->unit_code}}</option>
      @endforeach
      @endif
    </select> 
  </div>
</div> 
</div>                    
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="tenant_name">Tenant<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="icon icon-tenant" aria-hidden="true"></i>
     <input required type="text" class="form-control read" id="tenant_name" name="tenant_name" value="{{ old('tenant_name', isset($complaintEnquiry->tenant_id)? $complaintEnquiry->tenant->tenant_name : ''  )}}"  placeholder="Enter Tenant Name">
     <input type="hidden" name="tenant_id" id="tenant_id" value="{{ old('tenant_id', isset($complaintEnquiry)? $complaintEnquiry->tenant_id : ''  )}}">
   </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="occupant_name">Occupant Name</label>
    <div class="p-relative">
     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
     <select name="occupant_name" class="form-control read" id="occupant_name">
      <option value="">Select</option>
    </select>
    <input type="hidden" name="occupant_id" id="occupant_id" value="{{ old('occupant_id', isset($complaintEnquiry)? $complaintEnquiry->occupant_id : ''  )}}">
    
  </div>
</div>
</div>
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="location_name">Location<small class="textRed">*</small></label>
    <div class="p-relative">
     <i class="fa fa-globe icn-add" aria-hidden="true"></i>
     <input required type="text" class="form-control read" id="location_name" name="location_name" value="{{ old('location_name', isset($complaintEnquiry)? $complaintEnquiry->location->locations_name : ''  )}}"  placeholder="Enter Location">
     <input type="hidden" name="tenant_location_id" value="{{ old('tenant_location_id', isset($complaintEnquiry)? $complaintEnquiry->location_id : ''  )}}" id="tenant_location_id">
   </div>
 </div>
</div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="way_no">Way No</label>
    <div class="p-relative">
     <i class="fa fa-address-card icn-add" aria-hidden="true"></i>
     <input type="text" class="form-control read" id="way_no" name="way_no" value="{{ old('way_no', isset($complaintEnquiry)? $complaintEnquiry->way_no : ''  )}}"  placeholder="Enter Way No">
   </div>
 </div>
</div>
<div class="w-100"></div>
<div class="col-sm-6">
  <div class="form-group">
    <label for="priority_status">Priority</label>
    <div class="p-relative">
     <i class="fa fa-spinner icn-add" aria-hidden="true"></i>
     <select name="priority_status" class="form-control">
      <option value="">Select</option>
      
      <option {{ (old('priority_status',isset($complaintEnquiry)? $complaintEnquiry->priority_status : '') == 0)? 'selected':'' }}  value="0">Normal</option>
      <option {{ (old('priority_status',isset($complaintEnquiry)? $complaintEnquiry->priority_status : '') == 1)? 'selected':'' }}  value="1">High</option>
      
    </select>
  </div>
</div>
</div>                   

</div>
</div>
@if(!isset($complaintEnquiry))
<div class="clearfix"></div>

<div class="dataSearchBox panel-heading-lightblue">
  <h4>Select Complaint Category</h4>
  <div class="row bb-1 mb-3">
    <div class="col-sm-4">
      <label for="simpleFormEmail">Category<small class="textRed">*</small></label>
      <select class="form-control worksIds" id="work_id">
        <option value="">Select Category </option>
        @foreach($works as $work)
        <option value="{{$work->id}}">{{$work->works_code}} </option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label for="simpleFormEmail">Description<small class="textRed">*</small></label>
        <textarea class="form-control checkList" rows="2" placeholder="Enter Description"  id="checklist_desc"></textarea>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="dataSearchLabel w-100"></div>
      <button type="button" class="btn btn-primary dataSearchLabel add_complaint">Add</button>
    </div>
  </div>
  

</div>

@endif
<div class="clearfix"></div>
<div class="dataSearchBox panel-heading-lightblue">
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box">
      <div class="card-head">
        <header>Ticket List</header>
      </div>
      <div class="card-body ">
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table display product-overview mb-30" id="support_table5">
              <thead>
                <tr>
                  <th>Serial No</th>
                  <th>Category</th>
                  <th>Description</th>
                  
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="complaint_form">

               @if(isset($complaintEnquiry))
               @forelse ($sub_complaints as $sub_complaint)
               <tr class="tr" id="{{$loop->iteration}}"><input type="hidden" name="checklist_id[]" value="{{$sub_complaint->id}}">
                <td id="no{{$loop->iteration}}" class="counters"></td>
                <td id="work_id{{$loop->iteration}}">{{$sub_complaint->work->works_code}}<input type="hidden" name="works_id[]" value="{{$sub_complaint->work->id}}"></td>
                <td id="checklist_desc{{$loop->iteration}}">{{$sub_complaint->checklist_desc}}<input type="hidden" name="checklist_des[]" value="{{$sub_complaint->checklist_desc}}"></td>
                <td id="action{{$loop->iteration}}">
                  @if($sub_complaint->ticket_status < 1)
                  @if(!isset($complaintEnquiry))
                  <button class="btn btn-tbl-delete btn-xs remove_complaint" type="button">
                    <i class="fa fa-trash-o "></i>
                  </button>
                  @endif

                  <button type="button" class="btn btn-tbl-edit btn-xs {{isset($complaintEnquiry)? 'checklistEdit' : 'TicketEdit'}}" data-toggle="modal" data-target="#myModal" data-id = "{{$sub_complaint->work->id}}" data-ids = "{{$sub_complaint->checklist_desc}}" data-idNo="{{$loop->iteration}}"  dataa-id = "{{$sub_complaint->id}}">
                    <i class="fa fa-pencil-square-o"></i> 
                  </button>
                  @endif
                </td>                            
                
              </tr>  
              @empty
              <tr>
                <td colspan="4" align="center">
                  <p>No record</p>
                </td>
              </tr>
              @endforelse  
              @endif 
              
              
            </tbody>
          </table>
        </div>
      </div>  
    </div>
  </div>
</div>
</div>
<div class="col"><button type="submit" class="btn btn-primary save-enquiry">Submit</button></div>
</form>
</div>
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
<script>
  $(document).ready(function() {
    /*******************************/
    // Read oman code by default
    var readOnlyLength = $('#sales_mobile_no').val().length;
 

    $('#sales_mobile_no').on('keypress, keydown', function(event) {
    var $field = $(this);
    
    if($(this).val()=== '00968'){
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
          $('#sales_mobile_no').removeAttr( "data-rule-pattern" ).val('001');
           $('#alternative_no').removeAttr( "data-rule-pattern" ).val('001');
        }
        else{
           $('#sales_mobile_no').attr("data-rule-pattern",'^((\+)?(\d{2,2}))?(\d{13}){1}?$').val('00968');
           $('#alternative_no').attr("data-rule-pattern",'^((\+)?(\d{2,2}))?(\d{13}){1}?$').val('00968');
        }
           
    });

    /******************************/


    /*****************************************************************/
    var dtToday = new Date();
    
    var todaymonth = dtToday.getMonth()+1;
    var month = dtToday.getMonth();

    var day   = dtToday.getDate();

    var year  = dtToday.getFullYear();
    if(month < 10)
        month = '0' + month.toString();
    if(todaymonth < 10)
        todaymonth = '0' + todaymonth.toString();
    if(day < 10)
        day = '0' + day.toString();
    
    var minDate = year + '-' + month + '-' + day;
    var maxDate = year + '-' + todaymonth + '-' + day;
    
    
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
    
  $("#alternative_no").addClass("alternative_no");  
  $("#landlord_alternative_no").addClass("alternative_no");

  $.validator.addMethod("alternative_no", function (value, element) {

    if(value == '00968'){ return true; }
      return this.optional(element) || /^((\+)?(\d{2,2}))?(\d{13}){1}?$/i.test(value);
    
  }, "Allowed only 13 Or 15 Digit Numeric Values.");   

  $("#landlord_simpleFormEmail").addClass("customEmail");
  $.validator.addClassRules({
      customEmail: {
        customEmail: true
      }
    });
  jQuery.validator.addMethod("customEmail", function(value, element) {
    return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
}, "Please enter a valid email address");
   
// Complaint date validation with past One Month

$("#complaint_date").addClass("complaint_date");
$.validator.addMethod("dateRange", function(value, element, params) {
    try {
      var date = new Date(value);
      if (date >= params.from && date <= params.to) {
        return true;
      }
    } catch (e) {}
    return false;
  }, 'Today And One Month Back Only Allowed');
  
  $.validator.addClassRules({
    complaint_date: {
      dateRange: {
        from: new Date(minDate),
        to: new Date(maxDate)
      }
    }
  });

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
    $(".read").attr('readonly',true);
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
      

    });
    /**************************************************************************************/
    $('#building_text').autocomplete({
      source : '{!!URL::route('complaintBuildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if(ui.item.ids !=null){
          $('#building_text_id').val(ui.item.ids);
          $('#buildings').val(ui.item.ids);
          var buil = ui.item.ids;
          var tenant = $("#tenant_id").val();
          var selected = '';
          $.ajax({
            method: "POST",
            url: "{{route('complaintBuildingByUnitOccuiped')}}",
            data: {"id":buil,"tenant":tenant,"_token": "{{ csrf_token() }}"},
            cache: false,
            //dataType: "json",
            success: function(data)
            {
              var result = $.parseJSON(data);
              if(result[0].length > 0){
                if(result[0].length ==1){selected = "selected";}
                $('#unit_id').empty();
                $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                $.each(result[0], function(key, value) {
                  $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
                });
              }
              else{
                $('#unit_id').html('<option value="">No Data</option>');
              }
              $('#way_no').val(result[2].building_address);
              $('#tenant_location_id').val(result[2].location_id);
              $('#location_name').val(result[1].locations_name);
              var buil_status = result[2].building_maintenance_info;
              if(buil_status != null){
                switch(buil_status){
                  case 0:
                    //$("#tenantStatus").val(0); 
                    $(".tenantStatus").hide();
                    break;
                    case 1:
                    $(".tenantStatus").show();
                    $("#tenantStatus").val(1);  
                    $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                    break;
                  }
                  
                }
                var unit = $('#unit_id').val();
                if(unit != ""){
                  $.ajax({
                    method: "POST",
                    url: "{{route('contractDetailsByBuildingUnit')}}",
                    data: { building: buil, unit : unit , 
                      "_token" : $('meta[name="csrf-token"]').attr('content')},
                      success: function(data){
                        var results = $.parseJSON(data);
                        $('#registerd_mob_no-error').hide();
                        $('#tenant_name').val(results[1].tenant_name); 
                        $('#resident_card_id').val(results[1].resident_id);
                        $('#registerd_mob_no').val(results[1].tenant_contact_no);
                        $('#complaint_mob_no').val(results[1].tenant_contact_no);
                        $('#tenant_id').val(results[1].id);
                        $('#complainer_name').val(results[1].tenant_name);
					//$('#way_no').val(results[1].tenant_contact_address);
                  if(results[2]!=null){
                    //$('#location_name').val(results[2].locations_name);
                    //$('#tenant_location_id').val(results[2].id);

                  }
                  if(results[5] != null){ocselected = "selected";}
                  $('#occupant_name').empty();
                  $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
                  
                    //alert(value['building_id']);
                    
                    $('#occupant_name').append('<option value="'+ results[5].id +'" '+ ocselected +'>'+ results[5].occupant_name +'</option>');
                    
                    $('#way_no').val(results[3].building_address);
                    $('#location_name').val(results[4].locations_name);                               
                    $('#tenant_location_id').val(results[3].location_id);

                    $(".read").attr('readonly',true);
                    var status = results[0].status;
                    var tenantStatus = results[1].status;
                    var buil_status = results[3].building_maintenance_info;
                    if(buil_status != null){
                      switch(buil_status){
                        case 0:
                          //$("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 1:
                          $(".tenantStatus").show();
                          $("#tenantStatus").val(1); 
                          $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                          break;
                        }
                        
                      } if(status != null){
                        switch(status){
                          case 0:
                          $("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 2:
                          $("#tenantStatus").val(2);
                          $(".tenantStatus").show(); 
                          $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                          break;
                          case 3: 
                          $("#tenantStatus").val(3);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                          break;
                          case 4: 
                          $("#tenantStatus").val(4);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                          break;
                          case 5: 
                          $("#tenantStatus").val(5);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                          break;
                        }
                      }else{
                        switch(tenantStatus){
                          case 0:
                          $("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 6:
                          $("#tenantStatus").val(6);
                          $(".tenantStatus").show(); 
                          $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                          break;
                        }
                      }    
                      
                    }
                  });
}
}
});

}

}
});
/***************************************************************************************/
$("#building_id").on('change',function(e){
  var building_id = $('#building_id').val();
  $('#buildings').val(building_id);
});
/***************************************************************************************/
$("#registerd_mob_no").on('change keyup input',function(e){
  var mobilenumber = $('#registerd_mob_no').val();
  var resident_card_id = $('#resident_card_id').val();
  var mobile_size = mobilenumber.length;
  var occupant_no = $('#occupant_no').val();
  var selected = "";
  var uselected = "";
  var oselected = "";
  if(mobile_size  >  7 ){
    $.ajax({
      method: "POST",
      url: "{{route('maintenanceContractDetails')}}",
      data: { mobile: mobilenumber, occupant_no : occupant_no , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
         
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
              $('#registerd_mob_no-error').hide();
              $('#tenant_name').val(result[0].tenant_name); 
              $('#resident_card_id').val(result[0].resident_id);
              $('#registerd_mob_no').val(result[0].tenant_contact_no);
              $('#complaint_mob_no').val(result[0].tenant_contact_no);
              $('#tenant_id').val(result[0].id);
              $('#complainer_name').val(result[0].tenant_name);
              
              $('#building_id').empty();
              $('#unit_id').empty();
              if(result[5] != ""){
                $('#location_name').val(result[6].locations_name);
                $('#tenant_location_id').val(result[5].location_id);
                $('#way_no').val(result[5].building_address);
                var buil_status = result[5].building_maintenance_info;
                
                if(buil_status == 1){
                  $(".tenantStatus").show();
                  $("#tenantStatus").val(1); 
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>'); 
                }else{
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                }
              }
              
              

              $('.building_text').hide();
              $('.building_select').show();
              $('.unit_select').show();
              if(result[1].length ==1) selected = "selected";
              if(result[4].length ==1) uselected = "selected";
              if(result[3].length ==1) oselected = "selected";
              $('#building_id').append('<option value="">'+ 'Select Building' +'</option>')
              $.each(result[1], function(key, value) {
                if(result[1].length ==1) $('#buildings').val( value['id']);
                

                $('#building_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
              });
              $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
              $.each(result[4], function(key, value) {
               
                $('#unit_id').append('<option value="'+ value['id'] +'" '+ uselected +'>'+ value['name'] +'</option>');
              });
              $('#occupant_name').empty();
              $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
              $.each(result[3], function(key, value) {
                //alert(value['building_id']);
                
                $('#occupant_name').append('<option value="'+ value['id'] +'" '+ oselected +'>'+ value['name'] +'</option>');
              }); 

              var building = $("#building_id").val(); 
              var unit = $("#unit_id").val();
              var ocselected = '';
              if(building !="" && unit != ""){
                $.ajax({
                  method: "POST",
                  url: "{{route('contractDetailsByBuildingUnit')}}",
                  data: { building: building, unit : unit , 
                    "_token" : $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                      var results = $.parseJSON(data);
                      if(results[5] != null){ocselected = "selected";
                      $('#occupant_name').empty();
                      $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
                      
                      //alert(value['building_id']);
                      
                      $('#occupant_name').append('<option value="'+ results[5].id +'" '+ ocselected +'>'+ results[5].occupant_name +'</option>');
                    }
                    var status = results[0].status;
                    if(result[3] != ""){
                      $('#way_no').val(results[3].building_address);
                      $('#location_name').val(results[4].locations_name);                               
                      $('#tenant_location_id').val(results[3].location_id);
                      var buil_status = result[3].building_maintenance_info;
                      
                    }
                    var tenantStatus = results[1].status;
                    if(buil_status != null){
                      switch(buil_status){
                        case 0:
                        $("#tenantStatus").val(0); 
                        $(".tenantStatus").hide();
                        break;
                        case 1:
                        $(".tenantStatus").show();
                        $("#tenantStatus").val(1);  
                        $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                        break;
                      }
                      
                    } if(status != null){
                      switch(status){
                        case 0:
                        $("#tenantStatus").val(0); 
                        $(".tenantStatus").hide();
                        break;
                        case 2:
                        $("#tenantStatus").val(2);
                        $(".tenantStatus").show(); 
                        $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                        break;
                        case 3: 
                        $("#tenantStatus").val(3);
                        $(".tenantStatus").show();
                        $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                        break;
                        case 4: 
                        $("#tenantStatus").val(4);
                        $(".tenantStatus").show();
                        $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                        break;
                        case 5: 
                        $("#tenantStatus").val(5);
                        $(".tenantStatus").show();
                        $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                        break;
                      }
                    }else{
                      switch(tenantStatus){
                        case 0:
                        $("#tenantStatus").val(0); 
                        $(".tenantStatus").hide();
                        break;
                        case 6:
                        $("#tenantStatus").val(6);
                        $(".tenantStatus").show(); 
                        $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                        break;
                      }
                    }
                    
                    
                  }
                });
                $(".read").attr('readonly',true);
              }              
            }else{//alert(data);                               
              $('#tenant_name').val('');
              $('#complainer_name').val('');
              $('#way_no').val('');
              $('#complaint_mob_no').val("");
              $('#location_name').val("");                               
              $('#tenant_location_id').val(""); 
              $('#building_text_id').val("");
              $('#building_text').val(""); 
              $('#unit_id').empty(); 
              $('#building_id').empty();
              $('#buildings').val("");
              $('#occupant_name').empty();
              $('#tenant_name').val(""); 
              $('#resident_card_id').val("");
                //$('#registerd_mob_no').val("");
                
                /*$('#registerd_mob_no-error').show();*/
                var validator = $( "#maintenance-form" ).validate();
                validator.showErrors({
                  "registerd_mob_no": "It's Not A Registered Mobile Number,Please Try Again!"
                });
                $('#complaint_mob_no').val("");
                $('#tenant_id').val("");
                $('.building_text').show();
                $('.building_select').hide();
                $(".tenantStatus").hide();
                $("#tenantStatus").val('');
                
              }             
            }           
          });
}

});
/***************************************************************************************/
$("#resident_card_id").on('change keyup input',function(e){
  var mobilenumber = $('#registerd_mob_no').val();
  var resident_card_id = $('#resident_card_id').val();
  var mobile_size = mobilenumber.length;
  var occupant_no = $('#occupant_no').val();
  var selected = "";
  var uselected = "";
  var oselected = "";
  if(resident_card_id !=""){
    $.ajax({
      method: "POST",
      url: "{{route('maintenanceContractDetails')}}",
      data: { resident_card_id:resident_card_id, occupant_no : occupant_no , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
         
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
              $('#registerd_mob_no-error').hide();
              $('#tenant_name').val(result[0].tenant_name); 
              $('#resident_card_id').val(result[0].resident_id);
              $('#registerd_mob_no').val(result[0].tenant_contact_no);
              $('#complaint_mob_no').val(result[0].tenant_contact_no);
              $('#tenant_id').val(result[0].id);
              $('#complainer_name').val(result[0].tenant_name);
              
              $('#building_id').empty();
              $('#unit_id').empty();
              if(result[5] != ""){
                $('#location_name').val(result[6].locations_name);
                $('#tenant_location_id').val(result[5].location_id);
                $('#way_no').val(result[5].building_address);
                var buil_status = result[5].building_maintenance_info;
                if(buil_status == 1){
                  $(".tenantStatus").show(); 
                  $("#tenantStatus").val(1); 
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>'); 
                }else{
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                }
              }
              
              

              $('.building_text').hide();
              $('.building_select').show();
              $('.unit_select').show();
              if(result[1].length ==1) selected = "selected";
              if(result[4].length ==1) uselected = "selected";
              if(result[3].length ==1) oselected = "selected";
              $('#building_id').append('<option value="">'+ 'Select Building' +'</option>')
              $.each(result[1], function(key, value) {
                if(result[1].length ==1) $('#buildings').val( value['id']);

                $('#building_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
              });
              $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
              $.each(result[4], function(key, value) {
               
                $('#unit_id').append('<option value="'+ value['id'] +'" '+ uselected +'>'+ value['name'] +'</option>');
              });
              $('#occupant_name').empty();
              $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
              $.each(result[3], function(key, value) {
                //alert(value['building_id']);
                
                $('#occupant_name').append('<option value="'+ value['id'] +'" '+ oselected +'>'+ value['name'] +'</option>');
              }); 

              var building = $("#building_id").val();
              var unit = $("#unit_id").val();
              var ocselected = '';
              if(building !="" && unit != ""){
                $.ajax({
                  method: "POST",
                  url: "{{route('contractDetailsByBuildingUnit')}}",
                  data: { building: building, unit : unit , 
                    "_token" : $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                      var results = $.parseJSON(data);
                      if(results[5] != null){ocselected = "selected";
                      $('#occupant_name').empty();
                      $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
                      
                      //alert(value['building_id']);
                      
                      $('#occupant_name').append('<option value="'+ results[5].id +'" '+ ocselected +'>'+ results[5].occupant_name +'</option>');
                    }
                    var status = results[0].status;
                    if(result[3] != ""){
                      $('#way_no').val(results[3].building_address);
                      $('#location_name').val(results[4].locations_name);                               
                      $('#tenant_location_id').val(results[3].location_id);
                      var buil_status = result[3].building_maintenance_info;
                    }
                    var tenantStatus = results[1].status;
                    if(buil_status != null){
                      switch(buil_status){
                        case 0:
                          //$("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 1:

                          $(".tenantStatus").show();
                          $("#tenantStatus").val(1);  
                          $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                          break;
                        }
                        
                      } if(status != null){
                        switch(status){
                          case 0:
                          $("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 2:
                          $("#tenantStatus").val(2);
                          $(".tenantStatus").show(); 
                          $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                          break;
                          case 3: 
                          $("#tenantStatus").val(3);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                          break;
                          case 4: 
                          $("#tenantStatus").val(4);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                          break;
                          case 5: 
                          $("#tenantStatus").val(5);
                          $(".tenantStatus").show();
                          $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                          break;
                        }
                      }else{
                        switch(tenantStatus){
                          case 0:
                          $("#tenantStatus").val(0); 
                          $(".tenantStatus").hide();
                          break;
                          case 6:
                          $("#tenantStatus").val(6);
                          $(".tenantStatus").show(); 
                          $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                          break;
                        }
                      }
                      
                      
                    }
                  });
$(".read").attr('readonly',true);
}              
            }else{//alert(data);                               
              $('#tenant_name').val('');
              $('#complainer_name').val('');
              $('#way_no').val('');
              $('#complaint_mob_no').val("");
              $('#location_name').val("");                               
              $('#tenant_location_id').val(""); 
              $('#building_text_id').val("");
              $('#building_text').val(""); 
              $('#unit_id').empty(); 
              $('#building_id').empty();
              $('#buildings').val("");
              $('#occupant_name').empty();
              $('#tenant_name').val(""); 
                //$('#resident_card_id').val("");
                $('#registerd_mob_no').val("");
                
                /*$('#registerd_mob_no-error').show();*/
                var validator = $( "#maintenance-form" ).validate();
                validator.showErrors({
                  "resident_card_id": "It's Not A Registered Resident Id,Please Try Again!"
                });
                $('#complaint_mob_no').val("");
                $('#tenant_id').val("");
                $('.building_text').show();
                $('.building_select').hide();
                $(".tenantStatus").hide();
                $("#tenantStatus").val('');
                
              }             
            }           
          });
}

});
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
$("#occupant_no").on('change keyup input',function(e){
 
  var occupant_no = $('#occupant_no').val();
  var mobile_size = occupant_no.length;

  if(mobile_size  >  7 ){
    $.ajax({
      method: "POST",
      url: "{{route('contractSearchByOccupant')}}",
      data: { occupant_no : occupant_no , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
         
            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
              //$('#occupant_name').val(result[0].occupant_name); 
              $('#occupant_name').append('<option value="'+ result[0].id +'" "selected">'+ result[0].occupant_name +'</option>');
              
            }else{                               
              $('#occupant_name').val('');                              
              
            }             
          }           
        });
  }

});
/*************************************************************************/
$("#occupant_name").on('change',function(e){

  var occupant_id = $('#occupant_name').val();

  if(occupant_id != "") {
    $.ajax({
      method: "POST",
      url: "{{route('contractSearchByOccupant')}}",
      data: { occupant_id : occupant_id , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          
          if(data != 0){
            var result = $.parseJSON(data);
            //$('#occupant_name').val(result[0].occupant_name); 
            $('#occupant_no').val(result[0].occupant_primary_contact_no);
            $('#occupant_id').val(result[0].id);
            
          }else{                               
            $('#occupant_no').val('');  
            $('#occupant_id').val('');                              
            
          }             
        }           
      });
  }
  

});
/*************************************************************************/
$(document).on('change',".Building", function(){
        //alert(55);
        var id = $("#building_id").val();
        var tenant = $("#tenant_id").val();
        $('#buildings').val(id);
        var selected = '';
        $.ajax({
          type: "POST",
          /*url: "{{url('/tenantContract/buildingByUnitOccuiped')}}",
          data: {"id":id,"_token": "{{ csrf_token() }}"},*/
          url: "{{route('complaintBuildingByUnitOccuiped')}}",
          data: {"id":id,"tenant":tenant,"_token": "{{ csrf_token() }}"},
          cache: false,
          //dataType: "json",
          success: function(data)
          {
            var result = $.parseJSON(data);
            if(result[0].length > 0){
              if(result[0].length ==1){selected = "selected";}
              $('#unit_id').empty();
              $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
              $.each(result[0], function(key, value) {
                $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
              });
            }
            else{
              $('#unit_id').html('<option value="">No Data</option>');
            }
            $('#way_no').val(result[2].building_address);
            $('#tenant_location_id').val(result[2].location_id);
            $('#location_name').val(result[1].locations_name);
            var buil_status = result[2].building_maintenance_info;
            if(buil_status != null){
              switch(buil_status){
                case 0:
                  //$("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 1:
                  $(".tenantStatus").show();
                  $("#tenantStatus").val(1);  
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                  break;
                }
                
              }
            } 
          });
        $.ajax({
          type: "POST",
          url: "{{url('/complaint/getBuildingDetail')}}",
          data: {"id":id,"_token": "{{ csrf_token() }}"},
          cache: false,
              //dataType: "json",
              success: function(data)
              {
                var result = $.parseJSON(data);
                $('#building_name').val(result[0].building_name);
                $('#way_no').val(result[0].building_address);
                $('#tenant_location_id').val(result[0].location_id);
                $('#location_name').val(result[1].locations_name);
                
              } 
            });
        
        
      });
/*************************************************************************/
$(document).on('change',".complaintUnit", function(){
  var id = $("#building_text_id").val();
  if(id == null){id = $("#building_id").val();}
  var unit = $("#unit_id").val();
  var ocselected = '';
  if(id !="" && unit != ""){
    $.ajax({
      method: "POST",
      url: "{{route('contractDetailsByBuildingUnit')}}",
      data: { building: id, unit : unit , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          var results = $.parseJSON(data);
          $('#registerd_mob_no-error').hide();
          $('#tenant_name').val(results[1].tenant_name); 
          $('#resident_card_id').val(results[1].resident_id);
          $('#registerd_mob_no').val(results[1].tenant_contact_no);
          $('#complaint_mob_no').val(results[1].tenant_contact_no);
          $('#tenant_id').val(results[1].id);
          $('#complainer_name').val(results[1].tenant_name);
          //$('#way_no').val(results[1].tenant_contact_address);
          if(results[2]!=null){
            //$('#location_name').val(results[2].locations_name);
            //$('#tenant_location_id').val(results[2].id);

          }
          if(results[5] != null){ocselected = "selected";}
          $('#occupant_name').empty();
          $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
          
            //alert(value['building_id']);
            
            $('#occupant_name').append('<option value="'+ results[5].id +'" '+ ocselected +'>'+ results[5].occupant_name +'</option>');
            
            $('#way_no').val(results[3].building_address);
            $('#location_name').val(results[4].locations_name);                               
            $('#tenant_location_id').val(results[3].location_id);

            $(".read").attr('readonly',true);
            var status = results[0].status;
            var tenantStatus = results[1].status;
            var buil_status = results[3].building_maintenance_info;
            if(buil_status != null){
              switch(buil_status){
                case 0:
                  //$("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 1:
                  $(".tenantStatus").show();
                  $("#tenantStatus").val(1); 
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                  break;
                }
                
              } if(status != null){
                switch(status){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 2:
                  $("#tenantStatus").val(2);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                  break;
                  case 3: 
                  $("#tenantStatus").val(3);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                  break;
                  case 4: 
                  $("#tenantStatus").val(4);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                  break;
                  case 5: 
                  $("#tenantStatus").val(5);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                  break;
                }
              }else{
                switch(tenantStatus){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 6:
                  $("#tenantStatus").val(6);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                  break;
                }
              }    
              
            }
          });
}
});

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
          console.log("drop");
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
          console.log("over");
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
            console.log("drop");
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
    
    
    console.log("drop1");
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
  console.log("over");
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
      console.log("drop1");
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


