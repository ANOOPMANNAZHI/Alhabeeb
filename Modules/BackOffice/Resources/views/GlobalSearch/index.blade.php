@extends('layouts.plms-app')
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Global Search</div>
    </div>

    {{ Breadcrumbs::render('contractGlobleSearch') }}

  </div>
</div>
<div class="row collapse show" id="show" style="">
   <div class="col-md-12 col-sm-12 dashboardtab">
      <div class="panel tab-border card-box">
         <div class="panel-body ">
            <div class="dataSearchBox ">
               <!-- http://localhost/Plms_new/depositRefund -->
               <form autocomplete="off" action="{{route('contractGlobleSearch')}}" method="POST" class="form-horizontal" data-toggle="validator">
                  {{csrf_field()}}
                  <div class="row">
                     <div class="col-sm-12">
                        <div id="search_form" class="">
                           <div class="row">
                              <div class="col-sm-4">
                                 <div class="form-group">
                                    <label for="fieldName">Select</label>
                                    <select class="form-control fieldName" required="" name="fieldName">
                                       <option value="">Select </option>
                                       <option value="building_code" {{(request()->fieldName=='building_code')?'SELECTED':''}}>Building Code</option>
                                       <option value="building_name" {{(request()->fieldName=='building_name')?'SELECTED':''}}>Building Name</option>
                                       <option value="unit_no" {{(request()->fieldName=='unit_no')?'SELECTED':''}}>Unit No</option>
                                       <option value="tenant_name" {{(request()->fieldName=='tenant_name')?'SELECTED':''}}>Tenant Name</option>
                                       <option value="tenant_contact_no" {{(request()->fieldName=='tenant_contact_no')?'SELECTED':''}}>Tenant Mobile No</option>
                                       <option value="resident_id" {{(request()->fieldName=='resident_id')?'SELECTED':''}}>ID No</option>
                                       <option value="locations_name" {{(request()->fieldName=='locations_name')?'SELECTED':''}}>Building Location</option>
                                       <option value="tenant_employer_name" {{(request()->fieldName=='tenant_employer_name')?'SELECTED':''}}>Company</option>
                                       <option value="tenant_contact_email" {{(request()->fieldName=='tenant_contact_email')?'SELECTED':''}}>Email</option>
                                       <option value="unit_types_name" {{(request()->fieldName=='unit_types_name')?'SELECTED':''}}>Unit Type</option>
                                       <option value="tenant_contract_muncipality_agr_no" {{(request()->fieldName=='tenant_contract_muncipality_agr_no')?'SELECTED':''}}>Muncipality Agreement No</option>
                                       <option value="service_report_no" {{(request()->fieldName=='service_report_no')?'SELECTED':''}}>Service Report No</option>
                                       <option value="complaint_no" {{(request()->fieldName=='complaint_no')?'SELECTED':''}}>Complaint No</option>
                                    </select>
                                 </div>
                              </div>
                              <div class="col-sm-4">
                                 <div class="form-group">
                                    <label for="fieldValue"> Value</label>
                                    <input autocomplete="off" required="" type="text" class="form-control fieldValue" name="fieldValue" placeholder="Enter Value" value="{{request()->fieldValue}}">
                                 </div>
                              </div>
                              <div class="col-sm-1 mt-3">
                                 <div class="dataSearchLabel w-100"></div>
                                 <button type="submit" name="submit" value="submit" class="btn btn-primary">Search</button>
                              </div>                              
                           </div>
                        </div>
                     </div>                     
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
@if(isset(request()->submit))
<div class="row">
   <div class="col-md-12 col-sm-12">
      <div class="card  card-box"> 
         <div class="card-body ">
            <!--<h4>
               <div id="pagination_info">
                  <div class="col-md-12 col-lg-12">
                    <div class="dataTables_info flex-wrap ">Showing 
                      
                    </div>
                 </div>         
               </div> 
              <div class="clr"></div>
            </h4> -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a href="{{$url['sales']}}" class="{{$tab=='sales'?'active':'nav-link'}}" id="home-tab1"   role="tab" aria-controls="home" aria-selected="true">Sales</a>
              </li>
              <li class="nav-item">
                <a href="{{$url['backoffice']}}" class="{{$tab=='backoffice'?'active':'nav-link1'}}" id="profile-tab1"   role="tab" aria-controls="profile" aria-selected="false">BackOffice</a>
              </li>
              <li class="nav-item">
                <a href="{{$url['maintenance']}}" class="{{$tab=='maintenance'?'active':'nav-link1'}}" id="contact-tab1"  href="#contact" role="tab" aria-controls="contact" aria-selected="false">Maintenance</a>
              </li>
              <li class="nav-item">
                <a href="{{$url['inspection']}}" class="{{$tab=='inspection'?'active':'nav-link1'}}" id="profile-tab1"  role="tab" aria-controls="profile" aria-selected="false">Inspection</a>
              </li>
              <li class="nav-item">
                <a href="{{$url['lease']}}" class="{{$tab=='lease'?'active':'nav-link1'}}" id="contact-tab1" aria-selected="false">Leasing</a>
              </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">

               @if($tab=='sales')
               <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                  <div class="table-wrap">
                     <div class="table-responsive">  
                        
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                           <thead>
                              <tr>

                               <th>Enquiry No</th>
                               <th>Enquiry Date</th>
                               <th>Customer No</th>
                               <th>Area</th>
                               <th>Unit Type</th>
                               <th>Enquiry Owner</th>
                               <th>Enquiry Status</th>
                              </tr>
                           </thead>
                        
                              @forelse($searchResult as $res)
                              <tr>
                                 <td><a target="__blank" href="{{route('enquiry.show',$res->salesEnquiryId)}}" >{{$res->sales_enquiry_no}}</a></td>
                                 <td>{{date('d-m-Y',strtotime($res->created_at))}}</td>
                                 <td>{{$res->sales_enquiry_name}}</td>
                                 <td>{{isset($res->sales_size)?$res->sales_size:''}}</td>
                                 <td>{{isset($res->unit_types_name)?$res->unit_types_name:''}}</td>
                                 <td>{{isset($res->enquiry_owner)?$res->enquiry_owner:''}}</td>
                                 <td>{{$res->work_flow_processes_name}}</td>
                              </tr>
                               @empty
                               <tr>
                                 <td colspan="7" align="center">
                                  <p>No Record</p>
                                </td>
                              </tr>
                              @endforelse
                                                   
                        </table>
                     </div>
                  </div>
                  @if(count($searchResult)>0)
                  <div class="page-nation">
                  {{$searchResult->appends(['fieldName'=> request()->fieldName,'fieldValue'=> request()->fieldValue,'per_page' =>6,'tab'=>'sales','submit'=>'submit'])->links()}}
                  </div>
                  @endif
               </div>
               @elseif($tab=='backoffice') 
               <!-------------- Second Tab Content ----------------------------->
               <div class="tab-pane active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                  <div class="table-wrap">
                     <div class="table-responsive">  
                        
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                           <thead>
                              <tr>
                                  <th>Contract No</th>
                                  <th>Tenant Name</th>
                                  <th>Building</th>
                                  <th>Unit</th>
                                  <th>Unit Type</th>
                                  <th>Duration</th>
                                  <th>Valid To</th>
                                  <th>Contract Status</th>
                                  <th>Last Paid Upto</th>
                                  <th>Over Due</th>
                                  <th>ARE</th>
                                  <th>Muncipality Reg</th>
                              </tr>
                           </thead>
                          
                              @forelse($searchResult as $res)
                              <tr>
                                 <td><a target="__blank" href="{{route('tenant-contract.show',$res->contractId)}}" >{{$res->tenant_contract_no}}</a></td>
                                 <td>{{$res->tenant_name}}</td>
                                 <td>{{$res->building_name}}</td>
                                 <td>{{$res->unit_no}}</td>
                                 <td>{{$res->unit_types_name}}</td>
                                 <td>{{explode('-',$res->tenant_contract_duration_countdown)[0].' Yr '.explode('-',$res->tenant_contract_duration_countdown)[1].' Month '.explode('-',$res->tenant_contract_duration_countdown)[2].' Days '}}</td>
                                 <td>{{date('d-m-Y',strtotime($res->tenant_contract_valid_to_date))}}</td>
                                 <td>{{($res->tenant_contract_status==1)?'Active':'Expired'}}</td>
                                 <td>{{empty($res->tenant_contract_last_paid_date)?'':$res->tenant_contract_last_paid_date}}</td>
                                 <td>{{isset($res->tenant_contract_os)?numberFormat($res->tenant_contract_os).' OMR':0}}</td>
                                 <td>{{isset($res->employee_name)?$res->employee_name:''}}</td>
                                 <td>{{($res->tenant_contract_is_reg_municipality==1)?'Registered':'Not'}}</td>                                 
                              </tr>
                               @empty
                               <tr>
                                 <td colspan="12" align="center">
                                  <p>No Record</p>
                                </td>
                              </tr>
                              @endforelse
                                                  
                        </table>
                     </div>
                  </div>
                  @if(count($searchResult)>0)
                  <div class="page-nation">
                  {{$searchResult->appends(['fieldName'=> request()->fieldName,'fieldValue'=> request()->fieldValue,'per_page'=>6,'tab'=>'backoffice','submit'=>'submit'])->links()}}
                  </div>
                  @endif
               </div>
               @elseif($tab=='maintenance') 
               <!-------------- Third Tab Content ----------------------------->
               <div class="tab-pane active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                  <div class="table-wrap">
                     <div class="table-responsive">  
                        
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                           <thead>
                              <tr>
                                  <th>Complaint No</th>
                                  <th>Complaint Date</th>
                                  <th>Tenant</th>
                                  <th>Building</th>
                                  <th>Unit No</th>
                                  <th>Complaint Status</th>
                                  <th>Service No</th>
                                  <th>Invoice No</th>
                                  <th>Teachnician</th>
                                  <th>Sub-Ticket No</th>                                  
                              </tr>
                           </thead>
                           
                              @forelse($searchResult as $res)
                              <tr>
                                 <td>
                                 @if(isset($res->contractId))                                 
                                 <a target="__blank" href="{{route('tenant-contract.show',$res->contractId)}}" >{{$res->complaint_no}}</a>
                                 @else
                                 {{$res->complaint_no}}
                                 @endif
                                 </td>
                                 <td>{{date('d-m-Y',strtotime($res->complaint_date))}}</td>
                                 <td>{{$res->tenant_name}}</td>
                                 <td>{{$res->building_name}}</td>
                                 <td>{{isset($res->unit_no)?$res->unit_no:''}}</td>
                                 <td>{{($res->complaint_status==1)?'Partialy Closed':($res->complaint_status==2)?'Closed':'Open'}}</td>
                                 <td>{{($res->service_report_no==null)?'':$res->service_report_no}}</td>
                                 <td>{{empty($res->maintenance_invoice_no)?'':$res->maintenance_invoice_no}}</td>
                                 <td>{{isset($res->employee_name)?$res->employee_name:''}}</td>
                                 <td>{{isset($res->complaint_ticket_no)?$res->complaint_ticket_no:''}}</td>
                              </tr>
                               @empty
                               <tr>
                                 <td colspan="11" align="center">
                                  <p>No Record</p>
                                </td>
                              </tr>
                              @endforelse
                                                       
                        </table>
                     </div>
                  </div>
                  @if(count($searchResult)>0)
                  <div class="page-nation">
                  {{$searchResult->appends(['fieldName'=> request()->fieldName,'fieldValue'=> request()->fieldValue,'per_page'=>6,'tab'=>'maintenance','submit'=>'submit'])->links()}}
                  </div>
                  @endif
               </div>
               @elseif($tab=='inspection') 
                <!-------------- Fourth Tab Content ----------------------------->
               <div class="tab-pane active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                     <div class="table-wrap">
                        <div class="table-responsive">  
                           
                           <table class="table display product-overview mb-30" id="dtBasicExample">
                              <thead>
                                 <tr>
                                     <th>Contract No</th>
                                     <th>Tenant Name</th>
                                     <th>Building</th>
                                     <th>Unit</th>
                                     <th>Handover Status</th>
                                     <th>Termination Status</th>
                                     <th>Assign</th>
                                     <th>Assign Status</th>
                                     <th>Last Paid Upto</th>
                                     <th>Takeover Date</th>                                  
                                 </tr>
                              </thead>
                           
                                 @forelse($searchResult as $res)
                                 <tr>
                                    <td>
                                       <!--- HandOver -------->
                                       @if($res->work_flow_processes_code==503)    
                                       <a href="{{route('handoverAssigned')}}" >{{$res->tenant_contract_no}}</a>
                                       <!--- Takeover -------->
                                       @else
                                       <a href="{{route('takeoverForTermination')}}" >{{$res->tenant_contract_no}}</a>
                                       @endif
                                    </td>
                                    <td>{{$res->tenant_name}}</td>
                                    <td>{{$res->building_name}}</td>
                                    <td>{{$res->unit_no}}</td>
                                    <td></td>
                                    <td>{{($res->tenant_renewal_termination_status==7)?'Termination Request':($res->tenant_renewal_termination_status==8)?'Terminated':'Active'}}</td>
                                    <td>{{date('d-m-Y',strtotime($res->tenant_contract_valid_to_date))}}</td>
                                    <td>{{($res->tenant_contract_status==1)?'Active':'Expired'}}</td>
                                    <td>{{empty($res->tenant_contract_last_paid_date)?'':date('d-m-Y',strtotime($res->tenant_contract_last_paid_date))}}</td>
                                    <td>{{isset($res->termination_takenover_date)?date('d-m-Y',strtotime($res->termination_takenover_date)):''}}</td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12" align="center">
                                     <p>No Record</p>
                                   </td>
                                 </tr>
                                 @endforelse
                                                      
                           </table>
                        </div>
                     </div>
                     @if(count($searchResult)>0)
                     <div class="page-nation">
                     {{$searchResult->appends(['fieldName'=> request()->fieldName,'fieldValue'=> request()->fieldValue,'per_page'=>6,'tab'=>'inspection','submit'=>'submit'])->links()}}
                     </div>
                     @endif
               </div>                 
               @elseif($tab=='lease') 
               <!-------------- Fifth Tab Content ----------------------------->
               <div class="tab-pane active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                     <div class="table-wrap">
                        <div class="table-responsive">  
                           
                           <table class="table display product-overview mb-30" id="dtBasicExample">
                              <thead>
                                 <tr>
                                     <th>Landlord Contract No</th>
                                     <th>Vendor Name</th>
                                     <th>Building</th>
                                     <th>Management Type</th>
                                     <!--<th>Contract Type</th>-->
                                     <th>Duration</th>
                                     <th>Valid To</th>
                                     <th>Contract Value</th>
                                     <th>Invoice No</th>                                  
                                 </tr>
                              </thead>
                 
                                 @forelse($searchResult as $res)
                                 <tr>
                                    <td><a target="__blank" href="{{route('landlord-contract.show',$res->lcontractId)}}" >{{$res->landlord_contract_no}}</a></td>
                                    <td>{{$res->vendor_name}}</td>
                                    <td>{{$res->building_name}}</td>
                                    <td>{{$res->management_types_name}}</td>
                                    <td>{{isset($res->month_duration)?$res->month_duration.' Month':'Open'}}</td>
                                    <td>{{isset($res->landlord_contract_valid_to_date)?date('d-m-Y',strtotime($res->landlord_contract_valid_to_date)):''}}</td>
                                    <td>{{numberFormat($res->landlord_contract_amt)}} OMR</td>
                                    <td>{{empty($res->landlord_invoice_voucher_no)?'':$res->landlord_invoice_voucher_no}}</td>
                                 </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9" align="center">
                                     <p>No Record</p>
                                   </td>
                                 </tr>
                                 @endforelse
                                                    
                           </table>
                        </div>
                     </div>
                     <div class="page-nation">
                     {{$searchResult->appends(['fieldName'=> request()->fieldName,'fieldValue'=> request()->fieldValue,'per_page'=>6,'tab'=>'lease','submit'=>'submit'])->links()}}
                     </div>
               </div>
               @endif
           
            </div>
         
        </div>
      </div>
   </div>
</div>
@endif
@endsection