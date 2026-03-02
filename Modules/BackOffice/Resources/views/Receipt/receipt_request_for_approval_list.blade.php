@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Receipt Approval</div>
    </div>
    {{ Breadcrumbs::render('receiptsRequestForApprovalTabViewList') }} 
    
  </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
        @include('backoffice::Receipt.receipt_list_advance_search') 
            
        </div>
    </div>
</div>
<div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
            <header class="panel-heading panel-heading-gray custom-tab ">
                  
                <ul class="nav nav-tabs">
                     
                    <li class="nav-item"><a href="{{ url('receiptsRequestApproval/rent') }}"  class="{{($tab=='rent')?'active':''}}">Rent</a>
                    </li><!-- active -->
                    
                     
                    <li class="nav-item"><a href="{{ url('receiptsRequestApproval/deposit') }}"  class="{{($tab=='deposit')?'active':''}}">Deposit</a>
                    </li>
                     
                    <li class="nav-item"><a href="{{ url('receiptsRequestApproval/general') }}"  class="{{($tab=='general')?'active':''}}">General</a>
                    </li>  
                                                         
                </ul>
                
            </header>
<div class="panel-body">
    <div class="tab-content">
    <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $receiptslist])         
                 </div>                                     
        
        <div class="tab-pane   active" id="rent">
            <div class="clearfix"></div>
            <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>
                                
                                <th title="Serial no">Sl No.</th>
                                <th title="Receipt No">@sortablelink('receipts_generation_receipt_no','Rec No',[],[ 'class' => 'sort_url'])</th>
                                @if($tab=='rent')
                                <th title="Receipt Date" >@sortablelink('receipts_generation_eff_from','Eff.From Date',[],[ 'class' => 'sort_url']) </th>
                                <th title="Receipt Date" >@sortablelink('receipts_generation_eff_to','Eff.To Date',[],[ 'class' => 'sort_url']) </th>
                                @endif
                                <th title="Building Name">@sortablelink('building_name','Bldge. Name',[],[ 'class' => 'sort_url'])</th>
                                <th title="Building Code">@sortablelink('building_code','Bldge. Code',[],[ 'class' => 'sort_url'])</th>
                                <th title="Tenant Name">@sortablelink('tenant_name','Tenant Name',[],[ 'class' => 'sort_url'])</th>
                                <th title="Tenant Code">@sortablelink('tenant_code','Tenant Code',[],[ 'class' => 'sort_url'])</th>
                                <th title="Agreement No">@sortablelink('tenant_contract_no','Agre. No',[],[ 'class' => 'sort_url'])</th>
                                <th title="Payment Method">@sortablelink('receipts_generation_payment_method','Pay Method',[],[ 'class' => 'sort_url'])</th>
                                <th title="Amount">@sortablelink('receipts_generation_amt','Amount',[],[ 'class' => 'sort_url'])</th>
                                <th title="Amount">Status</th>
                                <th width="20%" title="Action">Action</th>
                           </tr>
                        <tr> 
                           
                          <td> </td>
                          <td><input  type="text" name="receipt_no" class="search_fields mob" id="receipt_no" value=""></td>
                          @if($tab=='rent')
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31"  type="date" name="receipts_generation_eff_from" class="search_fields" id="receipts_generation_eff_from" value=""></td>
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31"  type="date" name="receipts_generation_eff_to" class="search_fields" id="receipts_generation_eff_to" value=""></td>
                          @endif
                          <td><input autocomplete="off" type="text" name="sales_mobile_no" class="search_fields mob" id="bldg_name" value="{{old('building_name')}}"></td>
                          <td><input autocomplete="off" type="text" name="unitTypes" class="search_fields mob" id="bldg_code" value="{{old('building_code')}}"></td>
                          <td><input autocomplete="off" type="text" name="location" class="search_fields mob" id="tenant_name" value="{{old('tenant_name')}}"></td>
                          <td><input autocomplete="off" type="text" name="assigned_person" class="search_fields assigned_person"  id="tenant_code" value="{{old('tenant_code')}}"> </td>
                          <td><input autocomplete="off" type="text" name="sales_note" class="search_fields" id="contract_no" value="{{old('tenant_contract_no')}}"></td>
                          <td>
                          <select name="payment_type" id="payment_type" class="search_fields">
                            <option value="">Select</option>
                                <option value="1" {{(old('receipts_generation_payment_method')? 'SELECTED':'') }} >Cheque</option>
                                <option value="2" {{(old('receipts_generation_pant_method')? 'SELECTED':'') }} >Cash</option>
                               
                            </select>
                          </td>
                          <td><input autocomplete="off" type="text" name="amount" class="search_fields" id="amount" value="{{old('receipts_generation_amt')}}"> </td>
                          <td> <select name="pdc_check" id="status" class="search_fields">
                            <option value="">Select</option>
                                
                                <option value="2" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Pending</option>
                                <option value="5" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Pending For Draft</option>
                            
                            </select></td>
                          <td> </td>
                        </tr>
                        </thead>
                         <tbody id="receipt-search">
                                @include('backoffice::Receipt.receipt_request_for_approval_list_ajax')
                        </tbody>
                        </table>
                        
                    </div>
                </div>

            <div class="row"  id="pagination">
                <div class="text-center">  

                {{$receiptslist->links()}}         

                </div>

            </div>

</div>
</div>
</div>
</div>
<!--Payment ends -->
<div class="clearfix"></div>

<!--Remaining Invoices ends -->
</div>
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('public/js/jquery.printPage.js')}}"></script>
@include('backoffice::Receipt.receipt_search_js')
@endsection
