@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Receipt</div>
    </div>
    {{ Breadcrumbs::render('receiptsTabViewList') }} 
    
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
                     
                    <li class="nav-item"><a href="{{ url('receiptsAgreementViewList/rentReceiptGeneration/'.$id.'/rent') }}"  class="{{($tab=='rent')?'active':''}}">Rent</a>
                    </li><!-- active -->
                    
                     
                    <li class="nav-item"><a href="{{ url('receiptsAgreementViewList/rentReceiptGeneration/'.$id.'/deposit') }}"  class="{{($tab=='deposit')?'active':''}}">Deposit</a>
                    </li>
                     
                    <li class="nav-item"><a href="{{ url('receiptsAgreementViewList/rentReceiptGeneration/'.$id.'/general') }}"  class="{{($tab=='general')?'active':''}}">General</a>
                    </li>  
                                                         
                </ul>
                
            </header>
<div class="panel-body">
    <div class="tab-content">                                     
        <h4>
        @if(auth()->user()->can('add_tenant_receipt') &&
        $contractInfo->tenant_contract_status ==1)
        @if($tab=='rent')

            <a href="{{route('rentReceiptGeneration.create')}}"  class="btn btn-circle btn-primary  align-right">Add</a>

        @elseif($tab=='deposit')
             <a href="{{route('addDepositReceipt')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @elseif($tab=='general')
             <a href="{{route('addGeneralReceipt')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @else
            <a href="{{route('rentReceiptGeneration.create')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @endif
        @endif
          <div class="clr"></div>
        </h4>
        <div class="tab-pane   active" id="rent">
            <div class="clearfix"></div>
            <div class="table-wrap">
                    <div class="table-responsive1">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>
                                
                                <th title="Serial no">Sl No.</th>
                                <th title="Receipt No">Rec No</th>
                                <th title="Receipt Date" >Rec Date </th>
                                <th title="Building Name">Bldge. Name</th>
                                <th title="Building Code">Bldge. Code</th>
                                <th title="Tenant Name">Tenant Name</th>
                                <th title="Agreement No">Agre. No</th>
                                @if($tab=='rent')
                                <th title="Receipt receipts_generation_eff_from" >Eff.From Date </th>
                                <th title="receipts_generation_eff_to Date" >Eff.To Date </th>
                                @endif
                                <th title="Payment Method">Pay Method</th>
                                <th title="Amount">Amount</th>
                                <th title="Status">Status</th>
                                <th width="30%" title="Action">Action</th>
                            </tr>
                         
                        <tr> 
                           
                          <td> </td>
                          <td><input autocomplete="off" type="text" name="receipt_no" class="search_fields mob" id="receipt_no" value=""></td>
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31" onkeydown="return false" type="date" name="receipt_dt" class="search_fields" id="receipt_dt" value=""></td>
                          <td><input autocomplete="off" type="text" name="bldg_name" class="search_fields mob" id="bldg_name" value=""></td>
                          <td><input autocomplete="off" type="text" name="bldg_code" class="search_fields mob" id="bldg_code" value=""></td>
                          <td><input autocomplete="off" type="text" name="tenant_name" class="search_fields mob" id="tenant_name" value=""></td>
                          <td><input autocomplete="off" type="text" name="contract_no" class="search_fields" id="contract_no" value=""></td>
                          @if($tab=='rent')
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31" type="date" name="receipts_generation_eff_from" class="search_fields" id="receipts_generation_eff_from" value="{{old('receipts_generation_eff_from')}}"></td>
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31" type="date" name="receipts_generation_eff_to" class="search_fields" id="receipts_generation_eff_to" value="{{old('receipts_generation_eff_to')}}"></td>
                          @endif
                          <td>
                            <select name="payment_type" id="payment_type" class="search_fields">
                            <option value="">Select</option>
                                <option value="1" {{(isset($request->payment_type)? (old('status')? 'SELECTED':''):'') }} >Cheque</option>
                                <option value="2" {{(isset($request->payment_type)? (old('status')? 'SELECTED':''):'') }} >Cash</option>
                               
                            </select>
                          </td>
                          <td><input autocomplete="off" type="text" name="amount" class="search_fields" id="amount" value=""> </td>
                          <td>
                            <select name="pdc_check" id="status" class="search_fields">
                            <option value="">Select</option>
                                <option value="1" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >unapproval</option>
                                <option value="2" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >Pending</option>
                                <option value="3" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >Approved</option>
                                <option value="4" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >Reject</option>
                                <option value="6" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >Posted</option>
                                <option value="5" {{(isset($request->status)? (old('status')? 'SELECTED':''):'') }} >Pending For Unapprove</option>
                            
                            </select>
                          </td>
                          <td><input type="hidden" id="action" value="{{route('receiptsAgreementViewList',[$id,$tab])}}">
                            <input type="hidden" id="tab" value="{{$tab}}">
                          </td>
                        </tr>
                    </thead>
                        <tbody id="receipt-search">
                                @include('backoffice::Receipt.receipt_agreement_list_ajax')
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
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
<form id="post-form" action="" method="POST">
   {{csrf_field()}}
   <input type="hidden" name="receipt_id" value="" id="receiptId">
    <input value="post" style="display: none;" type="submit">
</form>
<form id="send-approve-form" action="" method="POST">
   {{csrf_field()}}
   <input type="hidden" name="receipt_id" value="" id="receiptIdForSendApproval">
   <input type="hidden" name="process_id" value="" id="process_id">
   <input value="post" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('public/js/jquery.printPage.js')}}"></script>
@include('backoffice::Receipt.receipt_search_js')
<script>
$(document).ready(function() {
    $(document).on('click','.delete_type',function(){  
    
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Receipt ?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
    });
    $(document).on('click','.post_type',function(){  
    
        var action = $(this).attr("href");
        var receiptId = $(this).attr("id"); 

        event.preventDefault();
        if (confirm('Do you want to Post this Receipt ?')) {
            jQuery("#receiptId").val(receiptId);
            jQuery("#post-form").attr('action', action);

            jQuery("#post-form").submit();
        } else {
            return false;
        }
    });
    $(document).on('click','.send_request_receipt',function(){  
    
        var action      = $(this).attr("href");
        
        var receiptId   = $(this).attr("id"); 

        if(action && receiptId){
            event.preventDefault();
            jQuery("#process_id").val(2);    
            jQuery("#receiptIdForSendApproval").val(receiptId);
            jQuery("#send-approve-form").attr('action', action);

            jQuery("#send-approve-form").submit();
        }
        else
            return false;
    });
    $(document).on('click','.send_request_unapprove',function(){  
     
        var action      = $(this).attr("href");
        var receiptId   = $(this).attr("id"); 

        if(action && receiptId){
            event.preventDefault();
            jQuery("#process_id").val(5);
            jQuery("#receiptIdForSendApproval").val(receiptId);
            jQuery("#send-approve-form").attr('action', action);

            jQuery("#send-approve-form").submit();
        }
        else
            return false;
    });
});
</script>
@endsection
