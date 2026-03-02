@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Handover Unassigned View</div>
    </div>
    {{ Breadcrumbs::render('handoverUnassignedView',$tenantContract) }} 
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
  <div class="card-box">  
   <div class="card-head">
		<div class="col">
        <h4>
        @can('handover_assign')
            <button type="button" class="btn btn-circle btn-primary align-right assignLead"  data-toggle="modal" data-target="#myModal" data-id="{{$termination->work_flow_processes_code}}" datas-id = "{{$termination->id}}" data_ac_key = "AS" data-backdrop="static" data-keyboard="false">  Assign</button>
        @endcan
        </h4>
         </div>
	</div>

     <div class="salesSearchBox">
            <!-- <div class="dataSearchBox">
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
            </div> -->
            <div class="sub-head"></div>
            <div class="dataSearchBox">    
                <div class="card-body row">
               
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Contract No :  </b><span>{{$tenantContract->tenant_contract_no??'NA'}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name??'NA'}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no??'NA'}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name??'NA'}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Mobile No :  </b><span>{{$tenantContract->tenant->tenant_contact_no??'NA'}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Location :  </b><span>{{$tenantContract->building->location->locations_name??'NA'}}</span></h5>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Way No :  </b><span>{{$tenantContract->building->building_address??'NA'}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Outstanding:  </b><span>{{ ($outstandingOs > 0)? "Yes": 'No'}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Code:  </b><span>{{$tenantContract->building->building_code ?? "NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Code:  </b><span>{{$tenantContract->tenant->tenant_code ?? "NA"}}</span></h5>
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
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->tenant_status_name}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContract->tenant_contract_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->unit->vacant_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
                    @if(!empty($tenantContract->unit->key)){{$tenantContract->unit->key->status_name}}
                    @else
                    NA
                    @endif</span></h5>
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
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> </b><span></span></h5>
                </div>
            </div>
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->
            <div class="sub-head"></div>
            <div class="dataSearchBox">    
                <div class="card-body row">
               
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>TakeOver Date :  </b><span>
                                {{ (!empty($tenantContract->terminationContract->termination_takenover_date)) ?  $tenantContract->terminationContract->termination_takenover_date->format('d/m/Y') : "NA"}}
                            </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Termination Date:  </b><span>{{ (!empty($tenantContract->terminationContract->termination_date))? $tenantContract->terminationContract->termination_date->format('d/m/Y') : "NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark:  </b><span>{{$tenantContract->terminationContract->termination_remark?? "NA"}}</span></h5>
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- <div class="sub-head">Contract Details</div>
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
                            <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContract->tenant_contract_value)?number_format($tenantContract->tenant_contract_value, 3):"NA"}} OMR</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Rent (P.M) :  </b><span>{{ number_format($tenantContract->tenant_contract_rent, 3)}} OMR</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?number_format($tenantContract->vaccant_date, 3):"NA"}} </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Rent Paid By Previous Tenant :  </b><span>{{isset($tenantContract->last_rent)? number_format($tenantContract->last_rent, 3)." OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                    @if(isset($tenantContract->tenant_contract_muncipality_agr_no))
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if(isset($tenantContract->tenant_contract_electric_water))
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$tenantContract->tenant_contract_electric_water?? "NA"}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if($tenantContract->tenant_contract_registered_in)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
                        </div>
                    </div>
                    @endif
                    @if($tenantContract->tenant_contract_payment_type)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractPaymentName}}</span></h5>
                        </div>
                    </div>
                    @endif
                  
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>PDC  :  </b><span>{{($tenantContract->pdc_check ==null)?"No":"Yes"}} </span></h5>
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
                    @if($tenantContract->tenant_contract_note)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark :  </b><span>{{$tenantContract->tenant_contract_note}} </span></h5>
                        </div>
                    </div>
                    @endif
                </div>
             </div>
            <div class="sub-head">Payment Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
              
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?number_format($tenantContract->tenant_contract_deposit_amt, 3)."OMR":"NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?number_format($tenantContract->tenant_contract_guarantee_cheque_details, 3)."OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt No:  </b><span>{{$tenantContract->tenant_contract_receipt_no ?? 'NA' }}</span></h5>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt date:  </b><span>{{isset($tenantContract->tenant_contract_receipt_date)?$tenantContract->tenant_contract_receipt_date->format('d/m/Y'):"NA"}}</span></h5>
                        </div>
                    </div>
                   
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?number_format($tenantContract->tenant_contract_receipt_amt, 3)."OMR":"NA"}} </span></h5>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div class="sub-head">Document Upload   </div>
            <div class="dataSearchBox">    
                <div class="card-body row">
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Document :  </b><span>
                            @if(!empty($tenantContract->tenantDocument)) 
                              @foreach ($tenantContract->tenantDocument  as $doc) 
                                <a href="{{ route('tenantContractDownload',[$doc->id,'tenantContract'])}}">
                                    {{$doc->tenant_documents_name}}  
                                </a>
                              @endforeach    
                  
                            @endif
                            </span></h5>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
  </div>

  </div>
  <!-- ends-->

  <!-- Document Show Starts -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Open For Termination Document List</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Sl No</th>
                <th>Document</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($openTerminationDocument as $key=>$document)
            <tr>
                <td>{{ ++$key }}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a></td>

            </tr>                     
            @empty
            <tr>
                <td colspan="3" >
                  <p>No Record</p>
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
<!-- Document Show Ends -->

</div>

<div class="modal" id="myModal">

</div>


@endsection
@section('scripts')
@include('backoffice::Termination.termination_js') 

@endsection
