@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('content')


<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ucwords($type)}} Enquiry</div>
    </div>
    {{ ($type == 'tenant')?   Breadcrumbs::render('enquiry.tenant') : Breadcrumbs::render('enquiry.landlord') }}
  </div>
</div>
<!-- start widget -->

<!-- end widget -->
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
  <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="card card-box salesSearchBox">


     <div class="panel-body">
      <div class="dataSearchBox ">
        <form action="{{url()->current()}}" method="POST" id="leade_search" class="form-horizontal"  da ta-toggle="validator">
          {{csrf_field()}}
          <div class="row">

            <div class="col-sm-12">

              <div id="search_form" class="">  
               <!------------------------------>

               @if(old('fieldName', null) != null)

               @php
               $size = count(old('fieldName'));
               @endphp

               @for($i = 0; $i < $size ; $i++) 

               <div class="row">

                <div class="col-sm-3">
                  <div class="form-group">
                    <label for="fieldName">Field</label>
                    <div class="p-relative">
                     <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
                     <select class="form-control fieldName" required name="fieldName[]">
                      <option value="">Select </option>
                      @foreach($enquiry_fields as $key=>$val)
                      <option  {{ (old('fieldName')[$i] == $key)? 'selected' : '' }}   value="{{$key}}">{{$val}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>


              <div class="col-sm-2">
                <div class="form-group">
                  <label for="fieldValue"> Operation</label>
                  <div class="p-relative">
                   <i class="fa fa-arrows-v icn-add" aria-hidden="true"></i>
                   <select class="form-control operation" required name="operation[]">
                    <option  value="">Select </option>  
                    @foreach($operations as $operation_key=>$operation_val)
                    <option  {{ (old('operation')[$i] == $operation_key)? 'selected' : '' }}   value="{{$operation_key}}">{{$operation_val}}</option>
                    @endforeach                                          
                  </select>                                        
                </div>
              </div>
            </div>


            <div class="col-sm-3">
              <div class="form-group">
                <label for="fieldValue"> Value</label>
                <div class="p-relative">
                 <i class="fa fa-yahoo icn-add" aria-hidden="true"></i>
                 <input autocomplete="off" required type="{{ (in_array(old('fieldName')[$i],array('created_at','sales_move_in_date')) == true)? 'date' : 'text'}}" class="form-control fieldValue" name="fieldValue[]"  placeholder="Enter Value" value="{{old('fieldValue')[$i]}}">
               </div>
             </div>
           </div>

           <div class="col-sm-2">                                    

             <div class="form-group">
              <label for="fieldValue"> AND/OR</label>
              <select class="form-control and_search logic" name="logic[]">                                         
                <option {{ (old('logic')[$i] == 'and')? 'selected' : '' }}  value="and">AND</option>
                <option {{ (old('logic')[$i] == 'or')? 'selected' : '' }}  value="or">OR</option>
              </select>                                        
            </div> 

          </div>  


          <div class="col-sm-1">   
           @if($i > 0)
           <div class="dataSearchLabel w-100 margin"></div> 
           <button type="button"  class="btn btn-primary remove_search margin">Remove</button>
           @else                                  
           <div class="dataSearchLabel w-100"></div>
           <button type="button" class="btn btn-primary add_search margin">ADD </button> 
           @endif
         </div>                             


         <div class="w-100"></div>
       </div>  

       @endfor 

       @else 





       <div class="row">                           
        <div class="col-sm-3">
          <div class="form-group">
            <label for="fieldName">Field</label>
            <div class="p-relative">
             <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
             <select class="form-control fieldName" required name="fieldName[]">
              <option value="">Select </option>
              @foreach($enquiry_fields as $key=>$val)
              <option  value="{{$key}}">{{$val}}</option>
              @endforeach
            </select>
          </div></div>
        </div>


        <div class="col-sm-2">
          <div class="form-group">
            <label for="fieldValue"> Operation</label>
            <div class="p-relative">
             <i class="fa fa-arrows-v icn-add" aria-hidden="true"></i>
             <select class="form-control operation" required name="operation[]">
               <option value="">Select </option>   
               @foreach($operations as $operation_key=>$operation_val)
               <option value="{{$operation_key}}">{{$operation_val}}</option>
               @endforeach    
             </select>                                        
           </div> </div>
         </div>


         <div class="col-sm-3">
          <div class="form-group">
            <label for="fieldValue"> Value</label>
            <div class="p-relative">
             <i class="fa fa-yahoo icn-add" aria-hidden="true"></i>
             <input autocomplete="off" required type="text" class="form-control fieldValue" name="fieldValue[]"  placeholder="Enter Value" value="">
           </div>
         </div>
       </div>

       <div class="col-sm-2">

         <div class="form-group">
          <label for="fieldValue"> AND/OR</label>
          <div class="p-relative">
           <i class="fa fa-vine icn-add" aria-hidden="true"></i>
           <select class="form-control and_search logic" name="logic[]">                                       
            <option value="and">AND</option>
            <option value="or">OR</option>
          </select>                                        
        </div>
      </div>
    </div>


    <div class="col-sm-1">
      <div class="dataSearchLabel w-100"></div> 
      <button type="button" class="btn btn-primary add_search margin">ADD </button>
    </div>





    <div class="w-100"></div>
  </div>
  <!------>

  @endif
</div>                             

</div>    


<div class="col-sm-1">
  <div class="dataSearchLabel w-100"></div>
  <button type="submit" class="btn btn-primary">Search</button>
</div>
<div class="col-sm-1">
  <div class="dataSearchLabel w-100 "></div>
  <a href="{{url()->current()}}" class="btn btn-primary">Reset</a>
</div>
</div>
</form>
</div>
</div>



</div>
</div>
</div>


<div class="row">
  <div class="col-md-12 col-sm-12">


    <div class="card card-box">


     <div class="card-body " >
       <h4>
       <div id="pagination_info">
         @include('includes.pagination_info',['paginator' => $enquiries])         
       </div>

       <a href="{{route('enquiry.create',($type == 'tenant')?  'type=1&#tenant' : 'type=2&#landlord')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>   
       <div class="clr"></div></h4>





       <div class="table-wrap">
         <div class="table-responsive1">
          <table class="table display product-overview mb-30" id="dtBasicExample">
            <thead>

              <tr>
                <th title="Enquiry no">@sortablelink('sales_enquiry_no','Enq No',[],[ 'class' => 'sort_url'])</th>
                <th title="Enquiry date">@sortablelink('created_at','Enq Dt',[],[ 'class' => 'sort_url'])</th>
                @if($type == 'tenant')
                <th title="First call attended">@sortablelink('fc_att','Fc Att',[],[ 'class' => 'sort_url'])</th>
                @endif
                <th title="Customer">@sortablelink('cust','Customer',[],[ 'class' => 'sort_url'])</th>
                <th title="Mobile no">@sortablelink('cust_no','Mob',[],[ 'class' => 'sort_url'])</th>	
                @if($type == 'tenant')
                <th title="Unit">@sortablelink('unit_type','Unit',[],[ 'class' => 'sort_url'])</th>	 
                <th title="Location">@sortablelink('loc','Loc',[],[ 'class' => 'sort_url']) </th>
                @endif
                <th title="Status">@sortablelink('enquiry_flow','Status',[],[ 'class' => 'sort_url'])</th>  
                <th title="Remark">@sortablelink('sales_note','Last Stage Note',[],[ 'class' => 'sort_url'])</th>
                <th title="Creator" >Created By</th>
                <th title="Action" width="15%">Action</th>
              </tr>
              <tr> 
               <td><input autocomplete="off" type="text" name="sales_enquiry_no" class="search_fields" id="sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
               <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
               @if($type == 'tenant')
               <td>
                <select name="fc_att" class="searchFields search_fields mob" id="fc_att" >
                  <option value="">Show All</option>
                  <option {{ (old('fc_att') == 'Yes')? 'selected' : '' }} value="Yes">Yes</option>
                  <option {{ (old('fc_att') == 'No')? 'selected' : '' }} value="No">No</option>

                </select>
              </td>	
              @endif  
              <td><input autocomplete="off" type="text" name="customer_name" class="search_fields" id="sales_enquiry_name" value="{{old('sales_enquiry_name')}}" ></td>
              <td><input autocomplete="off" type="text" name="sales_mobile_no" class="search_fields mob" id="sales_mobile_no"  value="{{old('sales_mobile_no')}}" ></td>
              @if($type == 'tenant')
              <td><input autocomplete="off" type="text" name="unitTypes" class="search_fields mob" id="unit"  value="{{old('unitTypes__unit_types_name')}}" ></td>
              <td><input autocomplete="off" type="text" name="location" class="search_fields mob" id="location"  value="{{old('locations__locations_name')}}" ></td>
              @endif
              <td>
               <select name="stages" class="searchFields search_fields" id="stages" style="width:100px;">
                <option value="">Show All</option>
                @foreach($stages as $stage)
                <option {{ (old('work_flow_processes_code') == $stage->work_flow_processes_code)? 'selected' : '' }} value="{{$stage->work_flow_processes_code}}">{{$stage->work_flow_processes_name}}</option>
                @endforeach
                <option {{ (old('work_flow_processes_code') == 'RJCT')? 'selected' : '' }} value="RJCT">Referred Back</option>
              </select>
            </td>
            <td><input autocomplete="off" type="text" name="sales_note" class="search_fields" id="sales_note" value="{{old('sales_note')}}" ></td>
            <td><input autocomplete="off" type="text" name="created_by" class="search_fields" id="created_by" value="{{old('employee_name')}}" ></td>
            <td></td>
          </tr>
        </thead>
        <tbody id="enquiry-search">
          @include('sales::enquiry_list_search')
          
        </tbody>
      </table>
    </div>

    <div  id="pagination">
      <div class="text-center"> 							                
       {{$enquiries->appends(\Request::except(['page','_token']))->links()}}
     </div>
   </div>
 </div>
</div>
</div>

</div>
</div>


</div>
</div>

@endsection




@section('scripts')
<script>
	(function($){

    $.fn.focusTextToEnd = function(){
      this.focus();
      var $thisVal = this.val();
      this.val('').val($thisVal);
      return this;
    }
  }(jQuery));

 $(document).ready(function(){
  $("#show").on("hide.bs.collapse", function(){
    $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
  });
  $("#show").on("show.bs.collapse", function(){
    $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
  });
  /**********************************************************************************/

  // var type = <?php echo json_encode($type)?>;
  // if(type == 'landlord'){
  //   $('table tr').find('td:eq(2),th:eq(2)').remove();
  //   $('table tr').find('td:eq(4),th:eq(4)').remove();
  //   $('table tr').find('td:eq(4),th:eq(4)').remove();

  // }
  $(document).on('change','.fieldName',function(){ 
    var fieldValue = $(this).val() ;

    if( fieldValue == 'sales_move_in_date' ||  fieldValue  == 'created_at'){  
     $(this).closest('.row').find('.fieldValue').attr('type','date');
   }else{
    $(this).closest('.row').find('.fieldValue').attr('type','text');
  }

});
  $(document).on('change keyup paste','.search_fields',function(){ 
    var type = "{{$type}}";
    var focus = $(this).attr('id'); 
    var sales_enquiry_no 		= $("#sales_enquiry_no").val();
    var created_at 			= $("#created_at").val();
    var fc_att 				= $("#fc_att").val();
    var sales_enquiry_name	= $("#sales_enquiry_name").val();
    var sales_mobile_no 		= $("#sales_mobile_no").val();
    var unit 					= $("#unit").val();
    var location 				= $("#location").val();

    var sales_note 			= $("#sales_note").val();
    var stages 				= $("#stages").val();
    var creator      = $("#created_by").val();
		//  var route 				= "{{ Request::fullUrl() }}"; 


    var fieldName = [];
    var operation = [];
    var fieldValue = [];
    var logic = [];

      // Initializing array  
      $(".fieldName").each(function(){
    //  if(this.value != '')
    fieldName.push(this.value);
  });
      
      $(".operation").each(function(){
    //  if(this.value != '')
    operation.push(this.value);
  });      
      
      $(".fieldValue").each(function(){
    //  if(this.value != '')
    fieldValue.push(this.value);
  });
      
      $(".logic").each(function(){
    //  if(this.value != '')
    logic.push(this.value);
  });




      $.ajax({
       method: "POST",
       url: "{{$route}}",
       data: { 'sales_enquiry_no': sales_enquiry_no,'created_at': created_at,'fc_att': fc_att,'sales_enquiry_name': sales_enquiry_name,'work_flow_processes_code':stages,
       'sales_mobile_no': sales_mobile_no,'unit_type': unit,'sales_note': sales_note,'loc': location,"_token" : $('meta[name="csrf-token"]').attr('content') ,'ajax':true,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic,'created_by':creator  },
       success: function(data){ 

        console.log(data);
        if(data != 0){

          var temp = $(data);
          var paginate_info = temp.find('.pagination_info').clone();
          temp.find('.pagination_info').remove();

          var paginate = temp.find('#pagination_ajax').clone();

          temp.find('#pagination_ajax').remove();
          $('#enquiry-search').html(temp);
          $( "#pagination" ).html(paginate);      
          $( "#pagination_info" ).html(paginate_info);			

        } 


        var txt = '' ;
        txt =  'sales_enquiry_no='+sales_enquiry_no+'&created_at='+created_at+'&fc_att='+fc_att+'&sales_enquiry_name='+sales_enquiry_name+'&work_flow_processes_code='+stages+
        '&sales_mobile_no='+ sales_mobile_no+'&unit_type='+ unit+'&sales_note='+ sales_note+'&loc='+location;


        var txt_hashes = txt.split('&');
        var href_txt = '';
        for(var i = 0; i < txt_hashes.length; i++)
        {
          txt_hash = txt_hashes[i].split('=');

          if(txt_hash[1] !=  '' && txt_hash[1] != 'undefined'){                             
           href_txt =  (href_txt != '')? href_txt + '&': href_txt;

           href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
         }                       
       } 


       var queryTxt = decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));

       href_txt = href_txt + '&'+queryTxt;


       $('.sort_url').each(function (i, n) {
        var href = $(n).attr('href'); 
        var hashes =  href.slice(href.indexOf('sort'));
        href = href.split('?')[0];
        $(n).attr('href',href+'?'+href_txt+'&'+hashes);  
      });             




     }           
   });

    });
  

  $(document).on('click','.add_search',function(){ 

   var filter =   $(this).closest('.row').clone(); 
   
   $(filter).find(".add_search")
   .removeClass("add_search").addClass('remove_search').end()
   .find(".remove_search").html('Remove').end()
   .appendTo('#search_form')             
 });


  $(document).on('click','.remove_search',function(){
    $(this).closest('div .row').remove();
  });

});
</script>

@endsection
