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

 @section('search_reset',url()->current())

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title"> {{ (url()->current() == route('landlordInvoiceApproval'))? 'Landlord Invoice Approval' : 'Landlord Invoice '  }}    </div>
        </div>
         {{ Breadcrumbs::render('landlordInvoicesApproval') }}
    </div>
</div>

<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>

<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
           @include('backoffice::GeneralLedger.search') 
 
        </div>
    </div>
</div>

 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
          <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $landlordInvoices])         
                 </div>
                  <div class="clr"></div>
            </h4>
               <div class="card-body ">
			  <div class="table-wrap">
			  <div class="table-responsive">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                       <th>Sl No.</th>
                       <th>@sortablelink('landlord_invoice_voucher_no','Voucher No.',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('landlord_invoice_voucher_date','Voucher Date',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('vendor_name','Landlord Name',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('vendor_code','Landlord Code',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('landlord_contract_no','Agreement No',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('landlord_contract_payment_type','Payment Term',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('landlord_invoice_doc_type','Doc Type',[],[ 'class' => 'invoice_sort'])</th>
                       <th>@sortablelink('landlord_given_invoice_no','Ref No.')</th>
                       <th>@sortablelink('landlord_invoice_amt','Contract Amt')</th>
                       <th>@sortablelink('landlord_invoice_approval_status','Purpose')</th>
                       <th>Action</th>
                    </tr>   

                     <tr>
                     <td></td>
                     <td> <input  type="text" name="landlord_invoice_voucher_no" class="search_fields " id="landlord_invoice_voucher_no" value="{{old('landlord_invoice_voucher_no')}}" ></td>
                     <td> <input  type="date" name="landlord_invoice_voucher_date" class="search_fields " id="landlord_invoice_voucher_date" value="{{old('landlord_invoice_voucher_date')}}" ></td>
                     <td> <input  type="text" name="landlordInfo__vendor_name" class="search_fields " id="landlordInfo__vendor_name" value="{{old('landlordInfo__vendor_name')}}" ></td>  
                     <td> <input  type="text" name="landlordInfo__vendor_code" class="search_fields " id="landlordInfo__vendor_code" value="{{old('landlordInfo__vendor_code')}}" ></td>  
                     <td>  <input  type="text" name="landlordContractInfo__landlord_contract_no" class="search_fields " id="landlordContractInfo__landlord_contract_no" value="{{old('landlordContractInfo__landlord_contract_no')}}" ></td>  
                     <td>  <input  type="text" name="landlordContractInfo__paymentMethodInfo__payment_method_code" class="search_fields " id="landlordContractInfo__paymentMethodInfo__payment_method_code" value="{{old('landlordContractInfo__paymentMethodInfo__payment_method_code')}}" ></td>  
                     <td> <input  type="text" name="landlord_invoice_doc_type" class="search_fields " id="landlord_invoice_doc_type" value="{{old('landlord_invoice_doc_type')}}" > </td> 
                    <td>  <input  type="text" name="landlord_given_invoice_no" class="search_fields " id="landlord_given_invoice_no" value="{{old('landlord_given_invoice_no')}}" ></td>  
                    <td>  <input  type="text" name="landlordContractInfo__landlord_contract_amt" class="search_fields " id="landlordContractInfo__landlord_contract_amt" value="{{old('landlordContractInfo__landlord_contract_amt')}}" ></td>  
            <td>
            <select name="landlord_invoice_approval_status" class="searchFields search_fields mob" id="landlord_invoice_approval_status" style="width:100px;">
              <option value="">Show All</option>                                    
              <option {{ ( old('landlord_invoice_approval_status') && old('landlord_invoice_approval_status') == 2)? 'selected' : '' }} value="2">Approval</option>
              <option {{ (old('landlord_invoice_approval_status') == 3)? 'selected' : '' }} value="3">Unapprove</option>
            </select>
          </td>
          <td></td>


                     </tr>               
                </thead>
                <tbody id="search">                                                               
                  
               @include('backoffice::LandlordInvoice.invoice_list_ajax') 
                </tbody>
                </table>
                   </div>     
                 <div class="row"  id="pagination">                                 
                       {{$landlordInvoices->appends(\Request::except(['page','_token','ajax']))->links()}}
                </div> 
                   </div> 
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="curr_url" id="curr_url" value="{{url()->current()}}">


@endsection


@section('scripts') 
  

<script>
  $(document).ready(function() {

    $(document).on('click','.delete',function (event) {
                if (confirm('Do you want to Delete this Invoice?')) {
                     return true;
                } else {
                    event.preventDefault();
                    return false;
                }
            });

   $(document).on('click','.confirm',function (event) {
                var action = $(this).attr("href");
              
                if (confirm('Do you want to Continue?')) {
                      return true;
                } else {
                    event.preventDefault();
                    return false;
                }
            });



 
    $(document).on('click','.add_search',function(){     


     var filter =   $(this).closest('.row').clone();  
   
    $(filter).find(".add_search")
             .removeClass("add_search").addClass('remove_search').end()
             .find(".remove_search").html('Remove').end()
             .appendTo('#search_form')               
    //  $.ajax({
    //   method: "GET",
    //   url: "{{route('landlordInvoiceFilter')}}",
    //   data: {"_token" : $('meta[name="csrf-token"]').attr('content'),'search_form':true},
    //   success: function(data){                            
    //     if(data != 0){
    //       $('#search_form').append(data);                           
    //     }               
    //   }           
    // });              
   }); 


    $(document).on('click','.remove_search',function(){
        $(this).closest('div .row').remove();
    });

     
  

    $(document).on('change keyup paste','.search_fields',function(){ 

     var landlord_invoice_voucher_no = $("#landlord_invoice_voucher_no").val();  
     var landlord_invoice_voucher_date = $("#landlord_invoice_voucher_date").val();  
     var landlordContractInfo__landlord_contract_no = $("#landlordContractInfo__landlord_contract_no").val();  
     var landlordContractInfo__paymentMethodInfo__payment_method_code = $("#landlordContractInfo__paymentMethodInfo__payment_method_code").val();  
     var landlord_given_invoice_no = $("#landlord_given_invoice_no").val();  
     var landlordContractInfo__landlord_contract_amt = $("#landlordContractInfo__landlord_contract_amt").val();  
     var landlord_invoice_approval_status = $("#landlord_invoice_approval_status").val();  
     var vendor_name = $("#landlordInfo__vendor_name").val();
     var vendor_code = $("#landlordInfo__vendor_code").val(); 
     var landlord_invoice_doc_type = $("#landlord_invoice_doc_type").val(); 
      

      var fieldName = [];
      var operation = [];
      var fieldValue = [];
      var logic = [];
     
      
      // Initializing array  
      $(".fieldName").each(function(){
      if(this.value != '')
            fieldName.push(this.value);
      });
      
      $(".operation").each(function(){
    //  if(this.value != '')
            operation.push(this.value);
      });      
      
      $(".fieldValue").each(function(){
    //  if(this.value != '')
            fieldValue.push(this.value);
      });
      
      $(".logic").each(function(){
    //  if(this.value != '')
            logic.push(this.value);
      });

  

     $.ajax({
      method: "GET",
      url: "{{url()->current()}}",
      data: {
       'landlord_invoice_voucher_no' : landlord_invoice_voucher_no,
       'landlord_invoice_voucher_date' : landlord_invoice_voucher_date,
       'landlordContractInfo__landlord_contract_no' : landlordContractInfo__landlord_contract_no,'vendor_name':vendor_name,'vendor_code':vendor_code,'landlord_invoice_doc_type':landlord_invoice_doc_type,
       'landlordContractInfo__paymentMethodInfo__payment_method_code' : landlordContractInfo__paymentMethodInfo__payment_method_code,
       'landlord_given_invoice_no' : landlord_given_invoice_no,
       'landlordContractInfo__landlord_contract_amt' : landlordContractInfo__landlord_contract_amt,
       'landlord_invoice_approval_status' : landlord_invoice_approval_status,'ajax' : true,
      
      'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 
      'logic' : logic,'route':"{{url()->current()}}",
      "_token" : $('meta[name="csrf-token"]').attr('content')},        
      beforeSend: function(){
                // Show image container
                $('#search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");        
              },
              success: function(data){  

                if(data != 0){

                  var temp = $(data);
                   var paginate_info = temp.find('.pagination_info').clone();
                    temp.find('.pagination_info').remove();
                  var paginate = temp.find('#pagination_ajax').clone();

                  temp.find('#pagination_ajax').remove();
                  $('#search').html(temp);
                  $("#pagination" ).html(paginate);   
                  $( "#pagination_info" ).html(paginate_info); 
              //   $('.search_fields').trigger('blur');             
            } 

          

            var txt = 'landlord_invoice_voucher_no='+landlord_invoice_voucher_no+'&landlord_invoice_voucher_date='+
            landlord_invoice_voucher_date+'&landlordContractInfo__landlord_contract_no='+landlordContractInfo__landlord_contract_no+'&landlord_invoice_doc_type='+landlord_invoice_doc_type+'&vendor_name='+vendor_name+'&vendor_code='+vendor_code+'&landlordContractInfo__paymentMethodInfo__payment_method_code='+landlordContractInfo__paymentMethodInfo__payment_method_code+'&landlord_given_invoice_no='+
            landlord_given_invoice_no+'&landlordContractInfo__landlord_contract_amt='+landlordContractInfo__landlord_contract_amt+'&landlord_invoice_approval_status='+landlord_invoice_approval_status ;
           

            var txt_hashes = txt.split('&');
            var href_txt = '';
            for(var i = 0; i < txt_hashes.length; i++)
            {
              txt_hash = txt_hashes[i].split('=');

              if(txt_hash[1] !=  '' && txt_hash[1] != 'undefined' && txt_hash[1] != undefined){                     
               href_txt =  (href_txt != '')? href_txt + '&': href_txt;

               href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
             }                       
           } 

        var queryTxt = ''; 
        
        if(fieldName.length > 0)
        queryTxt = decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));
                 
        href_txt = href_txt + '&'+queryTxt;


           
      

$('.invoice_sort').each(function (i, n) {
              var href = $(n).attr('href'); 
              var hashes =  href.slice(href.indexOf('sort'));
              href = href.split('?')[0];
              $(n).attr('href',href+'?'+href_txt+'&'+hashes);  
});
} 

});

   }); 




   });





</script>

@endsection
