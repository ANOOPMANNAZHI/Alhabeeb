@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Documentation Enquiries</div>
        </div>
        {{ Breadcrumbs::render('documentationList') }}
    </div>
</div>
<div class="row">
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
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>@sortablelink('salesEnquiry.sales_enquiry_name','Enquiry Name')</th>
                                    
                                    <th>@sortablelink('salesEnquiry.sales_email','Email')</th>
                                    <th>@sortablelink('salesEnquiry.sales_mobile_no','Phone')</th>
                                    <th>Enquiry Source</th>
                                    <th>Action</th>
                                </tr>
                                <tr> 
								  <td></td>
								  <td><input type="text" name="customer-name" class="search_fields customer-name" value="{{old('customer_name')}}" ></td>
								  <td><input type="text" name="email" class="search_fields email"  value="{{old('email')}}" ></td>
								  <td><input type="text" name="phone" class="search_fields phone"  value="{{old('phone')}}" ></td>
								  <td></td>
								  <td></td>
								</tr>
								
                            </thead>
                             <tbody id="enquiry-search">
							   								   
							    @include('sales::TenantSales.tenant_documentation_list_ajax')	
							    							
                            </tbody>
                        </table>                         
                        
                         <div class="row "  id="pagination">
							  @php 
							           if(!empty($request->customer_name))
										$documntation_lists->appends(['customer_name' => $request->customer_name]);
									
										if(!empty($request->email))
										$documntation_lists->appends(['email' => $request->email]);
										
										if(!empty($request->phone))
										$documntation_lists->appends(['phone' => $request->phone]);
										
										if(!empty($request->sales_building_name))
										$documntation_lists->appends(['sales_building_name' => $request->sales_building_name]);
										
										 
										if(!empty($request->type))
										$documntation_lists->appends(['type' => $request->type]);	
										
										$sort =  app('request')->input('sort');
										
										if(!empty($sort)){
											$direction =  app('request')->input('direction') ;
											$documntation_lists->appends(['sort' => $sort, 'direction' => $direction ]);
											
										}
										
										$fieldName =  app('request')->input('fieldName');
										
										if(!empty($fieldName)){
											$fieldName =  app('request')->input('fieldName');
											$operation =  app('request')->input('operation');
											$fieldValue =  app('request')->input('fieldValue');
											$logic =  app('request')->input('logic');
									
									    	$documntation_lists->appends(['fieldName' => $fieldName,
										             'operation' => $operation,
										             'fieldValue' => $fieldValue,
										             'logic' => $logic,
										     ]);	
										}
										
																				
							 @endphp				
							 {{$documntation_lists->links()}}
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
    $("#leade_search").validate();
    $('.closed').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $(this).attr('datas-id');
        var enquiryid = $(this).attr('datas-enid');
        if (confirm('Do you want to Close this Enquiry?')) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        }else {
            return false;
        }
        
         
    });
    $('.assignLead').on('click', function(e) {        

        if (confirm("Are you sure you want to Re-assign ?")) {
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
        } else {
            return false;
        }  
         
    });
    
    
});

</script>
@endsection
