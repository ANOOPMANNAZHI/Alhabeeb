@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('search_reset',$route)
@section('search_url',$route)


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
          @if($are_id == '')
            <header class="panel-heading panel-heading-gray custom-tab ">
             
                <ul class="nav nav-tabs">
                     
                    <li class="nav-item"><a href="{{ url('receiptsTabViewList/rentReceiptGeneration/rent') }}"  class="{{($tab=='rent')?'active':''}}">Rent</a>
                    </li><!-- active -->
                    
                     
                    <li class="nav-item"><a href="{{ url('receiptsTabViewList/rentReceiptGeneration/deposit') }}"  class="{{($tab=='deposit')?'active':''}}">Deposit</a>
                    </li>
                     
                    <li class="nav-item"><a href="{{ url('receiptsTabViewList/rentReceiptGeneration/general') }}"  class="{{($tab=='general')?'active':''}}">General</a>
                    </li>  
                                                         
                </ul> 
            </header>
            @endif 
<div class="panel-body">
    <div class="tab-content">                                     
        <h4>
                <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $receiptslist])         
                 </div>
        @can('add_tenant_receipt')
        @if($tab=='rent')
			<a href="{{route('rentReceiptGeneration.create')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
		 @elseif($tab=='deposit')
             <a href="{{route('addDepositReceipt')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @elseif($tab=='general')
             <a href="{{route('addGeneralReceipt')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @else
             <a href="{{route('rentReceiptGeneration.create')}}"  class="btn btn-circle btn-primary  align-right">Add</a>
        @endif
        @endcan
        <div class="clr"></div>
        </h4>
        <div class="tab-pane   active" id="rent">
            <div class="clearfix"></div>
            <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>                                
                                <th title="Serial no">Sl No.</th>
                                <th title="Receipt No">@sortablelink('receipts_generation_receipt_no','Rec No',[],[ 'class' => 'sort_url'])</th>
                                <th title="Receipt Date">@sortablelink('receipts_generation_receipt_date','Rec Date',[],[ 'class' => 'sort_url'])</th>
                                <th title="Tenant Name">@sortablelink('tenant_name','Tenant Name',[],[ 'class' => 'sort_url'])</th>
                                <th title="Tenant Code">@sortablelink('tenant_code','Tenant Code',[],[ 'class' => 'sort_url'])</th>
                                <th title="Agreement No">@sortablelink('tenant_contract_no','Agre. No',[],[ 'class' => 'sort_url'])</th>
                                <th title="Unit No">@sortablelink('unit_no','Unit. No',[],[ 'class' => 'sort_url'])</th>
                                <th title="Building Name">@sortablelink('building_name','Bldg. Name',[],[ 'class' => 'sort_url'])</th>
                                @if($tab=='rent')
                                <th title="Receipt receipts_generation_eff_from" >@sortablelink('receipts_generation_eff_from','Eff.From Date',[],[ 'class' => 'sort_url']) </th>
                                <th title="receipts_generation_eff_to Date" >@sortablelink('receipts_generation_eff_to','Eff.To Date',[],[ 'class' => 'sort_url']) </th>
                                @endif
                                
                                <!-- <th title="Building Code">@sortablelink('building_code','Bldg. Code',[],[ 'class' => 'sort_url'])</th> -->
                                
                                <th title="Payment Method">@sortablelink('receipts_generation_payment_method','Pay Method',[],[ 'class' => 'sort_url'])</th>
                                <th title="Amount">@sortablelink('receipts_generation_amt','Amount',[],[ 'class' => 'sort_url'])</th>
                                <th title="Status">Status</th>
                                <th width="10%" title="Action">Action</th>
                            </tr>
                         
                        <tr> 
                           
                          <td> </td>
                          <td><input autocomplete="off" type="text" name="receipt_no" class="search_fields mob" id="receipt_no" value="{{old('receipts_generation_receipt_no')}}"></td>
                          <td><input autocomplete="off" type="date" name="receipt_date" class="search_fields" id="receipt_date" value="{{old('receipts_generation_receipt_date')}}" style="width:70px"></td>
                          <td><input autocomplete="off" type="text" name="tenant_name" class="search_fields mob" id="tenant_name" value="{{old('tenant_name')}}"></td>
                          <td><input autocomplete="off" type="text" name="tenant_code" class="search_fields" id="tenant_code" value="{{old('tenant_code')}}"  > </td>
                          <td><input autocomplete="off" type="text" name="contract_no" class="search_fields" id="contract_no" value="{{old('tenant_contract_no')}}"></td>
                          <td><input autocomplete="off" type="text" name="unit_no" class="search_fields mob" id="unit_no" value="{{old('unit_no')}}"></td>
                          <td><input autocomplete="off" type="text" name="bldg_name" class="search_fields mob" id="bldg_name" value="{{old('building_name')}}"></td>
                          @if($tab=='rent')
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31" type="date" name="receipts_generation_eff_from" class="search_fields" id="receipts_generation_eff_from" value="{{old('receipts_generation_eff_from')}}"  style="width:70px"></td>
                          <td><input autocomplete="off"  min="1970-01-01" max="2099-12-31" type="date" name="receipts_generation_eff_to" class="search_fields" id="receipts_generation_eff_to" value="{{old('receipts_generation_eff_to')}}" style="width:70px"></td>
                          @endif
                          
                          <!-- <td><input autocomplete="off" type="text" name="bldg_code" class="search_fields mob" id="bldg_code" value="{{old('building_code')}}"></td> -->
                          
                          <td>
                            <select name="receipts_generation_payment_method" id="payment_type" class="search_fields">
                            <option value="">Select</option>
                                <option value="1" {{(old('receipts_generation_payment_method')?'SELECTED':'') }} >Cheque</option>
                                <option value="2" {{(old('receipts_generation_payment_method')?'SELECTED':'') }} >Cash</option>
                                <option value="3" {{(old('receipts_generation_payment_method')?'SELECTED':'') }} >Bank Transfer</option>
                               
                            </select>
                          </td>
                          <td><input autocomplete="off" type="text" name="amount" class="search_fields" id="amount" value="{{old('receipts_generation_amt')}}"> </td>
                          <td>
                            <select name="pdc_check" id="status" class="search_fields"  style="width:70px">
                            <option value="">Select</option>
                                <option value="1" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Draft</option>
                                <option value="2" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Pending</option>
                                <option value="3" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Approved</option>
                                <option value="4" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Reject</option>
                                <option value="6" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Post</option>
                                <option value="5" {{(isset($request->receipts_generation_approval_status)? (old('receipts_generation_approval_status')? 'SELECTED':''):'') }} >Pending For Draft</option>
                            
                            </select>
                          </td>
                          <td><input type="hidden" id="action" value="{{route('receiptsTabViewList')}}">
                            <input type="hidden" id="tab" value="{{$tab}}">
                          </td>
                        </tr>
                    </thead>
                        <tbody id="receipt-search">
                                @include('backoffice::Receipt.receipt_list_ajax')
                        </tbody>
                        </table>
                        
                    </div>
                </div>
                <div id="are" are="{{$are_id}}"> </div>
                <div id="date" date="{{$date}}"> </div>
                <div id="pagination">
                    <div class="text-center">  

                     {{$receiptslist->appends(\Request::except(['page','_token']))->links()}}         
                
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

            $(this).css("pointer-events",'none');
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
