<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">



  <style>
  #sortable1, #sortable2 {
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
  #sortable1 li, #sortable2 li {
    
    padding: 5px 10px;
    font-size: 14px;
    width: 100%;
  }
  #sortable1 li:hover, #sortable2 li:hover {
      background-color: #32ab56;
      color: #fff;
  }


   #sortablePriceRange1, #sortablePriceRange2 {
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
  #sortablePriceRange1 li, #sortablePriceRange2 li {
   
    padding: 5px 10px;
    font-size: 14px;
    width: 100%;
  }
#sortablePriceRange1 li:hover, #sortablePriceRange2 li:hover {
  background-color: #32ab56;
      color: #fff;
}


  </style>

<div class="row">  

<!-- activities -->
<div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
            <header class="panel-heading panel-heading-gray custom-tab ">
                <ul class="nav nav-tabs">
                    @if((isset($enquiry) && $enquiry->sales_type == 1) || !isset($enquiry)) 
                    <li class="nav-item"><a href="#tenant" data-toggle="tab"class="{{(old('sales_type') <= 1)? 'active': ''}}" >Tenant</a>
                    </li>
                    @endif

                    @if((isset($enquiry) && $enquiry->sales_type == 2) || !isset($enquiry)) 
                    <li class="nav-item"><a href="#landlord" data-toggle="tab" class="{{(old('sales_type',isset($enquiry)? $enquiry->sales_type : ''  ) == 2)? 'active': ''}}">Landlord</a>
                    </li>
                    @endif                                       
                </ul>
            </header>
                                <div class="panel-body">
                                    <div class="tab-content">                                     
@if((isset($enquiry) && $enquiry->sales_type == 1) || !isset($enquiry)) 
        <div class="tab-pane {{(old('sales_type') <= 1)? 'active': ''}}" id="tenant">
          <form method="post" id="tenant-form" action="{{isset($enquiry)? route( 'enquiry.update',$enquiry->id) : route( 'enquiry.store')}}">
            @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
            <input type="hidden" name="sales_type" value="1">
            <div class="clearfix"></div>
            <div class="dataSearchBox">
                <div class="row">
      <div class="col-sm-3">
                <div class="form-group">
                    <label for="sales_mobile_no">Mobile No<small class="textRed">*</small></label>
                    <input pattern="^((\+)?(\d{2,3}))?(\d{8}){1}?$" required type="text" class="form-control" id="sales_mobile_no"  name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no : ''  )}}"  placeholder="Enter Mobile No">
                </div>
      </div>

 			<div class="col-sm-3">
                <div class="form-group">
                    <label>Region<small class="textRed">*</small></label>
                    <select required name="sales_region" class="form-control">
                        <option value="">Select</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 1)? 'selected':'selected' }}   value="1">Local</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : '') == 2)? 'selected':'' }}   value="2">International</option>
                    </select>
                </div>
      </div>
         <div class="col-sm-6">
                <div class="form-group">
                    <label for="sales_enquiry_name">Customer Name<small class="textRed">*</small></label>
                    <input required pattern="[A-Za-z\s]+" type="text" name="sales_enquiry_name" class="form-control" id="sales_enquiry_name"  value="{{old('sales_enquiry_name',isset($enquiry)? $enquiry->sales_enquiry_name : ''   ) }}" placeholder="Enter Customer Name">
                </div>
            </div>

			<div class="w-100"></div>

           
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="sales_enquiry_no">Enquiry No</label>
                    <input type="text" name="sales_enquiry_no" disabled=""  class="form-control" id="sales_enquiry_no" value="{{ isset($enquiry)? $enquiry->sales_enquiry_no : $nextTenantCode}}">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="simpleFormEmail">Enquiry Date</label>
                    <input type="text" disabled="" class="form-control" id="simpleFormEmail" value="{{ isset($enquiry)? $enquiry->created_at->format('d/m/Y') : today()->format('d/m/Y') }}" placeholder="">
                </div>
            </div>

             <div class="col-sm-6">
              <div class="form-group">
                  <label for="sales_email">Customer Email</label>
                  <input type="email"  name="sales_email" class="form-control" id="sales_email" value="{{old('sales_email',isset($enquiry)? $enquiry->sales_email : '')}}" placeholder="Email Address">
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
        <label class="col no-padding" for="sales_unit_type_id">Unit type<small class="textRed">*</small></label>         
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
        <ul id="sortable1" class="connectedSortable">
             @foreach($source_unitTypes as $units)
              <li id="{{$units->id}}" class="list_item">{{$units->unit_types_name}}</li>
              @endforeach
             
        </ul> 
       <div class="sort_arrow-l ui-state-disabled">  
			        <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
		  </div>
        <ul id="sortable2" class="connectedSortable">
           <li class="ui-state-disabled">(Drag and drop unit types)</li>
            @if(isset($destination_unitTypes))
              @foreach($destination_unitTypes as $units)
              <li id="{{$units->id}}" class="list_item">{{$units->unit_types_name}}</li>
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
        <ul id="sortablePriceRange1" class="connectedPriceRange">
             @foreach($source_priceRanges as $priceRange)
              <li id="{{$priceRange->id}}" class="list_item">{{$priceRange->price_ranges_name}}</li>
              @endforeach
              <!--  <div class="sort_arrow-l">  
			        <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
		        </div>  -->
        </ul>
          <div class="sort_arrow-l ui-state-disabled">  
			        <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
		  </div>
        <ul id="sortablePriceRange2" class="connectedPriceRange">
          <li class="ui-state-disabled">(Drag and drop Price Range)</li>
             @if(isset($destination_priceRanges))
              @foreach($destination_priceRanges as $priceRange)
              <li id="{{$priceRange->id}}" class="list_item">{{$priceRange->price_ranges_name}}</li>
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
        <label for="sales_no_of_unit">No. of units<small class="textRed">*</small></label>
        <input min="1" required type="number" class="form-control" id="sales_no_of_unit" name="sales_no_of_unit" value="{{old('sales_no_of_unit',isset($enquiry)? $enquiry->sales_no_of_unit : '1')}}" placeholder="">
     </div>
    </div>

<div class="col-sm-6">
  <div class="form-group autocomplete-cls">
    <label>List of Predefined Areas<small class="textRed">*</small></label>
        @php
        if(old('location_id')){       
         $locations_sales = $locations->whereIn('id',old('location_id'));

       }elseif(isset($enquiry)){
         $location_val = $enquiry->locations()->pluck('location_id');   
         $location_val = (array)array_flatten($location_val); 
         $locations_sales = $locations->whereIn('id',$location_val);
        }
        @endphp  
    <select id="location_id" required name="location_id[]" class="tokenize-remote-demo1 form-group" multiple>      
        @isset($locations_sales)
        @foreach($locations_sales as $location)
        <option selected value="{{$location->id}}">{{$location->locations_name}}</option>
        @endforeach
        @endif
    </select>
  </div>
</div>                                              
        

<div class="col-sm-6">
    <div class="form-group">
        <label for="sales_size">Square Meter</label>
         <input type="text" class="form-control" name=
         "sales_size" id="sales_size" placeholder="Enter Square Meter" value="{{old('sales_size',isset($enquiry)? $enquiry->sales_size : '')}}" >
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
       <label>Tenant type</label>
        <select name="tenant_type_id" class="form-control">
            <option value="">Select</option>
            @foreach($tenantTypes as $tenant_types)
            <option {{ (old('tenant_type_id',isset($enquiry)?  $enquiry->tenant_type_id : '' ) == $tenant_types->id)?  'selected':''  }}   value="{{$tenant_types->id}}">{{$tenant_types->tenant_types_name}}</option>
            @endforeach
        </select> 
    </div> 
</div>
 

    <div class="col-sm-6">
        <div class="form-group">
            <label for="alternative_no">Alternative No</label>
             <input type="text" class="form-control" name=
             "alternative_no" id="alternative_no" placeholder="Enter Alternative No" value="{{old('alternative_no',isset($enquiry)? $enquiry->alternative_no : '')}}">
        </div>
    </div>
                                                     
     <div class="col-sm-6">
        <div class="form-group">
            <label for="sales_referred_by">Referred By</label>
            <input type="text" class="form-control" name="sales_referred_by" id="sales_referred_by" placeholder="Enter Referred By" value="{{old('sales_referred_by',isset($enquiry)? $enquiry->sales_referred_by : '')}}">
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
          <label for="sales_move_in_date">Move in date<small class="textRed">*</small></label>

            <div class="row">
             <div class="col-sm-6 ">
                <select required  class="form-control" name="month" >
                  <option value="">Select Month </option>
                  @php
                   if(isset($enquiry)){
                   $month = \Carbon\Carbon::parse($enquiry->sales_move_in_date)->month; 
                   }else $month = 0;
                   @endphp
                  @for($m=1; $m<=12; ++$m)
                  <option {{(old('month',$month) == $m)? 'selected' :''  }} value="{{$m}}" >{{date('F', mktime(0, 0, 0, $m, 1))}}</option>
                  @endfor
                </select>
              </div>
             <div class="col-sm-6 "> 
                <select required class="form-control" name="year">
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
               <!--  <input type="date" required name="sales_move_in_date" class="form-control" id="sales_move_in_date" placeholder="Enter Move in date" value="{{old('sales_move_in_date',isset($enquiry)? $enquiry->sales_move_in_date->format('Y-m-d') : '')}}"> -->
            </div>
         
    </div>

    <div class="col-sm-6">
      <div class="form-group">
        <label>Source<small class="textRed">*</small></label>
        <select required name="sales_mode_id" class="form-control">
            <option value="">Select</option>
            @foreach($enquirySources as $enquirySource)
            <option {{ (old('sales_mode_id',isset($enquiry)?  $enquiry->sales_mode_id : '') == $enquirySource->id)?  'selected':''  }}  value="{{$enquirySource->id}}">{{$enquirySource->enquiry_sources_name}}</option>
            @endforeach
        </select> 
      </div> 
    </div>

            <div class="w-100"></div>
             <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Remark</label>
                    <textarea class="form-control" name="sales_note" rows="2" placeholder="Enter Description">{{old('sales_note',isset($enquiry)? $enquiry->sales_note : '')}}</textarea>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col"><button type="submit" class="btn btn-primary">Submit</button></div>
        </div>
    </div>
                                        </form>

                                        </div>
@endif

@if((isset($enquiry) && $enquiry->sales_type == 2) || !isset($enquiry))  
 <div class="tab-pane {{(old('sales_type',isset($enquiry)? $enquiry->sales_type : ''  ) == 2)? 'active': ''}}" id="landlord">
    
     <form method="post"  id="landlord-form" action="{{isset($enquiry)? route( 'enquiry.update',$enquiry->id) : route( 'enquiry.store')}}">
            @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
            <input type="hidden" name="sales_type" value="2">
            <div class="clearfix"></div>
            <div class="dataSearchBox">
                <div class="row">
                 

                 <div class="col-sm-3">
                        <div class="form-group">
                            <label for="landlord_sales_mobile_no">Mobile No<small class="textRed">*</small></label>
                            <input  pattern="^((\+)?(\d{2,3}))?(\d{8}){1}?$" required type="text" class="form-control" id="landlord_sales_mobile_no"  name="sales_mobile_no" value="{{ old('sales_mobile_no', isset($enquiry)? $enquiry->sales_mobile_no : ''  )}}"  placeholder="Enter Mobile No">
                        </div>
                 </div>


                  <div class="col-sm-3">
                        <div class="form-group">
                            <label>Region<small class="textRed">*</small></label>
                    <select required name="sales_region" class="form-control">
                        <option value="">Select</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 1)? 'selected':'selected' }}   value="1">Local</option>
                        <option {{ (old('sales_region',isset($enquiry)? $enquiry->sales_region : ''   ) == 2)? 'selected':'' }}   value="2">International</option>
                    </select> 
                </div> 
                    </div>  


            <div class="col-sm-6">
                <div class="form-group">
                    <label for="landlord_sales_enquiry_name">Customer Name<small class="textRed">*</small></label>
                    <input type="text" required name="sales_enquiry_name" class="form-control" id="landlord_sales_enquiry_name"  value="{{old('sales_enquiry_name',isset($enquiry)? $enquiry->sales_enquiry_name : ''   ) }}" placeholder="Enter Customer Name">
                </div>
            </div>                                                      
            
           
            <div class="w-100"></div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="landlord_sales_enquiry_no">Enquiry No</label>
                    <input type="text" name="sales_enquiry_no" disabled=""  class="form-control" id="sales_enquiry_no" value="{{ isset($enquiry)? $enquiry->sales_enquiry_no : $nextLandlordCode}}">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="landlord_enquiry_date">Enquiry Date</label>
                    <input type="text" disabled="" class="form-control" id="landlord_enquiry_date" value="{{ isset($enquiry)? $enquiry->created_at->format('d/m/Y') : today()->format('d/m/Y') }}" placeholder="">
                </div>
            </div>

   <div class="col-sm-6">
        <div class="form-group">
            <label for="landlord_simpleFormEmail">Customer Email</label>
            <input type="email"  name="sales_email" class="form-control" id="landlord_simpleFormEmail" value="{{old('sales_email',isset($enquiry)? $enquiry->sales_email : '')}}" placeholder="Email Address">
        </div>
    </div>     

     <div class="w-100"></div>

<!-- <div class="col-sm-6">
        <div class="form-group">
            <label for="landlord_sales_contact_address">Customer Address<small class="textRed">*</small></label>
            <textarea  required name="sales_contact_address" class="form-control" id="landlord_sales_contact_address"  placeholder="Contact Address" >{{old('sales_contact_address',isset($enquiry)? $enquiry->sales_contact_address : '')}}</textarea>            
        </div>
    </div>
        <div class="w-100"></div> -->
      
                                                   
 

    <div class="col-sm-6">
     <div class="form-group">
        <label for="landlord_sales_no_of_unit">No. of units<small class="textRed">*</small></label>
        <input required type="number" min="1" class="form-control" id="landlord_sales_no_of_unit" name="sales_no_of_unit" value="{{old('sales_no_of_unit',isset($enquiry)? $enquiry->sales_no_of_unit : '')}}" placeholder="">
     </div>
    </div>

                                                    
      <div class="col-sm-6">
            <div class="form-group autocomplete-cls">
            <label >List of Predefined Areas<small class="textRed">*</small></label> 
            @php
            if(isset($enquiry)){
             $location_val = $enquiry->locations()->pluck('location_id');   
             $location_val = (array)array_flatten($location_val); 
            }
            @endphp 
             <select required  name="location_id[]"  class="form-control">
                <option value="">Select Areas</option>
                @foreach($locations as $location)
                <option {{ isset($enquiry)? ((array_search($location->id,$location_val) !== false)? 'selected':'' )  :'' }}  {{ (old('location_id') !== null  && (old('sales_type') == 2))? 
  ((array_search($location->id,old('location_id')) !== false)?  'selected':''  ) : '' }} value="{{$location->id}}">{{$location->locations_name}}</option>
                @endforeach
               </select>                 
            </div>
        </div>          


                                                   
           <div class="col-sm-6">
                <div class="form-group">
                    <label >Price Range<small class="textRed">*</small></label>
                    @php
                    if(isset($enquiry)){
                     $price_range_val = $enquiry->priceRanges()->pluck('price_range_id');   
                     $price_range_val = (array)array_flatten($price_range_val); 
                    }
                    @endphp 
                    <select required  name="price_range_id[]"  class="form-control">
                    <option value="">Select Price Range</option>
                    @foreach($priceRanges as $priceRange)
                    <option {{ isset($enquiry)? ((array_search($priceRange->id,$price_range_val) !== false)? 'selected':'' )  :'' }}  {{ (old('price_range_id') !== null && (old('sales_type') == 2))? 
  ((array_search($priceRange->id,old('price_range_id')) !== false)?  'selected':''  ) : '' }}   value="{{$priceRange->id}}">{{$priceRange->price_ranges_name}}</option>
                    @endforeach
                   </select> 
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="sales_size">Square Meter<small class="textRed">*</small></label>
                     <input required type="text" class="form-control" name=
                     "sales_size" id="sales_size" placeholder="Enter Square Meter" value="{{old('sales_size',isset($enquiry)? $enquiry->sales_size : '')}}" >
                </div>
            </div>



    <div class="col-sm-6">
      <div class="form-group">
        <label>Building type<small class="textRed">*</small></label>
        <select required name="building_type_id" class="form-control">
            <option value="">Select</option>
            @foreach($buildingTypes as $buildingType)
            <option {{ (old('building_type_id',isset($enquiry)?  $enquiry->building_type_id : '') == $buildingType->id)?  'selected':''  }}  value="{{$buildingType->id}}">{{$buildingType->building_types_name}}</option>
            @endforeach
        </select> 
      </div> 
    </div>


    <div class="col-sm-6">
        <div class="form-group">
            <label for="landlord_sales_building_name">Building Name</label>
             <input type="text" class="form-control" name=
             "sales_building_name" id="landlord_sales_building_name" placeholder="Enter Building Name" value="{{old('sales_building_name',isset($enquiry)? $enquiry->sales_building_name : '')}}">
        </div>
    </div>
                                                     
     <div class="col-sm-6">
        <div class="form-group">
            <label for="sales_referred_by">Referred By</label>
            <input type="text" class="form-control" name="sales_referred_by" id="landlord_sales_referred_by" placeholder="Enter Referred By" value="{{old('sales_referred_by',isset($enquiry)? $enquiry->sales_referred_by : '')}}">
        </div>
    </div>
   

    <div class="col-sm-6">
      <div class="form-group">
        <label>Mode<small class="textRed">*</small></label>
        <select required name="sales_mode_id" class="form-control">
            <option value="">Select</option>
            @foreach($enquirySources as $enquirySource)
            <option {{ (old('sales_mode_id',isset($enquiry)?  $enquiry->sales_mode_id : '') == $enquirySource->id)?  'selected':''  }}  value="{{$enquirySource->id}}">{{$enquirySource->enquiry_sources_name}}</option>
            @endforeach
        </select> 
      </div> 
    </div>

            <div class="w-100"></div>
             <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Remark</label>
                    <textarea class="form-control" name="sales_note" rows="2" placeholder="Enter Description">{{old('sales_note',isset($enquiry)? $enquiry->sales_note : '')}}</textarea>
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col"><button type="submit" class="btn btn-primary">Submit</button></div>
        </div>
    </div>
                                        </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>           

                    </div>





@section('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script>
  $(document).ready(function() {
    $("#tenant-form").validate({
        ignore: [], 
          
    });
    $("#landlord-form").validate()

 
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

  });


 //Unit Type
$( function() {
    $( "#sortable1, #sortable2" ).sortable({
      connectWith: ".connectedSortable"}).disableSelection();
  } );
$( "#sortable2" ).on( "sortbeforestop", function( event, ui ) {
   var myarr = [];
 
   $("#sortable2 .list_item").each(function(){
       if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
    $('.unittype').val(myarr.toString());

    if(myarr.toString() != '')
    $('#sales_unit_type_id-error').hide();
    else
       $('#sales_unit_type_id-error').show();

});
$( "#sortable2" ).on( "sortreceive", function( event, ui ) {
   var myarr = [];
 
   $("#sortable2 .list_item").each(function(){
      if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
     $('.unittype').val(myarr.toString());

      if(myarr.toString() != '')
      $('#sales_unit_type_id-error').hide();
       else
       $('#sales_unit_type_id-error').show();
});
/***sortablePriceRange1***/

$( function() {
    $( "#sortablePriceRange1, #sortablePriceRange2" ).sortable({
      connectWith: ".connectedPriceRange"}).disableSelection();
  } );
$( "#sortablePriceRange2" ).on( "sortbeforestop", function( event, ui ) {
   var myarr = [];
 
   $("#sortablePriceRange2 .list_item").each(function(){
       if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
    $('.pricerange').val(myarr.toString());

     if(myarr.toString() != '')
      $('#price_range_id-error').hide();
     else
       $('#price_range_id-error').show();

});



$( "#sortablePriceRange2" ).on( "sortreceive", function( event, ui ) {
   var myarr = [];
 
   $("#sortablePriceRange2 .list_item").each(function(){
      if($(this).attr('id'))
        myarr[myarr.length] = $(this).attr('id');       
    });
     $('.pricerange').val(myarr.toString());

      if(myarr.toString() != '')
      $('#price_range_id-error').hide();
     else
       $('#price_range_id-error').show();
});



$('.tokenize-remote-demo1').tokenize2({
      dataSource: '{{route("location.locationAutocomplete")}}',
      placeholder: "Type the letter for prefered location"
});


@if(old('sales_type') == 1 )
  $('#landlord-form').find("input[type=text], textarea , select").val("");
@elseif(old('sales_type') == 2)  
  $('#tenant-form').find("input[type=text], textarea , select").val("");
@endif


 

</script>
@endsection


