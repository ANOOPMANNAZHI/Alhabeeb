@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Open For Termination View</div>
  </div>
  {{ Breadcrumbs::render('tenantTermination.show',$tenantContract) }}
</div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">

      <div class="card-head">
       <div class="col">
        <h4>
            @if($tenantContract->terminationContract->termination_type_status == 0)
            @can('renewal_due_accept')
            <a  href="{{route('tenantRenewalStage',[$tenantContract->id,301,1,-1])}}" class="btn btn-circle btn-primary align-right" title="Renewing">
              Renewing
          </a>
          @endcan
          @can('termination_handover')
          <a href="{{route('tenantTerminationStage',[$tenantContract->terminationContract->id,$tenantContract->id,$tenantContract->terminationContract->work_flow_processes_code,'ACPT'])}}" title="Handover" class="btn btn-circle btn-primary align-right">
              Handover
          </a> 
          @endcan
          @else
          @if(in_array($tenantContract->terminationContract->termination_type_status,[1,4]) )
                <!-- <button title="Send For Approval" type="button" class="btn btn-tbl-general btn-xs Approval" data-toggle="modal" data-target="#myModal" datas-id="{{$tenantContract->terminationContract->id}}" data-id="2">
                   
            </button> -->
            @can('tenant_termination_send_approval')
            <a href="{{route('tenantTerminationOpenStatus',[$tenantContract->terminationContract->id,2])}}" title="Send For Approval" class="btn btn-circle btn-primary align-right">
                Send For Approval
            </a>
            @endcan
            @elseif($tenantContract->terminationContract->termination_type_status ==3)
            @can('termination_handover') 
            <a href="{{route('tenantTerminationStage',[$tenantContract->terminationContract->id,$tenantContract->id,$tenantContract->terminationContract->work_flow_processes_code,'ACPT'])}}" title="Handover" class="btn btn-circle btn-primary align-right">
              Handover
          </a>
          @endcan 
          @endif
          @endif
          @if($tenantContract->terminationContract->termination_type_status == 1 || $tenantContract->terminationContract->termination_type_status == 4)
          @can('tenant_early_termination')
          <a href="{{route('tenantTermination.edit',$tenantContract->terminationContract->id)}}" title="Edit" class="btn btn-circle btn-primary align-right">
           Edit
       </a>
       @endcan
       @endif
   </h4>
</div>
</div>
<!--Agreement Section starts -->
<div class="salesSearchBox">
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
                <h5 class="details"><b>Unit Type :  </b><span>{{$tenantContract->unit->unit->unit_types_name}}</span></h5>
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
        @if($tenantContract->occupant_id)
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
        @endif
    </div>
</div>
<div class="sub-head"></div>
<div class="dataSearchBox" id="reloaded">    
    <div class="card-body row">
        <input type="hidden" name="termination_id" id="termination_id" value="{{$tenantContract->terminationContract->id}}">
       <div class="col-lg-6 p-t-20">
                        <div class = "txt-full-width">
                            <h5 class="details"><b>TakeOver Date :  </b><span class="closeTaken">{{$tenantContract->terminationContract->termination_takenover_date->format('d/m/Y')?? "NA"}}</span>
                            <span class="openTaken" style="display: none">
                                <input type="date" name="termination_takenover_date" id="termination_takenover_date" class="form-controll" value="{{isset($tenantContract->terminationContract->termination_takenover_date)?$tenantContract->terminationContract->termination_takenover_date:''}}"></span>
                             <button class="editTakenOver"><i class="fa fa-pencil"></i></button>
                            <button class="saveTakenOverbtn" title="Save" style="display:none;"><i class="fa fa-save"></i></button>
                            <button class="closeTakenOverbtn" style="display:none;"><i class="fa fa-close"></i></button>
                        </div>
                    </div>

                    <div class="col-lg-6 p-t-20">
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Termination Date:  </b><span class="closeTermination">{{$tenantContract->terminationContract->termination_date->format('d/m/Y')?? "NA"}}</span>
                                @if($termination->termination_type_status ==1)
                            <span class="openTermination" style="display: none"><input type="date" name="termination_date" id="termination_date" class="form-controll" value="{{isset($tenantContract->terminationContract->termination_date)?$tenantContract->terminationContract->termination_date:''}}"></span>
                            <button class="editTermination"><i class="fa fa-pencil"></i></button>
                            <button class="saveTerminationbtn" title="Save" style="display:none;"><i class="fa fa-save"></i></button>
                            <button class="closeTerminationbtn" style="display:none;"><i class="fa fa-close"></i></button>
                            @endif
                        </h5>
                        </div>
                    </div>


       
        <div class="col-lg-6 p-t-20">
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark:  </b><span class="closeRemark">{{$tenantContract->terminationContract->termination_remark?? "NA"}}</span>
                            <span class="openRemark" style="display: none"><input type="text" name="termination_remark" id="termination_remark" class="form-controll"></span>
                            <button class="editRemark" title="Edit"><i class="fa fa-pencil"></i></button>
                            <button class="saveRemarkbtn" title="Save" style="display:none;"><i class="fa fa-save"></i></button>
                            <button class="closeRemarkbtn" title="close" style="display:none;"><i class="fa fa-close"></i></button>
                        </h5>
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
                    <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?$tenantContract->vaccant_date:"NA"}} </span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent Paid By Previous Tenant :  </b><span>{{isset($tenantContract->last_rent)? numberFormat($tenantContract->last_rent)." OMR":"NA"}} </span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Last Paid Rent:  </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? numberFormat($tenantContract->tenant_contract_last_paid_amt)." OMR":"NA"}} </span></h5>
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
                    <h5 class="details"><b>Deposit Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt)."OMR":"NA"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?$tenantContract->tenant_contract_guarantee_cheque_details:"NA"}} </span></h5>
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
                    <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?numberFormat($tenantContract->tenant_contract_receipt_amt)."OMR":"NA"}} </span></h5>
                </div>
            </div>
            
        </div>
    </div>
    
    @if(count($tenantContract->tenantDocument) > 0) 
    
    <div class="sub-head">Document Upload   </div>
    <div class="dataSearchBox">    
        <div class="card-body row">
          
           @foreach ($tenantContract->tenantDocument  as $doc) 
           <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Document :  </b><span>
                  
                    <a href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}" target="_blank"> {{$doc->tenant_documents_name}}  </a>
                    
                </span></h5>
            </div>
        </div>
        @endforeach    
        
    </div>
</div>

@endif
</div>
</div>
<!--Payment ends -->
<div class="clearfix"></div>
<!-- <div class="card-head">
  <header>Document Upload</header>
</div> -->
<!--Document Section starts -->
<!-- <div class="dataSearchBox">
  <div class="row">
    <div class="card-body row">
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Document:  </b><span><a href="">doc name</a></span></h5>
       </div>
     </div> 
   </div>
 </div>
</div> -->
<!--Document Section ends -->
<!-------------Previous Contract of Tenant --------------->
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Current And Previous Contracts</h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" >
              <thead>
                <tr style="background: #f5f5f5;">
                    <th>Agreement No</th>
                    <th>Name</th>
                    <th>Building</th>
                    <th>Unit</th>
                    <th>Start Dt</th>
                    <th>End Dt</th>
                    <th>Rent</th>
                    <th>OS</th>
                    <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($backHistory as $contract)
                <tr>
                    <td>{{$contract->tenant_contract_no}}</td>
                    <td>{{$contract->tenant->tenant_name}}</td>
                    <td>{{$contract->building->building_name}}</td>
                    <td>{{$contract->unit->unit_no}}</td>
                    <td>{{$contract->tenant_contract_start_date->format('d/m/Y')}}</td>
                    <td>{{$contract->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
                    <td>{{isset($contract->tenant_contract_rent)?numberFormat($contract->tenant_contract_rent):''}}</td>
                    <td>{{isset($contract->tenant_contract_os)?numberFormat($contract->tenant_contract_os):''}}</td>
                    <td>
                        <a target="_blank" href="{{route('tenant-contract.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        @if($contract->invoice_check !=null)
                        <a target="_blank" href="{{route('invoice.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="{{'Invoice'}}">
                            <i class="fa fa-files-o"></i>
                        </a>
                        @endif
                        <a target="_blank" href="{{route('pdcView',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC">
                            <i class="fa fa-book"></i>
                        </a>
                        <a target="_blank" href="{{route('rentReceiptFromTermination',[$contract->id])}}" class="btn btn-tbl-view btn-xs" title="Receipt">
                            <i class="fa fa-files-o"></i>
                        </a>
                    </td>
                </tr>
                @empty 
                <tr>
                   <td colspan="3" align="center">
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
</div>
</div>
<!--Remaining Invoices ends -->
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Termination Notes</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
        <div class="row">

          <div class="col leadInformation">
             <div class="table-responsive1">
                <table class="table" >
                  <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Note</th>
                        <th>Stage</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                    <tr>
                        <td>{{$comment->note}}</td>
                        <td>@if($comment->stage==501)
                            {{"Open For Termination"}}
                            @endif
                        </td>
                        <td>
                            {{$comment->createdBy?$comment->createdBy->employee->employee_name:'Admin'}}</td>
                            
                            <td>{{$comment->created_at->format('d/m/Y')}}</td>                    
                        </tr>
                        @empty 
                        <tr>
                         <td colspan="4" align="center">
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
</div>
</div>

<!--Other Dues Collected starts -->
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Other Dues Collected</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
        <div class="row">

          <div class="col leadInformation">
             <div class="table-responsive1">
                <table class="table" >
                  <thead>
                    <tr style="background: #f5f5f5;">
                      <th>Item</th>
                      <th>Amount</th>
                      <th>Date</th>
                  </tr>
              </thead>
              <tbody>
                @forelse ($otherDuesCollections as $otherDuesCollection)
                <tr>
                    <td>{{$otherDuesCollection->receipts_generation_receipt_no}}</td>
                    <td>{{isset($otherDuesCollection->receipts_generation_amt)?numberFormat($otherDuesCollection->receipts_generation_amt):''}}</td>
                    <td>{{$otherDuesCollection->receipts_generation_receipt_date->format('d/m/Y')}}</td>
                </tr>
                @empty 
                <tr>
                 <td colspan="3" align="center">
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
</div>
</div>
<!--Other Dues Collected starts -->
<!--Remaining Invoices starts -->
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Remaining Invoices</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
        <div class="row">

          <div class="col leadInformation">
             <div class="table-responsive1">
                <table class="table" >
                  <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Invoice No</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($remainingInvoices as $remainingInvoice)
                    <tr>
                        <td>{{$remainingInvoice->tenant_invoice_no}}</td>
                        <td>{{isset($remainingInvoice->tenant_invoice_amt)?numberFormat($remainingInvoice->tenant_invoice_amt):''}}</td>
                        <td>{{$remainingInvoice->tenant_invoice_date->format('d/m/Y')}}</td>
                    </tr>
                    @empty 
                    <tr>
                     <td colspan="3" align="center">
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
</div>
</div>
<!--Remaining Invoices ends -->



@if(count($openTerminationDocument) > 0)
<div class="row">
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
                  <th>Sl No.</th>
                  <th>Document</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($openTerminationDocument as $document)

                <tr>
                  <td>{{$loop->iteration}}</td>
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
</div>
@endif






<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Document Upload</h4></div>
    </div>
 <!-- Image upload-->
       <div class="card-body">          
        <div class="dataSearchBox ">
          <form autocomplete="off" action="{{route('terminationDocumentStore')}}" method="POST" id="img_form" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
            {{csrf_field()}}
            <input type="hidden" id="termination_doc_type" name="termination_doc_type[]" value="TerminationDocument">
            <div class="row">             
              <div class="col-sm-5">
                <div class="form-group">
                  <label for="report_image_file_name">Document   <!-- <button type="button" class="btn btn-primary add_button">Add Multiple Image</button> --></label>
                  <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="report_image_file_name form-control "  id="report_image_file_name"  name="report_image_file_name[]" data-rule-extension="pdf" data-msg-extension="Only allows PDF" required>
                    <input type="hidden" name="termination_id" value="{{$termination->id}}">
                    <input type="hidden" name="termination_contract" value="{{$termination->contract_id}}">
                    <input type="hidden" name="work_flow_processes" value="{{$termination->work_flow_processes_code}}">

                  </div>
                </div>
              </div>
              <div class="col-sm-2">
               <button type="submit" class="btn btn-primary" style="margin-top: 36px">Upload</button>
              </div>
              <div class="w-100"></div>
            </div>            

          </form>
        </div>
      </div>
  </div>
</div>
</div>




</div>
</div>


@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
    $(".editTakenOver").on('click',function(e){
        var valText = $(".closeTaken").text();
        var datesplit = valText.split('/');
       
        var takeOverDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];
       
        $("#termination_takenover_date").val(takeOverDt).show();
         var dtToday = new Date();

        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();
        if(month < 10)
            month = '0' + month.toString();
        if(day < 10)
            day = '0' + day.toString();
       
       // var maxDate = year + '-' + month + '-' + day;
        //$('#termination_takenover_date').attr('min', maxDate);
       // $('#termination_date').attr('min', takeOverDt);
       

        $(".openTaken").show();
        $(".closeTakenOverbtn").show();
        $(".saveTakenOverbtn").show();  
        $(".editTakenOver").hide();      
        $(".closeTaken").hide();

    });
/****************************************************************************/
    $(".editTermination").on('click',function(e){
        var valText = $(".closeTermination").text();
        var datesplit = valText.split('/');

        var terminateDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

        var valText = $(".closeTaken").text();
        var datesplit = valText.split('/');
       
        var takeOverDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

        var dtToday = new Date();

        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();
        if(month < 10)
            month = '0' + month.toString();
        if(day < 10)
            day = '0' + day.toString();
       
        var maxDate = year + '-' + month + '-' + day;

        $("#termination_date").val(terminateDt).show();
       // if(takeOverDt)

       //   $('#termination_date').attr('min', takeOverDt);
       // else
       //   $('#termination_date').attr('min', maxDate);
           
        $(".openTermination").show();
        $(".closeTerminationbtn").show();
        $(".editTermination").hide();
        $(".closeTermination").hide();
        $(".saveTerminationbtn").show();

    });
/****************************************************************************/
    $(".editRemark").on('click',function(e){
       
        var valText = $(".closeRemark").text();
        $("#termination_remark").val(valText);
        $(".openRemark").show();
        $(".closeRemark").hide();
        $(".closeRemarkbtn").show();
        $(".saveRemarkbtn").show();        
        $(".editRemark").hide();
    });
    $(".closeRemarkbtn").on('click',function(e){
         
          $(".closeRemark").show();
          $(".openRemark").hide();
          $(".closeRemarkbtn").hide();
          $(".saveRemarkbtn").hide();
          $(".editRemark").show();    

    });
    $(".closeTakenOverbtn").on('click',function(e){

        $(".closeTaken").show();
        $(".openTaken").hide();
        $(".closeTakenOverbtn").hide();
        $(".saveTakenOverbtn").hide();
        $(".editTakenOver").show();  

    });
    $(".closeTerminationbtn").on('click',function(e){

        $(".openTermination").hide();
        $(".editTermination").show();
        $(".closeTermination").show();
        $(".saveTerminationbtn").hide();
        $(".closeTerminationbtn").hide();    
     

    });
/****************************************************************************/
    // $("#termination_takenover_date,#termination_date,#termination_remark").on('change input',function(e){
     $(".saveTerminationbtn, .saveTakenOverbtn,.saveRemarkbtn").on('click',function(e){
        var termination_id              = $("#termination_id").val();
        var termination_takenover_date  = $("#termination_takenover_date").val();
        var termination_takenover_date_txt  = $(".closeTaken").text().split('/');
        var termination_date_txt            = $(".closeTermination").text().split('/');
        var termination_date            = $("#termination_date").val();
        var termination_remark          = $("#termination_remark").val();
        var clsName                     = $(this).attr('class');
        var dtToday                     = new Date();
        
        if(termination_takenover_date_txt && clsName=='saveTerminationbtn'){
            termination_takenover_dt = termination_takenover_date_txt[2]+'-'+   termination_takenover_date_txt[1]+'-'+termination_takenover_date_txt[0];
            termination_takenover_date = termination_takenover_dt;
            termination_date_dt = termination_date_txt[2]+'-'+   termination_date_txt[1]+'-'+termination_date_txt[0];
        }
        if(termination_date_txt && clsName=='saveTakenOverbtn'){
            termination_date_dt = termination_date_txt[2]+'-'+   termination_date_txt[1]+'-'+termination_date_txt[0];
            termination_date = termination_date_dt;
        }
        if(clsName=='saveTerminationbtn' && termination_takenover_date_txt==''){

            alert("Please Select TakeOver Date First..");
            return false;
        }
        else if(clsName=='saveTerminationbtn' && new Date(termination_date) < new Date(termination_takenover_dt)){

            alert("TakeOver Date Should Be Less Than Or Equal Termination Date. ! Are You Sure You Want To Continue?");
            
        }
        //else if(clsName=='saveTakenOverbtn' && new Date(termination_takenover_date) <= dtToday){

        //    alert("TakeOver Date Should Be Greater Than Today")
            
        //}
     
     
        if(clsName=='saveTakenOverbtn' && new Date(termination_takenover_date) >= new Date(termination_date_dt)){
                //termination_date = termination_takenover_date;
            alert("TakeOver Date Should Be Less Than Or Equal Termination Date. ! Are You Sure You Want To Continue?");   
        }
       
        if(termination_id && (termination_id || termination_takenover_date || termination_date )){
         
          $.ajax
          ({
            type: "POST",
            url: "{{route('terminationUpdateExtraFields')}}",
            data: {"termination_takenover_date":termination_takenover_date,"termination_id":termination_id,"termination_date":termination_date,"termination_remark":termination_remark,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
               if(termination_takenover_date && clsName=='saveTakenOverbtn'){
                var d = new Date(termination_takenover_date);  
                var day = d.getDate();
                var month_index = d.getMonth()+1;
                var year = d.getFullYear(); 
                 
                if(day < 10) day = '0'+day;
                if(month_index < 10) month_index = '0'+month_index;
                $(".closeTaken").text( day + "/" + month_index + "/" + year).show();
                /*
                if(new Date(termination_takenover_date)  >= new Date(termination_date_dt)){
                   
                    var d = new Date(termination_date);  
                    var day = d.getDate();
                    var month_index = d.getMonth()+1;
                    var year = d.getFullYear(); 
                    if(day < 10) day = '0'+day; 
                    if(month_index < 10) month_index = '0'+month_index;
                    $(".closeTermination").text( day + "/" + month_index + "/" + year).show();
                    alert("Termination Date reseted Due To TakeOver Date Greater Than Termination Date");
                }
                */
                $(".closeTaken").show();
                $(".openTaken").hide();
                $(".closeTakenOverbtn").hide();
                $(".saveTakenOverbtn").hide();
                $(".editTakenOver").show();
               
              }
              else if(termination_remark && clsName=='saveRemarkbtn'){
                  $(".closeRemark").text(termination_remark).show();
                  $(".closeRemarkbtn").hide();
                  $(".saveRemarkbtn").hide();
                  $(".openRemark").hide();
                 
                  $(".editRemark").show();
                 
              }
             else if(termination_date && clsName=='saveTerminationbtn'){
                   
                    var d = new Date(termination_date);  
                    var day = d.getDate();
                    var month_index = d.getMonth()+1;
                    var year = d.getFullYear();  
                    if(day < 10) day = '0'+day;
                    if(month_index < 10) month_index = '0'+month_index;
                    $(".closeTermination").text( day + "/" + month_index + "/" + year).show();
                    $(".openTermination").hide();
                    $(".editTermination").show();
                    $(".saveTerminationbtn").hide();
                    $(".closeTerminationbtn").hide();    
           
              }
            }
        });
       }
       else{

            alert("Termination Value Is Incorrect");
            return false;
        }
     });

/****************************************************************************/
  });
</script>
@endsection
