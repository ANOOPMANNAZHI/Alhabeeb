@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }
</style>
@endsection

@section('search_url', route('depositRefundApproval')) 
@section('search_reset', route('depositRefundApproval')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Deposit Refund Approval</div>
    </div>
    {{ Breadcrumbs::render('depositRefundApproval') }}
  </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
  <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">

      @include('backoffice::Transaction.transaction_search')   

    </div>
  </div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
     <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $depositRefundApprovals])         
                 </div>
                 <div class="clr"></div>
            </h4>
 <div class="table-wrap">
 <div class="table-responsive">	
        <table class="table display product-overview mb-30" id="dtBasicExample">
          <thead>
            <tr>
              <th>Sl No</th>
              <th>@sortablelink('deposit_refund_no','Refund No',[],[ 'class' => 'sort_url' ])</th>
              <th>@sortablelink('deposit_refund_date','Refund Dt',[],[ 'class' => 'sort_url' ])</th>
              <th>@sortablelink('receiptGeneration.receipts_generation_receipt_no','Deposit Receipt No',[],[ 'class' => 'sort_url' ])</th>
              <th>Building Name</th>
              <th>Building Code</th>
              <th>Unit No</th>
              <th>Tenant Name</th>
              <th>Tenant Code</th>
              <th>@sortablelink('tenantContract.tenant_contract_no','Agreement No',[],[ 'class' => 'sort_url' ])</th>
              <th>@sortablelink('deposit_refund_payment_method','Payment Method',[],[ 'class' => 'sort_url' ])</th>
              <th>@sortablelink('deposit_refund_amt','Refund Amount',[],[ 'class' => 'sort_url' ])</th>
              <th>@sortablelink('deposit_refund_approval_status','Purpose',[],[ 'class' => 'sort_url' ])</th>
              <th>Action</th>
            </tr>
            <tr>
              <td></td>
              <td> <input autocomplete="off" type="text" name="deposit_refund_no" class="search_fields mob" id="deposit_refund_no" value="{{old('deposit_refund_no')}}" ></td>

              <td><input autocomplete="off" type="date" name="deposit_refund_date" class="search_fields mob" id="deposit_refund_date" value="{{old('deposit_refund_date')}}" ></td>

              <td><input autocomplete="off" type="text" name="receipts_generation_id" class="search_fields mob" id="receipts_generation_id"  value="{{old('receiptGeneration__receipts_generation_receipt_no')}}" ></td>

              <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('tenantContract__building__building_name')}}" ></td>

              <td><input autocomplete="off" type="text" name="building_code" class="search_fields mob" id="building_code"  value="{{old('tenantContract__building__building_code')}}" ></td>

              <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('tenantContract__Unit__unit_code')}}" ></td>

              <td><input autocomplete="off" type="text" name="tenant_name" class="search_fields mob" id="tenant_name"  value="{{old('tenantContract__tenant__tenant_name')}}" ></td>

              <td><input autocomplete="off" type="text" name="tenant_code" class="search_fields mob" id="tenant_code"  value="{{old('tenantContract__tenant__tenant_code')}}" ></td>

              <td><input autocomplete="off" type="text" name="tenant_contract_id" class="search_fields mob" id="tenant_contract_id"  value="{{old('tenantContract__tenant_contract_no')}}" ></td>


              <td><select name="deposit_refund_payment_method" class="searchFields search_fields mob" id="deposit_refund_payment_method" style="width:100px;">
                <option value="">Show All</option>
                <option {{ (old('deposit_refund_payment_method') == 2)? 'selected' : '' }} value="2">{{'Cash'}}</option>
                <option {{ (old('deposit_refund_payment_method') == 1)? 'selected' : '' }} value="1">{{'Cheque'}}</option>
              </select>
            </td>

            <td><input autocomplete="off" type="text" name="deposit_refund_amt" class="search_fields mob" id="deposit_refund_amt"  value="{{old('deposit_refund_amt')}}" ></td>

            <td>
              <select name="deposit_refund_approval_status" class="searchFields search_fields mob" id="deposit_refund_approval_status" style="width:100px;">
                <option value="">Show All</option>
                <option {{ (old('deposit_refund_approval_status') == 2)? 'selected' : '' }} value="2">{{'Approval'}}</option>
                <option {{ (old('deposit_refund_approval_status') == 3)? 'selected' : '' }} value="3">{{'UnApproval'}}</option>
              </select>
            </td>

            <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('depositRefundApprovalSearch')}}" ></td>
            <td></td>
          </tr>
        </thead>
        <tbody id="enquiry-search">

        @include('backoffice::Transaction.deposit_refund_approval_list_ajax')  

        </tbody>
      </table>
    </div>
</div>

    <div class="row "  id="pagination">                   
    {{$depositRefundApprovals->appends(\Request::except(['page','_token','ajax']))->links()}} 
  </div> 

</div>
</div>
</div>
</div>
<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts') 
@include('backoffice::Transaction.deposit_refund_search_js')
@include('backoffice::Transaction.deposit_search_js') 
<script>   
  /**********************************************************************************/

  $("#show").on("hide.bs.collapse", function(){
    $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
  });
  $("#show").on("show.bs.collapse", function(){
    $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
  });


  /*********************************************************************************************/
  $(document).ready(function() {

    $(document).on('click', '.closed',function(e) {        
      var id = $(this).attr('data-id');
      var status = $(this).attr('data-status');
      $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('cancelDepositRefund')}}", // This is the url we gave in the route
            data: {'id':id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
      return true;
    });
  });
  /*********************************************************************************************/
</script>

@endsection
