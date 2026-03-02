@extends('layouts.plms-app')



@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">@if($enquiry->sales_type == 1) Tenant @else Landlord @endif Enquiry</div>
    </div>
    {{ Breadcrumbs::render('enquiryViewInLandlord') }}
  </div>
</div>


<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <div class="sub-head">Enquiry Details</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Enquiry No :  </b><span>{{$enquiry->sales_enquiry_no ?? ''}}</span></h5>
            </div>
          </div> 
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Enquiry Date  :  </b><span>{{$enquiry->created_at->format('d/m/Y') ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Region :  </b><span>{{$enquiry->sales_region_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Unit Type :  </b><span>{{$enquiry->unitTypes->implode('unit_types_name',', ')}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>No. Of Units :  </b><span>{{$enquiry->sales_no_of_unit ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Predefined Areas :  </b><span>{{$enquiry->locations->implode('locations_name',', ')}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Price Range :  </b><span>{{$enquiry->priceRanges->implode('price_ranges_name',', ')}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Square Meter :  </b><span>{{$enquiry->sales_size ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Tenent Type :  </b><span>{{ !empty($enquiry->tenant_type_id)?  $enquiry->tenantType->tenant_types_name: '' }}</span></h5>
            </div>
          </div>

          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Move In Date :  </b><span>{{ !empty($enquiry->sales_move_in_date)?  $enquiry->sales_move_in_date->format('m/Y'): '' }}</span></h5>
            </div>
          </div>


        </div>
      </div>
      <div class="sub-head">Customer Details</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Customer Name  :  </b><span>{{$enquiry->sales_enquiry_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Mobile No  :  </b><span>{{$enquiry->sales_mobile_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Alternative No  :  </b><span>{{$enquiry->alternative_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Customer Email :  </b><a href="mailto:{{$enquiry->sales_email}}" target="_top"><span>{{$enquiry->sales_email ?? ''}}</span></a></h5>
            </div>
          </div>
        </div>
      </div>
      @if(isset($enquiry->tenantContract))
      <!-- Documentation starts -->
      <div class="sub-head">Documentation</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Name  :  </b><span>{{$enquiry->tenantContract->building->building_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Building Code  :  </b><span>{{$enquiry->tenantContract->building->building_code ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Unit No  :  </b><span>{{$enquiry->tenantContract->Unit->unit_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Unit Code :  </b><span>{{$enquiry->tenantContract->Unit->unit_code ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Unit Usage  :  </b><span>{{$enquiry->tenantContract->unit_usage ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Unit Type  :  </b><span>{{$enquiry->tenantContract->Unit->unit->unit_types_name ?? ''}}</span></h5>
            </div>
          </div>
        </div>
      </div>
      <!-- Documentation ends -->
      <!-- Tenant Section starts -->
      <div class="sub-head">Tenant Section</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Tenant Name  :  </b><span>{{$enquiry->tenantContract->tenant->tenant_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Tenant Company Name  :  </b><span>{{$enquiry->tenantContract->tenant->tenant_company_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Residence ID No  :  </b><span>{{$enquiry->tenantContract->tenant->resident_id ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Mobile No :  </b><span>{{$enquiry->tenantContract->tenant->tenant_contact_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Nationality :  </b><span>{{$enquiry->tenantContract->tenant->nationality->nationality ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Tenant Designation  :  </b><span>{{$enquiry->tenantContract->tenant->designation->unit_types_name ?? ''}}</span></h5>
            </div>
          </div>
        </div>
      </div>
      @endif
      <!-- Tenant Section ends -->
      <!-- Contract Section starts -->
      @if($enquiry->tenantContract)
      <div class="sub-head">Contract Section</div>
      <div class="dataSearchBox">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Contract No :  </b><span>{{$enquiry->tenantContract->tenant_contract_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Contract Date  :  </b><span>{{$enquiry->tenantContract->created_at->format('d/m/Y') ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Municipality Agreement No  :  </b><span>{{$enquiry->tenantContract->tenant_contract_muncipality_agr_no ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Rent :  </b><span>{{number_format($enquiry->tenantContract->tenant_contract_rent ?? '',3)}} OMR</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Contract Value :  </b><span>{{number_format($enquiry->tenantContract->tenant_contract_value ?? '',3)}} OMR</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Start Date :  </b><span>{{$enquiry->tenantContract->tenant_contract_start_date->format('d/m/Y') ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Effective Date :  </b><span>{{$enquiry->tenantContract->tenant_contract_effective_date->format('d/m/Y') ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>End Date :  </b><span>{{$enquiry->tenantContract->tenant_contract_valid_to_date->format('d/m/Y') ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Payment Method :  </b><span>{{$enquiry->tenantContract->TenantContractPaymentName ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Duration :  </b><span>
                @php 
                $duration = explode('-',$enquiry->tenantContract->tenant_contract_duration_countdown)
                @endphp


                {{$duration[0]}} Year
                {{$duration[1]}} Month
                {{$duration[2]}} Days
              </span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Marketing Executive Name :  </b><span>{{$enquiry->tenantContract->marketExecutiveEmployeeInfo->employee_name ?? ''}}</span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Attachment :  </b><span>
               @foreach ($enquiry->tenantContract->tenantDocument  as $doc) 
               <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}" target="_blank"><i class="fa fa-file" aria-hidden="true"></i>
                 {{$doc->tenant_documents_name}} 
               </a>
               @endforeach
             </span></h5>
           </div>
         </div>
       </div>
     </div>
     @endif
	</div>
     <!-- Contract Section ends -->
     <!-- Sales Note ends -->
     @if(count($salesNotes)> 0)
      <div class="card card-box salesLeadBox">
		  <div class="card-head">
			<div class="col"><h4>Note</h4></div>
		  </div>
     
  
        <div class="card-body ">
           
            <div class="table-responsive1">
                <table class="table" id="note_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Progress</th>
                            <th>Date & time</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse ($salesNotes as $salesNote)
                        <tr>
                            <td>{{$salesNote->sales_notes_note}} </td>
                            <td class="d-t">{{$salesNote->created_at->format('d/m/Y h:m A')}}</td>
                           
                        </tr>                        
                        @empty
                        <tr>
                            <td colspan="2" align="center">
                            <p>No Record</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
     
        </div>
      @endif
     <!--Sales Note ends -->
     <!-- Enquiry Stage Note Starts --> 
     @if(count($allNotes))
      <div class="card card-box salesLeadBox">
		  <div class="card-head">
			<div class="col"><h4>Enquiry Stage Note</h4></div>
		  </div>
    
        <div class="card-body ">
                         <div class="table-responsive1">
                            <table class="table" id="note_datatable">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Stage</th>
                                        <th>Note</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                
                                   @forelse ($allNotes as $note)
                                 
                                   @if($note->sales_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->sales_notes}}</td>
                                        <td>{{$note->createdBy->employee->employee_name ??$note->createdBy->username }} </td>
                                        <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
                                    </tr> 
                                    @endif                       
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
     <!-- Enquiry Stage Note ends -->
   </div>
   @endif
 </div>
</div>
@endsection

