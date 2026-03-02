@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_reset',$serach_url)
@section('content')


<div class="page-bar">
	<div class="page-title-breadcrumb">
		<div class=" pull-left">
			<div class="page-title">@if(isset($title)) {{$title}} @else Tenant Contract  @endif </div>
		</div>
		@if(isset($title))
		{{ Breadcrumbs::render($breadcrumb,$title) }}
		@else
		{{ Breadcrumbs::render('tenant-contract.index') }}
		@endif
	</div>
</div>
<!-- start widget -->

<!-- end widget -->


<a  class="align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
	<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>

<div class="row collapse @if(old('fieldName',$request->fieldName)) show @endif" id="show"  >
	<div class="col-md-12 col-sm-12 dashboardtab"> 
		<div class="card card-box salesSearchBox">
			@include('backoffice::TenantContract.search') 
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-12 col-sm-12">


		<div class="card card-box">

            <!-- <div class="card-head"> 
               <div class="clr"></div>
                  <header>Tenant Contract List</header>   
                <div class="clr"></div>
            </div> -->

            <div class="card-body ">
            	<h4>
                 <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $tenantContracts])         
                 </div>
            		@if(!isset($pendingPage))
            		@can('add_tenant_contract_direct')
            		<a href="{{route('tenant-contract.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
            		@endcan
            		@endif
            		<div class="clr"></div>
            	</h4>

            	<div class="table-wrap">
            		<div class="table-responsive">
            			<table class="table display product-overview mb-30" id="dtBasicExample">
            				<thead>
            					<tr>
            						
            						<th>@sortablelink('tenant_contract_no','Contract No',[],[ 'class' => 'contract_sort' ]) </th>
            						<th>@sortablelink('tenant_name','Tenant',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('building.building_name','Building',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('unit_code','Unit No',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('unit_types_name','Unit Type',[],[ 'class' => 'contract_sort' ])</th>
            						
            						<th>@sortablelink('tenant_contract_start_date','Start',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('tenant_contract_valid_to_date','End',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('tenant_contract_rent','Rent',[],[ 'class' => 'contract_sort' ])</th>
            						<th>@sortablelink('tenant_contract_is_reg_municipality','Reg Municipality',[],[ 'class' => 'contract_sort' ])</th>      
            						<th>@sortablelink('pdc_check','PDC',[],[ 'class' => 'contract_sort' ])</th> 
                                    <th>@sortablelink('invoice_check','Invoice',[],[ 'class' => 'contract_sort' ])</th>    
            						<th>@sortablelink('tenant_contact_no','Mobile No',[],[ 'class' => 'contract_sort' ])</th>    
            						@if(!isset($is_tenant_contract))<th>Type</th> @endif       
            						<th  width="9%" style="text-align:center">Action</th>
            					</tr> 
            					<tr> 
            						
            						<td><input autocomplete="off" type="text" name="tenant_agr" class="contract_search_field tenant_agr" value="{{old('tenant_agr',$request->tenant_contract_no)}}" ></td>
            						<td><input autocomplete="off" type="text" name="tenant__tenant_name" class="contract_search_field tenant__tenant_name" value="{{old('tenant__tenant_name',$request->tenant__tenant_name)}}" ></td>
            						<td><input autocomplete="off" type="text" name="building__building_name" class="contract_search_field building__building_name" value="{{old('building__building_name',$request->building__building_name)}}" ></td>
            						<td><input autocomplete="off" type="text" name="unit__unit_code" class="contract_search_field unit__unit_code" value="{{old('unit__unit_code',$request->unit__unit_code)}}" > </td>

                                    <td><input autocomplete="off" type="text" name="unit_types_name" class="contract_search_field unit_types_name" value="{{old('Unit__unit_unit_types_name',$request->Unit__unit_unit_types_name)}}" > </td>

                                
            						<td><input autocomplete="off" type="date" name="tenant_contract_start_date" class="contract_search_field tenant_contract_start_date" value="{{old('contract_start_dt', $request->tenant_contract_start_date)}}" > </td>
            						<td><input autocomplete="off" type="date" name="tenant_contract_valid_to_date" class="contract_search_field tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date', $request->tenant_contract_valid_to_date)}}" > </td>
            						<td><input autocomplete="off" type="text" name="contract_rent" class="contract_search_field contract_rent"  value="{{old('contract_rent', $request->tenant_contract_rent)}}" ></td>
            						<td> 
            							<select name="tenant_contract_is_reg_municipality" class="tenant_contract_is_reg_municipality">
            								<option value="">Select</option>
            								<option value="1" {{ (old('tenant_contract_is_reg_municipality', (isset($request->tenant_contract_is_reg_municipality)? $request->tenant_contract_is_reg_municipality : '')  ) == 1)? 'selected':''}} >Yes</option>
            								<option value="0" {{ (old('tenant_contract_is_reg_municipality', (isset($request->tenant_contract_is_reg_municipality)? $request->tenant_contract_is_reg_municipality : '')  ) === '0')? 'selected':''}} >No</option>
            								
            							</select>
            						</td>
            						<td><select name="pdc_check" class="pdc_check">
            							<option value="">Select</option>
            							<option value="1" {{ (old('pdc_check', (isset($request->pdc_check)? $request->pdc_check : '')  ) == 1)? 'selected':''}} >Yes</option>
            							<option value="0" {{ (old('pdc_check', (isset($request->pdc_check)? $request->pdc_check : '')  ) === '0')? 'selected':''}} >No</option>
            							
            						</select>
            					</td>
            					<td>
            						<select name="invoice_check" class="invoice_check">
            							<option value="">Select</option>
            							<option value="1"  {{ (old('invoice_check', (isset($request->invoice_check)? $request->invoice_check : '')  ) == 1)? 'selected':''}}  >Yes</option>
            							<option value="0" {{ (old('invoice_check', (isset($request->invoice_check)? $request->invoice_check : '')  ) === '0')? 'selected':''}} >No</option>
            							
            						</select>
            					</td>
                                <td><input autocomplete="off" type="text" name="tenant__tenant_contact_no" class="contract_search_field tenant__tenant_contact_no" value="{{old('tenant__tenant_contact_no',$request->tenant__tenant_contact_no)}}" ></td>
            					<td></td> 
            					@if(!isset($is_tenant_contract))<td></td> @endif
            				</tr>
            			</thead>
            			<tbody id="contract-search">
            				@include('backoffice::TenantContract.contract_list_ajax') 						
            			</tbody>
            		</table>
            	</div>
            </div>
            <div id="pagination">
            	<div class="text-center">
            	
            	{{$tenantContracts->appends(\Request::except('page'))->links()}}
            </div>
        </div>
    </div>
</div>

</div>
</div>


</div>
</div>
<div class="modal" id="myModal">

</div>
<div class="modal" id="myModal1">

</div>
@endsection
@section('scripts')

@include('backoffice::TenantContract.search_js') 

<script>
	$(document).ready(function() {
		
		$("#show").on("hide.bs.collapse", function(){
			$("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
		});
		$("#show").on("show.bs.collapse", function(){
			$("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
		});
		var showResultsTimer = 0;
		$(".tenant_contract_valid_to_date, .tenant_contract_start_date, .tenant_contract_is_reg_municipality, .pdc_check,.invoice_check").change(function(){
			
			$('.contract_search_field').trigger('keyup');
		});
		
		$('.contract_search_field').on('keyup paste',function(){ 
			
			searchnow = this.value;
			var tenant_contract_no 			= $(".tenant_agr").val();
            var tenant__tenant_name                     = $(".tenant__tenant_name").val();
			var tenant__tenant_contact_no 					= $(".tenant__tenant_contact_no").val();
			var building__building_name 						= $(".building__building_name").val();
            var unit__unit_code                         = $(".unit__unit_code").val();
			var unit_types_name 						= $(".unit_types_name").val();
			var tenant_contract_start_date 	= $(".tenant_contract_start_date").val();
			var tenant_contract_valid_to_date 	= $(".tenant_contract_valid_to_date").val();
			var tenant_contract_rent 			= $(".contract_rent").val();
			var route_href 					= $("#leade_search").attr('action'); 
			var invoice_check					= $(".invoice_check").val();
			var pdc_check						= $(".pdc_check").val();
            var tenant_contract_is_reg_municipality = $(".tenant_contract_is_reg_municipality").val();
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
			
            
			
			window.clearTimeout(showResultsTimer);
			showResultsTimer = window.setTimeout(function(){	
				$.ajax({
					method: "GET",
					url: route_href,
					data: {'tenant_contract_no': tenant_contract_no,'tenant_name':tenant__tenant_name,'tenant_contact_no':tenant__tenant_contact_no,'building__building_name':building__building_name,'tenant_contract_rent':tenant_contract_rent,
				        'unit_code': unit__unit_code,'unit_types_name': unit_types_name, 'tenant_contract_valid_to_date':tenant_contract_valid_to_date,'tenant_contract_start_date':tenant_contract_start_date ,'invoice_check':invoice_check,'pdc_check':pdc_check, 'tenant_contract_is_reg_municipality':tenant_contract_is_reg_municipality ,'ajax' : true ,
                        'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic 
                        },			  
					beforeSend: function(){
							// Show image container
							$("#pagination" ).hide();
							$('#contract-search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
							
						},
						success: function(data){ 
							
							if(data != 0){
								if(data.length >0){
									$("#pagination" ).show();
									var temp = $(data);
                                    var paginate_info = temp.find('.pagination_info').clone();
                    temp.find('.pagination_info').remove();
									var paginate = temp.find('#pagination_ajax').clone();
									temp.find('#pagination_ajax').remove();
									$('#contract-search').html(temp);
									$("#pagination" ).html(paginate);	
                                     $( "#pagination_info" ).html(paginate_info); 	
									// $('.contract_search_field').trigger('blur');	


  var txt = 'tenant_contract_no='+tenant_contract_no+'&tenant__tenant_name='+tenant__tenant_name+'&tenant__tenant_contact_no='+tenant__tenant_contact_no+'&building__building_name='+building__building_name+'&tenant_contract_rent='+tenant_contract_rent+'&unit__unit_code='+ unit__unit_code+'&Unit__unit__unit_types_name='+unit_types_name+'&tenant_contract_valid_to_date='+tenant_contract_valid_to_date+'&tenant_contract_start_date='+tenant_contract_start_date+'&invoice_check='+invoice_check+'&pdc_check='+pdc_check+'&tenant_contract_is_reg_municipality='+tenant_contract_is_reg_municipality;


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


 $('.contract_sort').each(function (i, n) {
  var href = $(n).attr('href'); 
  var hashes =  href.slice(href.indexOf('sort'));
  href = href.split('?')[0];
  $(n).attr('href',href+'?'+href_txt+'&'+hashes);  
 }); 

								}
							}  
							
						}   
					});        
			},1000);
			
		});

	/**************************************************************************/
    $(document).on('click','.discussion-forum', function(e){
        var tenant_contract_id =  $(this).attr('data-id');

        $.ajax({
            method: 'POST',  
            url: "{{route('discussionForum')}}",  
            data: {'tenant_contract_id':tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){  
                $("#myModal").html(response);
                 
           },
       });
    })

    /************************************************************************/
	$(document).on("click",'.changeStatus', function(e) {       

			var tenant_contract_id =  $(this).attr('data-id');
        //alert(tenant_contract_id);
        /* if (confirm('Do you want to Approval Accept?')) {*/
        	$.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('tenantContractChangeStatus')}}", // This is the url we gave in the route
            data: {'tenant_contract_id':tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
            	$("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
           },
       });
        	return true;
        /*}else {
            return false;
        } */       
        
    });
	/**************************************************************************/

        $(document).on("click",'.addMunicipality', function(e) {      

            var tenant_contract_id =  $(this).attr('data-id');
        //alert(tenant_contract_id);
        /* if (confirm('Do you want to Approval Accept?')) {*/
            $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('tenantContractAddMunicipality')}}", // This is the url we gave in the route
            data: {'tenant_contract_id':tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
           },
       });
            return true;
        /*}else {
            return false;
        } */      
       
    });


		/**************************************************************************/
	
	//Flow start
	$(document).on('click','.accept_flow', function(e) {        

		var action_key = $(this).attr('data-id');
		var enquiryid = $(this).attr('data-enquiryid');
		var sales_id = $(this).attr('data-sales_id');
		var workflow_id = $(this).attr('data-workflow_id');
		/*if (confirm('Do you want to Accept this Enquiry?')) {*/
			$.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
            	$("#myModal").html(response); 
            },
        });
			return true;
        /*}else {
            return false;
        }*/
        
        
    });
	/**************************************************************************/

	$(document).on('click','.closed_flow', function(e) {        

		var action_key = $(this).attr('data-id');
		var enquiryid = $(this).attr('data-enquiryid');
		var sales_id = $(this).attr('data-sales_id');
		var workflow_id = $(this).attr('data-workflow_id');
		/*if (confirm('Do you want to Close this Enquiry?')) {*/
			$.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                	$("#myModal").html(response); 
                },
            });
			return true;
        /*}else {
            return false;
        }*/
        
        
    });
	/**************************************************************************/	
    $(document).on('click','.reject_direct', function(e) {        

        var action_key = $(this).attr('data-id');
        
        var enquiryid = $(this).attr('data-enquiryid');
        var tenant_contract_id = $(this).attr('data-tenant_contract_id');
        
        /*if (confirm('Do you want to Reject this Enquiry?')) {*/
            $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('noteDirectModal')}}",  // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal1").html(response); 
            },
        });
            return true;
        /*}else {
            return false;
        }*/
        
        
    }); 
     $(document).on('click','.accept_direct', function(e) {
       

        var action_key = $(this).attr('data-id');
        var enquiryid = $(this).attr('data-enquiryid');
        var tenant_contract_id = $(this).attr('data-tenant_contract_id');
        var sales_lead_note_name = 'Contract Rejected being Unit Occupied';
        var occuipied_units = $("#occuipied_units").val();
        /*if (confirm('Do you want to Accept this Enquiry?')) {*/
           
        /* if(occuipied_units > 0){
           alert("Created Contract Unit Already Occupied..!Contract Rejected !");

            $.ajax({
            method: 'POST',
            url: "{{route('directContractApprovalAcceptReject')}}",
            data: {'action_key' : 'RJCT','tenant_contract_id' : tenant_contract_id,'enquiryid' : enquiryid,'sales_lead_note_name' : sales_lead_note_name,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                  location.reload();
            },
        });
         //  return false;
        }else{ */
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('noteDirectModal')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
            },
        });
        return true;
       
       /* }*/
       
    });

	$(document).on('click','.reject', function(e) {        

		var action_key = $(this).attr('data-id');
		
		var enquiryid = $(this).attr('data-enquiryid');
		var tenant_contract_id = $(this).attr('data-tenant_contract_id');
		
		/*if (confirm('Do you want to Reject this Enquiry?')) {*/
			$.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('noteModal')}}",  // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
            	$("#myModal1").html(response); 
            },
        });
			return true;
        /*}else {
            return false;
        }*/
        
        
    });	
	/**************************************************************************/
	$(document).on('click','.accept', function(e) {        

		var action_key = $(this).attr('data-id');
		var enquiryid = $(this).attr('data-enquiryid');
		var tenant_contract_id = $(this).attr('data-tenant_contract_id');
		
		$.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('noteModal')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
            	$("#myModal").html(response); 
            },
        });
		return true;
		
		
		
	});
	/**************************************************************************/
	$("#myModal").on("hidden.bs.modal", function(){
		$("#myModal").html("");
		$(this).removeData('bs.modal');
	});
	$("#myModal1").on("hidden.bs.modal", function(){
		$("#myModal1").html("");
		$(this).removeData('bs.modal');
	});	
	/**************************************************************************/
});

/**************************************************************************/
$(document).ready(function(){
	if($("#purpose").val() == 'tenant_contract_status'){
		$("#status").show();
		$("#fieldValues").show();
		$("#val").hide();
		$("#fieldValue").hide().prop('required',false);
	}else{
		$("#status").hide();
		$("#val").show();
		$("#fieldValue").show();
	}
	
	$('#purpose').on('change', function() {
		if ( this.value == 'tenant_contract_status')
		{
			$("#status").show();
			$("#val").hide().prop('required',false);
			$("#fieldValue").hide().prop('required',false);
			$("#fieldValues").show();
		}
		else
		{
			$("#status").hide().prop('required',false);
			$("#fieldValues").hide().prop('required',false);
			$("#val").show();
			$("#fieldValue").show();
		}
	});
});
</script>
@endsection
