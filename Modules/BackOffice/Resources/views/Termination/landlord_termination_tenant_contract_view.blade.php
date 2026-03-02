@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Contract View</div>
        </div>
        @if($flag == 0)
        {{ Breadcrumbs::render('TerminatedContractLandlordByTenantView',$tenantContract,$termination) }}
        @else
           {{ Breadcrumbs::render('tenantContractByLandlordApproval',$tenantContract,$termination) }}
        @endif
            
         
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
         <div class="row">
                <div class="col-sm-12">

                  
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
                            <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Name :  </b><span>{{$tenantContract->occupant->occupant_name}}</span></h5>
                        </div>
                    </div> 
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Mob No:  </b><span>{{$tenantContract->occupant->occupant_primary_contact_no?? "NA"}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Occupant Email:  </b><span>{{$tenantContract->occupant->occupant_email ?? "NA"}}</span></h5>
                        </div>
                    </div>
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
                            <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContract->tenant_contract_value)?number_format($tenantContract->tenant_contract_value, 3):"NA"}} OMR</span></h5>
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
                            <h5 class="details"><b>Rent Paid by Previous Tenant :  </b><span>{{isset($tenantContract->last_rent)? number_format($tenantContract->last_rent, 3)." OMR":"NA"}} </span></h5>
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
                            <h5 class="details"><b>PDC  :  </b><span>{{$tenantContract->tenant_contract_note}} </span></h5>
                        </div>
                    </div>
                    @endif
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
            </div>
        </div>    
        
     
</div> 


@endsection
@section('scripts')

@endsection
