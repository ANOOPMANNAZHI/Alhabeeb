<!--Agreement Section starts -->            
@if(count($tenantContracts)>0)
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="card card-box salesSearchBox " id="agdiv">
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

        </div>
    </div>
    <div class="sub-head">Unit Details</div>
    <div class="dataSearchBox">    
        <div class="card-body row">

           <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
                <input type="hidden" class="unit_id" name="unit_id" id="unit_id" value="{{ $tenantContract->unit->id}}">
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Unit Code :  </b><span><a href="{{route('unit.show',$tenantContract->unit->id)}}" target="_blank" title="View Unit"> {{$tenantContract->unit->unit_code}}</a></span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->Unit->unit_status_name}}</span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Vacant/Occupied :  </b><span>{{$tenantContract->Unit->vacant_status_name}}</span></h5>
            </div>
        </div>
       <!--  <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Deposit Status :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
            </div>
        </div> -->

    </div>
</div>
<div class="sub-head">Tenant Details</div>
<div class="dataSearchBox">    
    <div class="card-body row">

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Tenant Name :  </b><span><a href="{{route('tenants.show',$tenantContract->tenant->id)}}" title="View Tenant" target="_blank"> {{$tenantContract->tenant->tenant_name}}</a></span></h5>
        </div>
    </div> 
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
        </div>
    </div>

</div>
</div>
<div class="sub-head">Occupant Details</div>
<div class="dataSearchBox">    
    <div class="card-body row">

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Occupant Name:  </b><span><a href="{{route('tenants.show',$tenantContract->tenant->id)}}" title="View Tenant" target="_blank"> 

                @if($tenantContract->occupant_id)
                {{$tenantContract->occupant->occupant_name??''}}
                @else
                {{$tenantContract->tenant->tenant_name}}
                @endif
               
            </a></span></h5>
        </div>
    </div> 
    <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b> Occupant Mob No:  </b><span>
               
                 @if(isset($tenantContract->occupant->occupant_primary_contact_no))
                {{$tenantContract->occupant->occupant_primary_contact_no}}
                @else
                {{$tenantContract->tenant->tenant_contact_no}}
                @endif
                </span></h5>
        </div>
    </div>

</div>
</div>
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">    
    <div class="card-body row">

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
            <h5 class="details"><b>Rent:  </b><span><a href="{{route('tenants.show',$tenantContract->tenant->id)}}" title="View Tenant" target="_blank"> 
               
               {{ numberFormat($tenantContract->tenant_contract_rent)}} OMR
               
            </a></span></h5>
        </div>
    </div> 
    @if(isset($tenantContract->tenant_contract_duration_countdown))
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                 <h5 class="details"><b>Duration :  </b><span>
                @if(isset($fullContractDuration))

                    {{$fullContractDuration['year']}} Year
                    {{$fullContractDuration['month']}} Month
                    {{$fullContractDuration['day']}} Days
                @else
                  @php 
                  $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
                  @endphp
                  {{$duration[0]}} Year
                  {{$duration[1]}} Month
                  {{$duration[2]}} Days

                @endif
              </span></h5>
          </div>
      </div>
      @endif
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt)." OMR":"NA"}}</span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Guarantee Cheque:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?$tenantContract->tenant_contract_guarantee_cheque_details:"NA"}}</span></h5>
            </div>
        </div>
        


</div>
</div>
<div class="sub-head">Remark <a class="text-primary"><i class="fa fa-pencil"></i></a> 

<div id="msg" style="display: none;">
  
</div>

<div class="dataSearchBox">

    @if(isset($remark->Remark))
    <textarea class="form-control" rows="3" id="remark_sec">{{$remark->Remark}}</textarea>
    @else
    <textarea class="form-control" rows="3" id="remark_sec">{{isset($remark->Remark)}}</textarea>
    @endif
    <div class="button-group ">
        <button class="btn btn-tbl-view btn-xs mt-2" onclick="save_remark(this)">Save</button>
    </div>
</div>

<table class="table display product-overview mb-30" id="dtBasicExample">
  <thead>
    <tr>
        <th>Sl No</th>
        <th>Contract No</th>
        <th>Start Dt</th>
        <th>End Dt</th>
        <!-- <th>Rented By</th>
        <th>Duration</th> -->
        <th>Rent</th>
        <th>Payment Term</th>
        <th>Last Paid</th>
        <th>Terminated Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
    @php
    $i = 1;
    @endphp
    @forelse ($tenantContracts as $tenantContract)
    <tr>  
        <td>{{$i}}</td>                      
        <td>{{$tenantContract->tenant_contract_no}}</td>                         
        <td>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</td>                         
        <td>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</td> 
        <!-- <td></td>   
        <td>@if(!empty($tenantContract->tenant_contract_duration_countdown))
                  @php 
                  $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
                  @endphp
                  {{$duration[0]}} Year
                  {{$duration[1]}} Month
                  {{$duration[2]}} Days
      @endif</td>  -->  
        <td>{{ numberFormat($tenantContract->tenant_contract_rent)}}</td>
        <td>{{ $tenantContract->tenant_contract_payment_name ?: "NA" }}</td>
        {{-- display_last_paid_date falls back to the latest rent receipt when the
             contract column is still empty because the receipt is not posted to AX --}}
        <td>{{isset($tenantContract->display_last_paid_date)?$tenantContract->display_last_paid_date->format('d/m/Y'):"NA"}} </td>
        <td>{{isset($tenantContract->terminationContract)?$tenantContract->terminationContract->termination_date->format('d/m/Y'): "NA"}}</td>   
        <td>
            @if($tenantContract->tenant_contract_status == 0 &&
               $tenantContract->work_flow_processes_code == 108 )
               @if($tenantContract->tenant_renewal_termination_status == 8)
               {{'Terminated'}}
                @elseif($tenantContract->tenant_renewal_termination_status ==5 && $tenantContract->tenant_contract_effective_date > Carbon\Carbon::today() )
               {{'renewed'}}
               @else
               {{'Expired'}}
               @endif
            @else
            {{$tenantContract->TenantContractStatusName}}
            @endif
        </td>                       
        <td>
           <a  href="{{route('tenant-contract.show',$tenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="View" target="_blank">
              <i class="fa fa-eye"></i>
            </a>
            <!---  PDC Details ---- -->
            @if((Gate::check('pdc_generation') || Gate::check('pdc_generation_view')))
                <a  href="{{route('pdc.edit',$tenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC Generation" target="_blank">
                  <i class="fa fa-book"></i>
                </a> 
            @else
            @can('pdc_view')
            
                <a  href="{{route('pdcView',$tenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC View" target="_blank">
                  <i class="fa fa-book"></i>
                </a> 
            @endcan 
            @endif
            <!---  End PDC Details ---- -->

            <!---  Invoice Details ---- -->
            @if($tenantContract->work_flow_processes_code == 108 && Carbon\Carbon::today() >= $tenantContract->tenant_contract_effective_date  && Carbon\Carbon::today() <= $tenantContract->tenant_contract_valid_to_date)  
            @if(Gate::check('invoice_generation') || Gate::check('invoice_generation_view'))
            <a  href="{{route('invoice.show',$tenantContract->id)}}" class="btn btn-tbl-view btn-xs" title="{{($tenantContract->invoice_check==1)?'View Invoice':'Generate Invoice'}}" target="_blank">
              <i class="fa fa-files-o"></i>
            </a> 
            @endif 
            @endif 
            <!---  End Invoice Details ---- -->
            <!---  Receipt Details ---- -->

            @if($tenantContract->work_flow_processes_code == 108 && Gate::check('view_tenant_receipt'))
            <a  href="{{route('receiptsAgreementViewList',[$tenantContract->id,'rent'])}}" class="btn btn-tbl-view btn-xs" title="Receipts" target="_blank">
                <i class="fa fa-file-excel-o" aria-hidden="true"></i>
            </a>  
            @endif 
            @if($tenantContract->work_flow_processes_code == 108 && Gate::check('deposit_refund_view'))
            <a  href="{{route('depositRefund.index').'?tenantContract__id='.$tenantContract->id}}" class="btn btn-tbl-view btn-xs" title="Deposit Refund" target="_blank">
                <i class="fa fa fa-undo" aria-hidden="true"></i>
            </a>  
            @endif 

             <a  href="#movetoleagal" onclick="open_prompt({{$tenantContract->id}})" class="btn btn-tbl-view btn-xs" style="background-color: black;" title="Move to Leagal">
               <i class="fa fa-user"></i>
            </a>  

        </td>                         
    </tr>  
    @php $i++; @endphp
    @empty 
    <tr>
      <td colspan="10" align="center">
        <p>No Record</p>
    </td>
</tr>
@endforelse 
@php
$i++;
@endphp
</tbody>
</table>





</div>

@else
<div class="card card-box salesSearchBox " id="agdiv">
    <div class="dataSearchBox">    
        <div class="card-body row">
            <p class="text-center">No Records Found !</p>

        </div>
    </div>
</div>
@endif
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>


<!--Payment ends -->
