@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Contract Info</div>
        </div>
         {{Breadcrumbs::render('tenantPdcView',$tenantContract->id)}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col"><h4>Contract Details</h4></div>
    </div>
    <div class="card-body">
     <div class="row">
        <div class="col-sm-6 p-0" >
            <dl>
                <dd>Building Code</dd>
                <dd>{{$tenantContract->building->building_code}}</dd>
                <dd>Unit Code</dd>
                <dd>{{$tenantContract->unit->unit_code}}</dd>
                <dt>Tenant code</dt>
                <dd>{{$tenantContract->tenant->tenant_code}}</dd>
                <dt>Agreement No</dt>
                <dd>{{$tenantContract->tenant_contract_no}}</dd>
                <dt>Contract From</dt>
                <dd>
                    @if($tenantContract->tenant_contract_valid_from_date)
                        {{$tenantContract->tenant_contract_valid_from_date->format('d-m-Y')}}
                    @endif
                </dd>
            </dl> 
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6 p-0">
            <dl>
                <dd>Building Name</dd>
                <dd>{{$tenantContract->building->building_name}}</dd>
                <dd>Rent</dd>
                <dd>{{$tenantContract->tenant_contract_rent}}</dd>
                <dt>Tenant Name</dt>
                <dd>{{$tenantContract->tenant->tenant_name}}</dd>
                <dd>Old Agreement No</dd>
                <dd>{{$tenantContract->tenant_contract_old_no?? 'NA'}}</dd>
                <dt>Contract To</dt>
                <dd>{{$tenantContract->tenant_contract_valid_to_date->format('d-m-Y')}}</dd>
            </dl> 
        </div>
        <div class="clearfix"></div>
        
   </div>
   

        {{--
        <div id="show" class="collapse">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
							<li class="bld">Agreement No</li>
                            <li>{{$landlordContractInfo->landlord_contract_no}}</li>
                            <li class="bld">Landlord Code</li>
                            <li>{{$landlordContractInfo->vendorInfo->vendor_code}}</li>
                             <li class="bld">Building Code</li>
                            <li>{{$landlordContractInfo->buildingInfo->building_code}}</li>
                            <li class="bld">Payment Term</li>
                            <li>{{$landlordContractInfo->paymentMethodInfo->payment_method_code}}</li>
                            <li class="bld">Management fees type</li>
                            <li>{{$landlordContractInfo->managementTypeInfo->management_types_name}}</li>
                               <li class="bld">Management Percentage</li>
                            <li>{{$landlordContractInfo->landlord_contract_percentage?? '0' }}%</li>
                             <li class="bld">Start Date</li>
                            <li>@if(empty($landlordContractInfo->start_date))
                                        {{"NA"}}
                                @else    
                                   {{$landlordContractInfo->start_date->format('d/m/Y')?? 'NA'}}</li>
                                @endif
                            <li class="bld">Close Activity</li>
                            <li>{{$landlordContractInfo->close_activity?? 'NA'}}</li>
                            <li class="bld">Valid From</li>
                            <li>{{$landlordContractInfo->landlord_contract_valid_from_date->format('d/m/Y')}}</li>
                            <li class="bld">Remark</li><li>
                                {{$landlordContractInfo->landlord_contract_note?? 'NA' }}</li> 
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                           
                            <li class="bld">Agreement Date</li>
                            <li>{{$landlordContractInfo->created_at->format('d/m/Y')}}</li>
                            <li class="bld">Landlord Name</li>
                            <li>{{$landlordContractInfo->vendorInfo->vendor_name}}</li>
                            <li class="bld">Building Name</li>
                            <li>{{$landlordContractInfo->buildingInfo->building_code}}</li>
                            <li class="bld">Payment Amount</li>
                            <li>{{$landlordContractInfo->landlord_contract_amt}}</li>
                            <li class="bld">Management fees</li>
                            <li>{{$landlordContractInfo->landlord_contract_management_fee}}</li>
                            <li class="bld">Cleaning charges</li>
                            <li>{{ $landlordContractInfo->landlord_contract_cleaning_charge }}</li>

                            <li class="bld">Free Lease Period</li>
                            <li>
                                @if(empty($landlordContractInfo->landlord_free_lease_period))
                                        {{"NA"}}
                                @else 
                                    {{$landlordContractInfo->landlord_free_lease_period->format('d/m/Y')}}
                                @endif
                            </li>
                            <li class="bld">Duration in Month</li>
                            <li>{{$landlordContractInfo->landlord_contract_duration?? 'NA'}}</li>
                            <li class="bld">Marketing Executive Name</li>
                            <li>{{$landlordContractInfo->marketExecutiveEmployeeInfo->employee_name}}</li>
                            
                             <li class="bld">Valid To</li>
                            <li>{{$landlordContractInfo->landlord_contract_valid_to_date->format('d/m/Y')}}</li>
                            <li class="bld">Source</li>
                            <li>{{$contract->salesEnquiry->enquirySource->enquiry_sources_name}}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        --}}
       

    
    </div> 
                   
</div>

</div>
</div>
<div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card card-box">
                    <div class="card-head"> 
                           <div class="clr"></div>
                             <header>Tenant Contract List</header>   
                         
                             <a  class="btn btn-circle btn-success align-right add_pdc" href="#" data-toggle="modal" data-target="#myModal" data-id="2">Add PDC</a>
       
                            <div class="clr"></div>
                    </div>
                    <div class="card-body ">
                       <div class="clr"></div>
                            <div class="table-wrap">
                                <div class="table-responsive">
                                    <table class="table display product-overview mb-30" id="dtBasicExample">
                                      <thead>
                                          <tr>
                                              <th>No</th>
                                              <th>Tenant Agreement No</th>
                                              <th>Building</th>
                                              <th>From</th>
                                              <th>To</th> 
                                              <th>Rent</th>            
                                              <th>Action</th>
                                          </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                    </tbody>
                                    </table>
                                </div>              
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- The Modal -->
<div class="modal" id="myModal"> </div>

@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    $("#form_sample_2").validate();
    
     $('.add_pdc').on('click', function(e) {        
            
       
            var contract_id = $(this).attr('data-id');
          
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url:"{{url('/tenant-contract/add-pdc/')}}"+'/'+contract_id+'', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        
    });
    
});
</script>
@endsection
