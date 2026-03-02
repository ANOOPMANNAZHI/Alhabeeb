@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('finalApprovalList'))
@section('search_reset', route('finalApprovalList'))

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Final Documentation Approval</div>
        </div>
        {{ Breadcrumbs::render('finalApprovalList') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

           @include('sales::enquiry_search')     
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            <!-- <div class="card-head">
                <header>Open Enquiry List</header>
                
            </div> -->
            <div class="card-body ">
            <h4>
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive1">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                               <tr>
                                    <th title="Enquiry no">@sortablelink('sales_enquiry_no','Enq No')</th>
                                    <th title="Enquiry date">@sortablelink('created_at','Enq Dt')</th>
                                    <th title="Customer Name">@sortablelink('sales_enquiry_name','Customer')</th>
                                    
                                    <th title="Building Name">Building</th>
                                    <th title="Unit">Unit</th>
                                    <!--<th title="Unit Usage">Unit Usage</th> -->
                                    <th title="Duration period">Duration(Months)</th>
                                    <th title="Start date">Start Dt</th>
                                    <th title="Rent">Rent</th>
                                    <th title="Action buttons" width="17%" style="text-align:center">Action</th>
                                </tr>
                               <tr> 
                                    <td> <input autocomplete="off" type="text" name="sales_enquiry_no" class="search_fields mob" id="sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
                                    <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
                                    <td><input autocomplete="off" type="text" name="sales_enquiry_name" class="search_fields" id="sales_enquiry_name" value="{{old('sales_enquiry_name',$request->customer_name)}}" ></td>
								    <td><input autocomplete="off" type="text" name="building_name_select" class="search_fields building_name_select" id="building_name_select"  value="{{old('building_name_select')}}" > </td>
                                    <td><input autocomplete="off" type="text" name="unit_select" class="search_fields unit_select" id="unit_select" value="{{old('unit_select')}}" ></td>
                                    <td><input autocomplete="off" type="text" name="duration" class="search_fields duration" id="duration"  value="{{old('duration')}}" > </td>
                                    <td><input autocomplete="off" type="date" name="start_dt" class="search_fields start_dt" id="start_dt" value="{{old('start_dt')}}" > </td>
									<td><input autocomplete="off" type="text" name="rent" class="search_fields rent" id="rent" value="{{old('rent')}}" > </td>
                                    <td></td>
								</tr>
                            </thead>
                              <tbody id="enquiry-search">
							   								   
							    @include('sales::TenantSales.tenant_final_approval_list_ajax')	
							    							
                            </tbody>
                        </table>
                        
                         <div class="row "  id="pagination">
							  @php 
							           if(!empty($request->customer_name))
										$lists->appends(['customer_name' => $request->customer_name]);
										
										if(!empty($request->sales_enquiry_no))
										$lists->appends(['sales_enquiry_no' => $request->sales_enquiry_no]);
										
										if(!empty($request->phone))
										$lists->appends(['building_name_select' => $request->building_name_select]);
										
										if(!empty($request->stages))
										$lists->appends(['unit_select' => $request->unit_select]);
										
										if(!empty($request->type))
										$lists->appends(['start_dt' => $request->start_dt]);	
										
										if(!empty($request->sales_building_name))
										$lists->appends(['rent' => $request->rent]);
										
										if(!empty($request->duration))
										$lists->appends(['duration' => $request->duration]);
								
										if(!empty($request->assigned_person))
											$documntation_lists->appends(['duration' => $request->assigned_person]);
										
										if(!empty($request->type))
										$lists->appends(['type' => $request->type]);	
										
										$sort =  app('request')->input('sort');
										
										if(!empty($sort)){
											$direction =  app('request')->input('direction') ;
											$lists->appends(['sort' => $sort, 'direction' => $direction ]);
											
										}
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
									
									    	$lists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
										
																				
							 @endphp				
							 {{$lists->links()}}
                        </div>                       
                        
                    </div>
                </div> 
            <!-- </form>  -->
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
@include('sales::LandlordSales.landlord_search_js')
<script>
$(document).ready(function() {
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
    $(document).on('click','.closed', function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $(this).attr('datas-id');
        var enquiryid = $(this).attr('datas-enid');
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else{
            return false;
        }*/
        
         
    });
    $(document).on('click', '.assignLead',function(e) {        

        /*if (confirm("Are you sure you want to Re-assign ?")) {*/
            var vals = $(this).attr('data-id');
            var workflow = $(this).attr('datas-id');
            $('input:hidden[name=enquiryIds]').val(vals);
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../leadAssign/re-assign/'+vals+'/'+workflow+'', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*} else {
            return false;
        }*/  
         
    });
    
    
});
</script>
@endsection
