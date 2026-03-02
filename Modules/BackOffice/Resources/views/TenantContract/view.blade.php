@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Contract View</div>
        </div>

        {{ Breadcrumbs::render('tenant-contract-show',$tenantContract) }}

    </div>
</div>
<?php 
      $agreements = array();
      if(isset($tenantContract->tenant_muncipality_agreement)){
        $agreements = explode ("/", $tenantContract->tenant_muncipality_agreement);
      }
     
?>

<div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
         <div class="row">
            <div class="col-sm-12">
             @can('tenant_contract_send_for_approval')
             @if($tenantContract->tennat_contract_direct_indirect_status == 1 && $tenantContract->tenant_contract_status == 0 && $tenantContract->work_flow_processes_code != 107 && $tenantContract->tenant_renewal_termination_status != 8 && $tenantContract->tenant_renewal_termination_status === 0) 
             <a href="{{route('tenantSendForApproval',[$tenantContract->id,'view'])}}" class="btn btn-circle btn-warning  align-right">Send For Approval</a> 
             @endif

             @endcan
              @if(!isset($pendingPage) && $tenantContract->tenant_contract_status == 1 && $tenantContract->tenant_renewal_termination_status === 0)
             @can('tenant_contract_revoke')
             @if($tenantContract->work_flow_processes_code == '108' && $tenantContract->tenant_contract_is_revoke != 1 && isset($tenantContract->sale_enquiry_id))
             <a title="Revoke" href="{{route('tenantRevoke',$tenantContract->id)}}" class="btn btn-circle btn-danger  align-right">
                Revoke
            </a>
            @endif
            @endcan
            @endif

            @if(empty($pendingPage))
            @can('edit_tenant_contract_direct')
            @if($tenantContract->work_flow_processes_code < 107   && $tenantContract->tennat_contract_direct_indirect_status == 1 && $tenantContract->tenant_contract_status == 0 && $tenantContract->tenant_renewal_termination_status != 8) 
            <a title="Edit" href="{{route('tenant-contract.edit',$tenantContract->id)}}" class="btn btn-circle btn-primary  align-right" >Edit
            </a>
            @endif
            @endcan
            @endif
            @if(isset($title))
            @if(!$pendingPage)
            @if($tenantContract->work_flow_processes_code == 107)  
            <button title = "Close" type="submit" class="btn btn-circle btn-warning  align-right  closed_flow" data-toggle="modal" data-target="#myModal" data-id = "CL" data-enquiryid="{{$tenantContract->sale_enquiry_id}}" data-sales_id="{{$tenantContract->sale_enquiry_id}}" data-workflow_id="{{$tenantContract->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">Close</button>

            <button title = "Accept" type="submit" id="accept" class="btn btn-circle btn-primary accept_flow" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-enquiryid="{{$tenantContract->sale_enquiry_id}}" data-sales_id="{{$tenantContract->sale_enquiry_id}}" data-workflow_id="{{$tenantContract->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">Accept</button>  
            @endif 
            @if($tenantContract->work_flow_processes_code == 108)
            <button title = "Reject" type="submit" id="reject" class="btn btn-circle btn-danger reject" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" data-enquiryid="{{$tenantContract->sale_enquiry_id}}" data-tenant_contract_id="{{$tenantContract->id}}" data-backdrop="static" data-keyboard="false">Reject</button>

            <button title = "Accept" type="submit" id="accept" class="btn btn-circle btn-danger accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-enquiryid="{{$tenantContract->sale_enquiry_id}}" data-tenant_contract_id="{{$tenantContract->id}}" data-backdrop="static" data-keyboard="false">Accept</button>
            @endif   
            @endif 

            @endif  

            @can('tenant_contract_status')
            @if($tenantContract->status != 5 && $tenantContract->work_flow_processes_code == 108 && $tenantContract->tenant_contract_status == 1)
            <button type="button" class="btn btn-circle btn-default  align-right changeStatus" data-id="{{$tenantContract->id}}" data-toggle="modal" data-target="#myModal" title="Move To Legal" data-backdrop="static" data-keyboard="false">Change Status</button>  
            <!-- <button type="button" class="btn btn-circle btn-warning align-right vacating" data-id="{{$tenantContract->id}}" id="{{$tenantContract->tenant_contract_vacating_status}}" title="Vacating">Vacating</button> -->
            @endif
            @endcan 
			@if($tenantContract->tenant_contract_status == 1)  
            @can('tenant_municipality_details')
             <button type="button" class="btn btn-circle btn-default  align-right addMunicipality" data-id="{{$tenantContract->id}}" data-toggle="modal" data-target="#myModal" title="Add Municipality" data-backdrop="static" data-keyboard="false">Add Municipality</button>
             @endcan
            @endif
            @if(Gate::check('invoice_generation') || Gate::check('invoice_generation_view'))

            @if($tenantContract->tenant_contract_status == 1)  

            <a  href="{{route('invoice.show',$tenantContract->id)}}" class="btn btn-circle btn-primary  align-right" title="{{($tenantContract->invoice_check==1 || isset($contract->tenantInvoiceList))?'View Invoice':'Generate Invoice'}}">
                {{($tenantContract->invoice_check==1)?'View Invoice':'Generate Invoice'}}
            </a> 
            @elseif($tenantContract->work_flow_processes_code == 108 && $tenantContract->invoice_check==1)
               <a  href="{{route('invoice.show',$tenantContract->id)}}" class="btn btn-circle btn-primary  align-right" title="{{'View Invoice'}}">
                {{'View Invoice'}}
            </a> 
            @endif  
            @endif  

           @if($tenantContract->work_flow_processes_code >= 106)
            @if(!isset($pendingPage))

            @if((Gate::check('pdc_generation') || Gate::check('pdc_generation_view')) && $tenantContract->tenant_contract_status == 1 && $tenantContract->work_flow_processes_code <= 108)
            <a  href="{{route('pdc.edit',$tenantContract->id)}}" class="btn btn-circle btn-primary  align-right" title="PDC Generation">
                PDC Generation
            </a> 
            @else
            @can('pdc_view')
            <a  href="{{route('pdcView',$tenantContract->id)}}" class="btn btn-circle btn-primary  align-right" title="PDC View">
                PDC View
            </a> 
            @endcan 
            @endif 
            @endif 
            @endif 
        </div>
    </div>


    <div class="dataSearchBox">
        <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Agreement Date  :  </b><span>{{isset($tenantContract->created_at)?$tenantContract->created_at->format('d/m/Y'):""}}</span></h5>
                </div>
            </div>
        </div>
    </div>

<!-- Status Ribbon Starts -->
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-blue">
                <div class="ribbon"><span>Status</span></div>
               Status </header>
            <div class="panel-body light-green">
            <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details" title="Inactive - Not active yet , Active - Running Contract"><b>Agreement Status :  </b><span>
                        @if($tenantContract->tenant_contract_status == 0 &&
               $tenantContract->work_flow_processes_code == 108 )
               @if($tenantContract->tenant_renewal_termination_status == 8)
               {{'Terminated'}}
               @else
               {{'Expired'}}
               @endif
            @else
            {{$tenantContract->TenantContractStatusName}}
            @endif
                        
                    </span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details" title="Legal Contract, On-hold, No Maintenance, Blacklisted, Observation, Alert etc"><b>Contract Status :  </b><span>{{$tenantContract->status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>
						@if(in_array($tenantContract->tenant_renewal_termination_status,[7]))
							{{'Vacating'}}
                        @else
							{{$tenantContract->unit->vacant_status_name}}
                        @endif
					</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
					
					@if(!empty($tenantContract->unit->key))
                        @if(!empty($tenantContract->unit->key->user_id))
                            {{$tenantContract->unit->key->user->employee->employee_name}}
                        @elseif(!empty($tenantContract->unit->key->landlord_id))
                            {{$tenantContract->unit->key->landlord->vendor_name}}
                        @elseif(!empty($tenantContract->unit->key->tenant_id))
                            {{$tenantContract->unit->key->tenant->tenant_name}}
                        @endif   
                    @else
                    NA
                    @endif
                    </span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remaining days to Expiry :  </b><span>{{$remainingDays}} Days</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent paid Up To:  </b><span>
                     @if(!empty($tenantContract->tenant_contract_last_paid_date))
                     {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
                     @else
                     NA
                     @endif</span></h5>
                </div>
            </div>
            
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->

<!----    Discussion Form                        ------ -->
@if(count($tenantContract->discussion) > 0)
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-yellow">
            	<div class="ribbon"><span>Discussion</span></div>
               Renewal/ Vacating Discussion Forum </header>
            <div class="panel-body light-green">
                <table class="table display product-overview mb-30">
                <thead class="background-red">
                    <tr>                       
                        <th> Discussion Type</th>
                        <th>Comment</th>
                        <th>Comment By </th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody class="background-white">
                    @foreach($tenantContract->discussion  as $discussion)
                     <tr>                       
                        <td>{{$discussion->category->category}}</td>
                        <td>{{$discussion->discussion}}</td>
                        <td>{{($discussion->user->user_type == 'admin') ? ucwords($discussion->user->username) : ucwords($discussion->user->employee->employee_name) }}</td>
                        <td>{{$discussion->created_at->format('d/m/Y')}}</td>              
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif


<!---- End    Discussion Form                        ------ -->





    <div class="sub-head">Building Details</div>
    <div class="dataSearchBox">    
        <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Type :  </b><span>{{$tenantContract->unit->unit->unit_types_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Name :  </b><span><a href="{{route('tenants.show',$tenantContract->tenant_id)}}" target="_blank">{{$tenantContract->tenant->tenant_name}}</a></span></h5>
                </div>
            </div> 
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Mobile No :  </b><span>{{$tenantContract->tenant->tenant_contact_no}}</span></h5>
                </div>
            </div>
            
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Name :  </b><span>
                         @if($tenantContract->occupant_id)
                        {{$tenantContract->occupant->occupant_name??''}}
                        @else
                        {{$tenantContract->tenant->tenant_name}}
                        @endif
                       </span></h5>
                </div>
            </div> 
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Mob No:  </b><span>
                        @if(isset($tenantContract->occupant->occupant_primary_contact_no))
                        {{$tenantContract->occupant->occupant_primary_contact_no}}
                        @else
                        {{$tenantContract->tenant->tenant_contact_no}}
                        @endif
                    </span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Email:  </b><span>
                        @if(isset($tenantContract->occupant->occupant_email))
                        {{$tenantContract->occupant->occupant_email}}
                        @else
                        {{$tenantContract->tenant->tenant_contact_email}}
                        @endif
                    </span></h5>
                </div>
            </div>
			<div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>ARE :  </b><span>{{isset($tenantContract->buildingPreferred->buildingAssignToName->areUser->employee->employee_name)?$tenantContract->buildingPreferred->buildingAssignToName->areUser->employee->employee_name:''}}</span></h5>
                </div>
            </div>
            <!-- <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Sales Executive :  </b><span>{{$marketing_Executive}}</span></h5>
                </div>
            </div> -->
            
        </div>
    </div>
    <div class="sub-head">Contract Details</div>
    <div class="dataSearchBox">    
        <div class="card-body row">
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Valid To :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>

            @if(isset($tenantContract->tenant_contract_duration_countdown))
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                 <h5 class="details"><b>Duration :  </b><span>
                  @php 
                  $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
                  @endphp


                  {{$duration[0]}} Year
                  {{$duration[1]}} Month
                  {{$duration[2]}} Days


              </span></h5>
          </div>
      </div>
      @endif
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContract->tenant_contract_value)?numberFormat($tenantContract->tenant_contract_value):"NA"}} OMR</span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Rent (P.M) :  </b><span>{{ numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?$tenantContract->vaccant_date->format('d/m/Y'):""}} </span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Rent Paid by Previous Tenant :  </b><span>{{isset($tenantContract->last_rent)? numberFormat($tenantContract->last_rent)." OMR":"NA"}} </span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Municipality Agr. No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$tenantContract->tenant_contract_electric_water?? "NA"}}</span></h5>
        </div>
    </div>
    @if(isset($tenantContract->tenant_muncipality_agreement))

       
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Agreement Doc </b><span><a href="../storage/app/{{$tenantContract->tenant_muncipality_agreement}}" target="_blank">{{$agreements[2]}}</a></span></h5>
            </div>
        </div>
    @endif
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractPaymentName ?? "NA"}}</span></h5>
        </div>
    </div>

    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>PDC  :  </b><span>{{$tenantContract->pdc_check==1?'Full':'Partial'}} </span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Comment  :  </b><span>{{$tenantContract->partial_comment ?? "NA"}} </span></h5>
        </div>
    </div>

    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Invoice Generate  :  </b><span>{{($tenantContract->invoice_check ==null)?"No":"Yes"}} </span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Registered in Municipality  :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}} </span></h5>
        </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Contract Registered In :  </b><span>{{ $tenantContract->tenant_contract_registered_in_name }} </span></h5>
        </div>
    </div>
    @if($tenantContract->tenant_contract_registered_date)
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Contract Registered Date :  </b><span>{{ $tenantContract->tenant_contract_registered_date->format('d/m/Y') }} </span></h5>
        </div>
    </div>
    @endif
	@if(isset($tenantContract->marketExecutiveEmployeeInfo->employee_name))
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Marketing Executive Name :  </b><span>{{ $tenantContract->marketExecutiveEmployeeInfo->employee_name }} </span></h5>
        </div>
    </div> 
	 @endif
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Deposit :  </b><span>{{ ($tenantContract->deposit_check == NULL )?"No" : "Yes" }} </span></h5>
        </div>
    </div>    
    @if($tenantContract->status)
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Status  :  </b><span>
                @switch($tenantContract->status)
                @case(2)
                On Hold
                @break
                @case(3)
                No Maintenance
                @break
                @case(4)
                Blacklisted 
                @break
                @case(5)
                Move to Legal
                @break
                @case(7)
                Alert
                @break
                @case(8)
                Observation
                @break
                @case(9)
                Watch
                @break
                @default
                No
                @endswitch
                
            </span></h5>
        </div>
    </div>
	@if(count($tenantContract->tenantContractComment) > 0)
    <div class="col-lg-6 p-t-20">
        <div class = "txt-full-width">
            <h5 class="details"><b>Status Comment :  </b>
                <span>{{$tenantContract->tenantContractComment->last()->comment}} </span>
            </h5>
        </div>
    </div>
    @endif

    @endif
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Remark :  </b><span>{{$tenantContract->tenant_contract_note ?? "NA"}} </span></h5>
        </div>
    </div>
     <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Remaining Days for Penalty :  </b><span>{{ getPenaltyDays($tenantContract->id,$tenantContract->tenant_contract_valid_to_date)}} </span></h5>
        </div>
    </div>
</div>
</div>
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">    
    <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt)." OMR":"NA"}}</span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Guarantee Cheque:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)? $tenantContract->tenant_contract_guarantee_cheque_details:"NA"}} </span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Receipt No:  </b><span>{{$tenantContract->tenant_contract_receipt_no ?? "NA"}} </span></h5>
            </div>
        </div>
       
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Receipt date:  </b><span>
                 @if(isset($tenantContract->tenant_contract_receipt_date))

                {{$tenantContract->tenant_contract_receipt_date->format('d/m/Y')}}         @endif
                @if(!isset($tenantContract->tenant_contract_receipt_date))
                NA
                @endif</span></h5>
            </div>
        </div>
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($tenantContract->tenant_contract_receipt_amount)?numberFormat($tenantContract->tenant_contract_receipt_amount)." OMR":"NA"}} </span></h5>
            </div>
        </div>
		
		<div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Last Paid Date:  </b><span>{{isset($tenantContract->tenant_contract_last_paid_date)?$tenantContract->tenant_contract_last_paid_date->format('d/m/Y'):"NA"}} </span></h5>
            </div>
        </div>
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Last Paid Amount:  </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)?numberFormat($tenantContract->tenant_contract_last_paid_amt)." OMR":"NA"}} </span></h5>
            </div>
        </div>
    </div>
</div>
@if(!empty($tenantContract->tenantDocument) && count($tenantContract->tenantDocument) > 0) 
<div class="sub-head">Document Upload  </div>
<div class="dataSearchBox">    
    <div class="card-body row">
      @foreach ($tenantContract->tenantDocument  as $doc) 
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Document :  </b>

            <span>


              <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}" target="_blank"><i class="fa fa-file" aria-hidden="true"></i>
                 {{$doc->tenant_documents_name}} 
             </a>
			</h5>
         </span>
     </div>
 </div>
 @endforeach    
</div>
</div>
</div>    
@endif
@if(count($notesArray)>0)
<div class="card card-box salesLeadBox">
  <div class="card-head">
   <div class="col"><h4> Note</h4></div>
</div>
<div class="card-body">
   <div class="col">
    <div class="row">

        <div class="col leadInformation">
         <div class="table-responsive1">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Stage</th>
                        <th>Note</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>

                   @forelse ($notesArray as $note)

                   <tr>
                    <td>{{$note['stage']}}</td>
                    <td>{{$note['sales_notes']}}</td>
                    <td>{{$note['employee_name']}}</td>
                    <td>{{$note['created_at']}}</td>
                </tr> 
                                    
                @empty
                <tr>
                    <td colspan="4" align="center">
                        No Record
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
</div>
</div>

</div>
</div>     
@endif
@if(count($revokeNote)>0)
<div class="card card-box salesLeadBox">
  <div class="card-head">
   <div class="col"><h4>Revoke Note</h4></div>
</div>
<div class="card-body">
   <div class="col">
    <div class="row">

        <div class="col leadInformation">
         <div class="table-responsive1">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Stage</th>
                        <th>Note</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>

                   @forelse ($revokeNote as $note)

                   @if($note->tenant_contract_revoke_note_desc!="")
                   <tr>
                    <td>Revoke </td>
                    <td>{{$note->tenant_contract_revoke_note_desc}}</td>
                    <td>{{$note->CreatedUser->username??''}} </td>
                    <td>{{$note->created_at->format('d/m/Y')}} </td>
                </tr> 
                @endif                       
                @empty
                <tr>
                    <td colspan="4" align="center">
                        No Record
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
</div>
</div>

</div>
</div>     
@endif
</div> 

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('.changeStatus').on('click', function(e) {        

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
            url: "{{route('tenantContractAddMunicipalityview')}}", // This is the url we gave in the route
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

        /***************************************************************************/
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
    });
</script>
@endsection


