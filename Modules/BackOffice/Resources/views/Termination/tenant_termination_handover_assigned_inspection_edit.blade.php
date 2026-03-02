@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<style type="text/css">
  /* Some CSS styling */
  #sketchpadapp {
    /* Prevent nearby text being highlighted when accidentally dragging mouse outside confines of the canvas */
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
  .leftside {
    float:left;
    width:220px;
    height:285px;
    background-color:#def;
    padding:10px;
    border-radius:4px;
  }
  .rightside {
    float:left;
    margin-left:10px;
  }
  #sketchpad {
    float:left;
    border:2px solid #888;
    border-radius:4px;
    position:relative; /* Necessary for correct mouse co-ords in Firefox */
  }
  #clearbutton {
    font-size: 15px;
    padding: 10px;
    -webkit-appearance: none;
    background: #eee;
    border: 1px solid #888;
  }

</style>
@endsection
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Vacating Unit Inspection Details</div>
    </div>
    {{ Breadcrumbs::render('handoverAssignedInspectionEdit',$termination) }}
  </div>
</div>
<div class="row">
  <form id="save_inspection" method="POST" action ="{{route('terminationInspectionUpdate',$termination->id)}}"method="POST"  class="form-horizontal" enctype="multipart/form-data" data-toggle="validator"  >
    {{csrf_field()}}{{method_field('PUT')}}
    <input type="hidden" name="terminationId" value="{{$termination->id}}">
    <input type="hidden" name="work_flow_processes_code" value="{{$termination->work_flow_processes_code}}">
    <input type="hidden" name="contract_id" value="{{$termination->contract_id}}">
    <div class="col-sm-12">
      <div class="card-box">
        <div class="card-head">
        <!-- @if($termination->termination_review_status == 2)
          <a href="{{route('tenantTerminationStage',[$termination->id,$termination->tenantContract->id,$termination->work_flow_processes_code,'ACPT'])}}" title="TakenOver" class="btn btn-circle btn-primary  align-right">
            TakeOver
          </a>
          @endif -->
        </div>

        <div class="card-body row"> 

         <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Contract No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->tenant_contract_no}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Building Name  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->building->building_name}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Unit No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->unit->unit_no}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Tenant Name  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_name}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Mob No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_contact_no}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Location</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6">{{$tenantContract->building->location->locations_name??'NA'}}<span></span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Way No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenantContract->building->building_address??'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>OutStanding</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{ ($outstandingOs > 0)? "Yes": "No" }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-12">
   <div class="card-box">
    <div class="card-body row"> 
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Tenancy Start Date </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenancyStartDt->tenant_contract_start_date->format('d/m/Y')??"NA"}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Tenancy End Date </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')??"NA"}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Last Paid </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? number_format($tenantContract->tenant_contract_last_paid_amt, 3,",","")." OMR":"NA"}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>OutStanding Rent</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span> {{($outstandingOs > 0)? numberFormat($outstandingOs)." OMR":"NA"}}</span></div>
        </div>
      </div>
      @if(isset($tenantContract->tenant_contract_duration_countdown))
      <div class="col-md-6 p-t-10">
        <div class="row">
         <div class="col-md-5"><b> Total Duration </b></div>
         <div class="col-md-1 s-clm">:</div>
         <div class="col-md-6">
          @php 
          $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
          @endphp
          {{$duration[0]}} Year
          {{$duration[1]}} Month
          {{$duration[2]}} Days
        </div>
      </div>
    </div>
    @endif

    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b> TakenOver Date </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$termination->termination_takenover_date->format('d/m/Y')??'NA'}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Termination Date </b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$termination->termination_date->format('d/m/Y')??'NA'}}</span></div>
      </div>
    </div>
    <div class="col-md-6 p-t-10">
      <div class="row">
        <div class="col-md-5"><b>Remark</b></div>
        <div class="col-md-1 s-clm">:</div>
        <div class="col-md-6"><span>{{$termination->termination_remark}}</span></div>
      </div>
    </div> 

  </div>
</div>
</div>
<!--Agreement Section ends -->

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
<!--Current And Previous Contracts starts -->

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
                    <td>{{numberFormat($contract->tenant_contract_rent)}}</td>
                    <td>{{numberFormat($contract->tenant_contract_os)}}</td>
                    <td>
                        <a target="_blank" href="{{route('tenant-contract.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a target="_blank" href="{{route('invoice.show',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="Invoice">
                            <i class="fa fa-files-o"></i>
                        </a>
                        <a target="_blank" href="{{route('pdcView',$contract->id)}}" class="btn btn-tbl-view btn-xs" title="PDC">
                            <i class="fa fa-book"></i>
                        </a>
                        <a target="_blank" href="{{route('receiptsAgreementViewList',[$contract->id, 'rent'])}}" class="btn btn-tbl-view btn-xs" title="Receipt">
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
<!-- ends -->

<div class="clearfix"></div>
<div class="col-sm-12">
  <div class="card-box">
   <div class="card-head">
    <header>Electricity & Water</header>
  </div>
  <div class="card-body row">
    <div class="col-md-12">
      <h4></h4>
      <div class="col-md-12 p-t-10">
        <div class="row">
          <div class="col-md-2"><b>Electricity Acc/No</b></div>
          <div class="col-md-2"><input type="text" name="elec_acc" class="form-control " value="{{$termination->termination_electricity_acc_no}}"></div>
          <div class="col-md-2"><b>Closing Reading</b></div>
          <div class="col-md-2"><span><input type="text" name="elec_cls_read" class="form-control " value="{{$termination->termination_electricity_close_reading}}"></span></div>
          <div class="col-md-2"><span><b>Amount R O</b></span></div>
          <div class="col-md-2"><span><input type="text" name="elec_amt" class="form-control elec_water" value="{{numberFormat($termination->termination_electricity_amount)}}" id="elec_amt" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
        <div class="row">
          <div class="col-md-2"><b>Water Acc/No</b></div>
          <div class="col-md-2"><input type="text" name="water_acc" class="form-control " value="{{$termination->termination_water_acc_no}}"></div>
          <div class="col-md-2"><b>Closing Reading</b></div>
          <div class="col-md-2"><span><input type="text" name="water_cls_read" class="form-control " value="{{$termination->termination_water_close_reading}}"></span></div>
          <div class="col-md-2"><span><b>Amount R O</b></span></div>
          <div class="col-md-2"><span><input type="text" name="water_amt" class="form-control elec_water" value="{{numberFormat($termination->termination_water_amount)}}" id="water_amt" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
      </div>
    </div>
    
  </div>
</div>
</div>
<!--Building Details starts -->
<div class="col-sm-12">
  <div class="card-box">
   <div class="card-head">
    <header></header>
  </div>
  @php $i = 0 ;@endphp
  <div class="card-body row">
    @foreach($works as $work)
    @if(count($work->subWork) > 0)
    <div class="col-md-6">
      <h4>{{$work->works_code}}</h4>

      <div class="col-md-12 p-t-10">
        <div class="row">
          <div class="col-md-1"></div>
          <div class="col-md-5"><b></b></div>
          <div class="col-md-3 s-clm">Quantity</div>
          <div class="col-md-3"><span>Amount</span></div>
        </div>
      </div>
      <div class="col-md-12 p-t-10">
        @foreach($work->subWork as $key=>$subWork)
        @php
        $a = count($tenantContract->terminationChecklist->where('sub_work_id',$subWork->id));
        @endphp
        <input type="hidden" name="count" value="{{$i}}">
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id="{{preg_replace('/\s+/', '', $subWork->sub_work)}}{{$i}}" class = "mdl-switch__input sub_chk" name="addinspection{{$i}}" value="{{$subWork->id}}" {{isset($tenantContract->terminationChecklist)? ((in_array($subWork->id,$arry))?'checked':''):''}}  ></div>
          <div class="col-md-5"><b>{{$subWork->sub_work}}</b></div>
          <input type="hidden" name="checkId[]" value="{{preg_replace('/\s+/', '', $subWork->sub_work)}}{{$i}}">
          <div class="col-md-3 s-clm"><input type="text" name="quantity_{{$i}}" class="form-control quantity" value="@if($a>0){{$tenantContract->terminationChecklist->where('sub_work_id',$subWork->id)->first()->termination_quantity}}@endif" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"></div>
          <div class="col-md-3"><span><input type="text" name="amount_{{$i}}" class="form-control amount" value="@if($a>0){{$tenantContract->terminationChecklist->where('sub_work_id',$subWork->id)->first()->termination_amount}}@endif" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
        @php $i++; @endphp
        @endforeach
      </div>
    </div>
    @endif
    
    @endforeach
    <div class="col-md-6">
      <h4>Others</h4>
      <div class="col-md-12 p-t-10">
        <div class="row">
          <div class="col-md-1"></div>
          <div class="col-md-5"><b></b></div>
          <div class="col-md-3 s-clm">Quantity</div>
          <div class="col-md-3"><span>Amount</span></div>
        </div>
      </div>
      <div class="col-md-12 p-t-10">
        <input type="hidden" name="other_count" value="3">
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id = "Others0" class = "mdl-switch__input sub_chk otherCheck" name="addinspectionOther_0" value="Others" {{isset($tenantContract->terminationChecklistOther)? ((in_array("Others",$otherArray))?'checked':''):''}}></div>
          <div class="col-md-5"><b>Others</b></div>
          <input type="hidden" name="checkId[]" value="Others0">
          <div class="col-md-3 s-clm"><input type="text" name="addinspectionQuantity_0" class="form-control Otherquantity" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Others',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Others')->first()->termination_quantity:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"></div>
          <div class="col-md-3"><span><input type="text" name="addinspectionAmount_0" class="form-control Otheramount total_other" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Others',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Others')->first()->termination_amount:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id = "Rent1"      class = "mdl-switch__input sub_chk otherCheck" name="addinspectionOther_1" value="Rent" {{isset($tenantContract->terminationChecklistOther)? ((in_array("Rent",$otherArray))?'checked':''):''}}></div>
          <div class="col-md-5"><b>Rent</b></div>
          <input type="hidden" name="checkId[]" value="Rent1">
          <div class="col-md-3 s-clm"><input type="text" name="addinspectionQuantity_1" class="form-control Otherquantity" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Rent',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Rent')->first()->termination_quantity:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"></div>
          <div class="col-md-3"><span><input type="text" name="addinspectionAmount_1" class="form-control Otheramount total_other" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Rent',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Rent')->first()->termination_amount:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id = "MuncipalTax2"      class = "mdl-switch__input sub_chk otherCheck" name="addinspectionOther_2" value="Muncipal Tax"{{isset($tenantContract->terminationChecklistOther)? ((in_array("Muncipal Tax",$otherArray))?'checked':''):''}}></div>
          <div class="col-md-5"><b>Muncipal Tax</b></div>
          <input type="hidden" name="checkId[]" value="MuncipalTax2">
          <div class="col-md-3 s-clm"><input type="text" name="addinspectionQuantity_2" class="form-control Otherquantity" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Muncipal Tax',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Muncipal Tax')->first()->termination_quantity:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"></div>
          <div class="col-md-3"><span><input type="text" name="addinspectionAmount_2" class="form-control Otheramount total_other" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Muncipal Tax',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Muncipal Tax')->first()->termination_amount:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id = "AnyOtherCharges3"      class = "mdl-switch__input sub_chk otherCheck" name="addinspectionOther_3" value="Any Other Charges" {{isset($tenantContract->terminationChecklistOther)? ((in_array("Any Other Charges",$otherArray))?'checked':''):''}}></div>
          <div class="col-md-5"><b>Any Other Charges</b></div>
          <input type="hidden" name="checkId[]" value="AnyOtherCharges3">
          <div class="col-md-3 s-clm"><input type="text" name="addinspectionQuantity_3" class="form-control Otherquantity" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Any Other Charges',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Any Other Charges')->first()->termination_quantity:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values"></div>
          <div class="col-md-3"><span><input type="text" name="addinspectionAmount_3" class="form-control Otheramount total_other" value="{{isset($tenantContract->terminationChecklistOther)? ((in_array('Any Other Charges',$otherArray))?$tenantContract->terminationChecklistOther->where('termination_other_work','Any Other Charges')->first()->termination_amount:''):''}}" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)"></span></div>
        </div>
      </div>

    </div>
    <div class="col-md-6">
      <h4></h4>
      <div class="col-md-12 p-t-10">
        <div class="row">
          <div class="col-md-1"><input type = "checkbox" id = "switch-2"      class = "mdl-switch__input" name="termination_main_key_status" value="1" {{isset($termination->termination_main_key_status)? (($termination->termination_main_key_status)?'checked':''):''}}></div>
          <div class="col-md-5"><b>Main Door Key Received</b></div>
          <div class="col-md-1 s-clm">Notes</div>
          <div class="col-md-5"><span><textarea class="form-control" name="notes" required="">{{ isset($termination->termination_notes)?  old('notes',$termination->termination_notes): old('notes','')}}</textarea></span></div>
        </div>
      </div>
    </div>
    <div class="col-md-12 ">
      <h4></h4>
      <div class="col-md-12 p-t-10">
        <div class="row ">
          <div class="col-md-4 "><b>Total Amount</b></div>
          <div class="col-md-5 "><input type="text" name="total" class="form-control total " value="{{ isset($tenantContract->terminationChecklist)?  old('total',numberFormat($termination->termination_total_amount)): old('total','')}}" id="total" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" readonly></div>
        </div>
        <div class="row ">
          <div class="col-md-4 "><b>Total Of Electricity & Water</b></div>
          <div class="col-md-5 "><input type="text" name="elec_water_total" class="form-control total " value="{{ isset($tenantContract->terminationChecklist)?  old('elec_water_total',numberFormat($termination->termination_total_elec_water_amount)): old('elec_water_total','')}}" id="elec_water_total" readonly></div>
        </div>
		<div class="row ">
          <div class="col-md-4 "><b>Total Other</b></div>
          <div class="col-md-5 "><input type="text" name="total_others" class="form-control total " value="{{ isset($totalOtherAmt)?numberFormat($totalOtherAmt):'0.000'}}" id="total_others" readonly></div>
        </div>
        <div class="row ">
          <div class="col-md-4 "><b>Discount On Total Maintenance Due</b></div>
          <div class="col-md-5 "><input type="text" name="maintenance_due" class="form-control total " value="{{ isset($tenantContract->terminationChecklist)?  old('maintenance_due',numberFormat($termination->termination_discount_maintenance_due)): old('maintenance_due','')}}" id="maintenance_due"></div>
        </div>
        <div class="row ">
          <div class="col-md-4 "><b>Net Amount</b></div>
          <div class="col-md-5 "><input type="text" name="net" class="form-control total " value="{{ isset($tenantContract->terminationChecklist)?  old('net',numberFormat($termination->termination_net_amount)): old('net','')}}" id="net" readonly data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values"></div>
        </div>
      </div>
    </div>
    <div class="col-md-12 ">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>
  
</div>

</div>
</form>
{{--
  @if(empty($termination->termination_tenant_signature) && $termination->termination_review_status == 0)
  <div class="col-md-12">
    <div class="card-box">
      <div class="card-body">
        <div class="sub-head">Signature</div>
        <div class="dataSearchBox ">
          <iframe name="my_iframe" src="{{route('tenantTerminationSignature')}}" scroll="none" style="overflow: hidden;height: 350px; width:350px"></iframe>
          <input type="hidden" name="termination_Id" value="{{$termination->id}}">

        </div>
        
      </div>
    </div>
  </div>
  @endif
  --}}
  <!-- Image upload-->
   <!-- <div class="col-md-12">
    <div class=" card card-box salesLeadBox">
      <div class="card-body">
        <div class="sub-head">Image Upload</div>
          <div class="dataSearchBox ">
              <form autocomplete="off" action="{{route('imageUpload')}}" method="POST" id="img_form" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}}
                  <div class="row">
                    <div class="col-sm-5">
                      <div class="form-group">
                          <label for="building_img_category">Image Category</label>
                            <div class="p-relative">
                                <i class="fa fa-camera-retro icn-add" aria-hidden="true"></i>
                          <select class="form-control margin-top-8" id="termination_doc_type"  name="termination_doc_type[]" required>
                          <option value="">Select Category</option>                  
                          <option value="Electricity">Electricity</option>
                          <option value="Plumbing">Plumbing</option> 
                          <option value="Carpentry">Carpentry</option>
                          <option value="Others">Others</option>                               
                          </select>  
                          </div>              
                      </div>
                    </div>
                     <div class="col-sm-5">
                      <div class="form-group">
                          <label for="report_image_file_name">Image    <button type="button" class="btn btn-primary add_button">Add Multiple Image</button> </label>
                          <div class="p-relative">
                              <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                          <input type="file" class="report_image_file_name form-control "  id="report_image_file_name"  name="report_image_file_name[]" data-rule-extension="jpg|jpeg|png" data-msg-extension="Only allows jpg,jpeg and png" required>
                          <input type="hidden" name="termination_id" value="{{$termination->id}}">
                          <input type="hidden" name="termination_contract" value="{{$termination->contract_id}}">
                          <input type="hidden" name="work_flow_processes" value="{{$termination->work_flow_processes_code}}">
                         
                      </div>
                      </div>
                    </div>
                    <div class="col-sm-2">
                          <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                           <button type="button" class="btn btn-primary add_button">Add</button> 
                      </div>
                  <div class="w-100"></div>
                  </div>
                  <div class="field_wrapper">
                  
                  </div>
                  <div class="row">
                    <div class="col-sm-1">
                      <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                  </div> 

              </form>
          </div>
      </div>
    </div>
  </div>
-->


<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>List</h4></div>
    </div>
    <div class="card-body">
     <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Category</th>
                <th>Document</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($terminationDocument as $document)
              
              <tr>
                <td>{{$document->termination_doc_type}}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a> </td>

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
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){
   $('#save_inspection').validate();
   $('#img_form').validate();
 });
  /**********************************************************************/
    var maxField = 100; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="row ro"><div class="col-sm-6"><div class="form-group"><label for="report_image_file_name"></label><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="report_image_file_name form-control"  id="report_image_file_name"  name="report_image_file_name[]" data-rule-extension="jpg|jpeg|png" data-msg-extension="Only allowes jpg,jpeg and png" required></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 36px"></div><button type="button" class="btn btn-warning remove_doc_button"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
      var values = $("input[name='report_image_file_name[]']")
      .map(function(){
        if($(this).val())return $(this).val();}).get();
      
      var len = $("input[name='report_image_file_name[]']").length;
      if (($( ".report_image_file_name" ).is( ".report_image_file_name.form-control.error" )) || ( values.length != len )) {


      }else{
        //Check maximum number of input fields
        if(x < maxField){ 
              x++; //Increment field counter
              $(wrapper).append(fieldHTML); //Add field html
            }
          }
          
        });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
      e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Document')) {
         $.ajax({
          headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          url: '{{ url('tenant-contract') }}' + '/' + img_path_id,
          type: "DELETE",
          data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
        });
         $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
         }
         
        x--; //Decrement field counter
      });

    $(wrapper).on('click', '.remove_doc_button', function(e){
      e.preventDefault();

      $(this).closest('.ro').remove();
      $("#report_image_file_name-error").hide();
      
        x--; //Decrement field counter
      });
    /*************************************************************************/
    $('[type=checkbox]').change(function(){
      var sum = 0;
      var i = 0;
      $("[type=checkbox]").each(function(index) {
    if($(this).is(':checked')){//alert(index);
      var quantity = $(this).closest("div.row").find("input[name='quantity_"+index+"']").val();
      var amount = $(this).closest("div.row").find("input[name='amount_"+index+"']").val().replace(/,/g, '');
      var othetAmount = $(this).closest("div.row").find("input[class='addinspectionAmount_"+i+"']").val();
      if(quantity == "" || !$.isNumeric(quantity) )quantity = 0;
      if(amount == "" || !$.isNumeric(amount) )amount = 0;
      if(othetAmount == "" || !$.isNumeric(othetAmount) )othetAmount = 0;
        //total  = parseFloat(quantity) * parseFloat(amount);
        //alert(amount);
        sum += parseFloat(amount);
        if(othetAmount != 0)
          sum += parseFloat(othetAmount);
        i++;  
      }
      $("#total").val(formatNumber(sum.toFixed(3)));
      netAmount();
    });
      $(".otherCheck").each(function(index) {

        if($(this).is(':checked')){

          var amount = $(this).closest("div.row").find("input[name='addinspectionAmount_"+index+"']").val();
          
          if(amount == "" || !$.isNumeric(amount) )  amount = 0; //alert(amount);
          
          
          sum += parseFloat(amount);
          
          
        }
      $("#total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
      
    });
    /*************************************************************************/
    $(document).on('change','.elec_water',function(){
      var sum = 0;
      $('.elec_water').each(function() {
        var amt = $(this).val().replace(/,/g, '') ;
        if(amt == "" || !$.isNumeric(amt) )amt = 0;

        sum += parseFloat(amt);
        $("#elec_water_total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
    });
	/*************************************************************************/
  $(document).on('change','.total_other',function(){
      var sums = 0;
      $('.total_other').each(function() {
        //alert(sums);
        var amts = $(this).val().replace(/,/g, '');
        if(amts == "" || !$.isNumeric(amts) )amts = 0;

        sums += parseFloat(amts);
        $("#total_others").val(formatNumber(sums.toFixed(3)));
        netAmount();
      });
    });
     
    /*************************************************************************/
    $(document).on('change','.amount,.quantity',function(){
      var sum = 0;
      var className = $(this).attr('class');
      var Name = $(this).attr('name');
      var current_val = $(this).val();
      if(current_val !=""){

        var checkId = $(this).closest("div.row").find("input[name='checkId[]']").val();
        var check = $(this).closest("div.row").find("input[id="+checkId+"]").prop('checked', true);
        
      }else{

        var checkId = $(this).closest("div.row").find("input[name='checkId[]']").val();
        var check = $(this).closest("div.row").find("input[id="+checkId+"]").prop('checked', false);
        
      }
      $("[type=checkbox]").each(function(index) {
        if($(this).is(':checked')){
          var quantity = $(this).closest("div.row").find("input[name='quantity_"+index+"']").val();
          var amount = $(this).closest("div.row").find("input[name='amount_"+index+"']").val().replace(/,/g, '');
          var othetAmount = $(this).closest("div.row").find("input[name='addinspectionAmount_"+index+"']").val();
          if(quantity == "" || !$.isNumeric(quantity) )quantity = 0;
          if(amount == "" || !$.isNumeric(amount) )amount = 0;
          if(othetAmount == "" || !$.isNumeric(othetAmount) )othetAmount = 0;
          //total  = parseFloat(quantity) * parseFloat(amount);
          //alert(othetAmount);
          sum += parseFloat(amount);
          if(othetAmount != 0)
            sum += parseFloat(othetAmount);
          
        }
       $("#total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
      $(".otherCheck").each(function(index) {

        if($(this).is(':checked')){

          var amount = $(this).closest("div.row").find("input[name='addinspectionAmount_"+index+"']").val();
          
          if(amount == "" || !$.isNumeric(amount) )  amount = 0; //alert(amount);
          
          
          sum += parseFloat(amount);
          
          
        }
        $("#total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
    });
    /*************************************************************************/
    $(document).on('change','.Otheramount,.Otherquantity',function(){
  //var sum =  parseFloat($("#total").val());//alert(sum);
  
  var sum = 0;
  var className = $(this).attr('class');
  var Name = $(this).attr('name');
  var current_val = $(this).val();
  if(current_val !=""){

    var checkId = $(this).closest("div.row").find("input[name='checkId[]']").val();
    var check = $(this).closest("div.row").find("input[id="+checkId+"]").prop('checked', true);
    
  }else{

    var checkId = $(this).closest("div.row").find("input[name='checkId[]']").val();
    var check = $(this).closest("div.row").find("input[id="+checkId+"]").prop('checked', false);
    
  }
  var i = 0;
  $("[type=checkbox]").each(function(index) {
    if($(this).is(':checked')){
      var quantity = $(this).closest("div.row").find("input[name='quantity_"+index+"']").val();
      var amount = $(this).closest("div.row").find("input[name='amount_"+index+"']").val().replace(/,/g, '');
      var othetAmount = $(this).closest("div.row").find("input[name='addinspectionAmount_"+index+"']").val();
      if(quantity == "" || !$.isNumeric(quantity) )quantity = 0;
      if(amount == "" || !$.isNumeric(amount) )amount = 0;
      if(othetAmount == "" || !$.isNumeric(othetAmount) )othetAmount = 0;
          //total  = parseFloat(quantity) * parseFloat(amount);
          //alert(othetAmount);
          sum += parseFloat(amount);
          if(othetAmount != 0)
            sum += parseFloat(othetAmount);
          
        }
        $("#total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
  $(".otherCheck").each(function(index) {

    if($(this).is(':checked')){

      var amount = $(this).closest("div.row").find("input[name='addinspectionAmount_"+index+"']").val();
      
          if(amount == "" || !$.isNumeric(amount) )  amount = 0; //alert(amount);
          
          
          sum += parseFloat(amount);
          
          i++;   
        }
        $("#total").val(formatNumber(sum.toFixed(3)));
        netAmount();
      });
});
    /*************************************************************************/
    $(document).on('change','.total',function(){
      netAmount();
    });
    /*************************************************************************/
  function netAmount(){
      var sum = 0;
      var total = $("#total").val().replace(/,/g, '') ;
      var el_wa_total = $("#elec_water_total").val().replace(/,/g, '') ;
      var total_others = $("#total_others").val().replace(/,/g, '') ;
      var maintenance_due = $("#maintenance_due").val() ;
      var net = $("#net").val() ;
      if(total == "" )total = 0;
      if(el_wa_total == "" )el_wa_total = 0;
      if(total_others == "" )total_others = 0;
      if(maintenance_due == "" || !$.isNumeric(maintenance_due))maintenance_due = 0;

      sum = parseFloat(total) + parseFloat(el_wa_total) + parseFloat(total_others) - parseFloat(maintenance_due);
      //if(sum < 0)sum =0;
      $("#net").val(formatNumber(sum.toFixed(3)));
    }

  </script>
  @endsection
