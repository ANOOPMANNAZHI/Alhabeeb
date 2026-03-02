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
            <div class="page-title"> {{ (url()->current() == route('generalLedgerApproval'))? 'General Ledger  Approval' : 'General Ledger'  }}    </div>
        </div>
        {{ (url()->current() == route('generalLedgerApproval'))?  Breadcrumbs::render('generalLedgerApproval.index') :  Breadcrumbs::render('generalLedger.index')  }}           
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
                     @include('includes.pagination_info',['paginator' => $generalLedgers])         
                 </div>
            @can('add_general_ledger')
              @if(url()->current() != route('generalLedgerApproval'))
               <a href="{{route('generalLedger.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
              @endif
            @endcan
             <div class="clr"></div>
            </h4>
              <div class="table-wrap">
 <div class="table-responsive">	
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>   
                    <tr>
                       <th>Sl No.</th>
                       <th>@sortablelink('general_ledger_type','Ledger Type',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('voucher_no','Voucher No.',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('jv_refer_no','JV Ref No.',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('doc_date','Doc Date',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('bank.bank_name','Bank',[],[ 'class' => 'invoice_sort' ])</th>
                       <th>@sortablelink('amount','Amount',[],[ 'class' => 'invoice_sort' ])</th>
                      
                        @if(url()->current() != route('generalLedgerApproval'))
                        <th> Status </th>
                       <!--<th>@sortablelink('general_ledger_status','Approved')</th>                      
                       <th>@sortablelink('general_ledger_status','Posted')</th> -->
                       @endif

                       @if(url()->current() ==  route('generalLedgerApproval'))
                       <th>@sortablelink('general_ledger_approval_status','Purpose')</th> 
                       @endif
                       <th>Action</th>
                    </tr>                   

                     <tr>
                       <td></td>
                            <td> 
                             <select name="general_ledger_type" class="searchFields search_fields mob" id="general_ledger_type" style="width:100px;">
                                    <option value="">Show All</option>
                                    @foreach($ledger_types as $key => $val)
                                    <option value="{{$key}}">{{$val}}</option>
                                    @endforeach
                                </select>                                
                            </td>
                            <td><input autocomplete="off" type="text" name="search_fields[voucher_no]" class="search_fields " id="voucher_no" value="{{old('voucher_no')}}" ></td>
                            <td><input autocomplete="off" type="text" name="search_fields[jv_refer_no]" class="search_fields " id="jv_refer_no" value="{{old('jv_refer_no')}}" ></td>
                            <td><input autocomplete="off" type="date" name="search_fields[doc_date]" class="search_fields" id="doc_date"  value="{{old('doc_date')}}" ></td>                          
                                                   
                            <td><input autocomplete="off" type="text" name="search_fields[bank__bank_name]" class="search_fields" id="bank__bank_name"  value="{{old('bank__bank_name')}}" ></td>                          
                            <td><input autocomplete="off" type="text" name="search_fields[amount]" class="search_fields" id="amount"  value="{{old('amount')}}" ></td> 

                            @if(url()->current() != route('generalLedgerApproval')) 
                           <td>
							  <select name="pdc_check" id="status" class="search_fields">
                            <option value="">Select</option>
                                <option value="1" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Draft</option>
                                <option value="2" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Pending</option>
                                <option value="4" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Approved</option>
                                <option value="5" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Reject</option>
                                <option value="6" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Post</option>
                                <option value="3" {{(isset($request->general_ledger_approval_status)? (old('general_ledger_approval_status')? 'SELECTED':''):'') }} >Pending For Draft</option>
                            
                            </select>
                            </td> 
                            @endif

                            @if(url()->current() == route('generalLedgerApproval'))
                             <td>
                              <select name="general_ledger_approval_status" class="searchFields search_fields mob" id="general_ledger_approval_status" style="width:100px;">
                              <option value="">Show All</option>
                              <option {{ (old('general_ledger_approval_status') == 2)? 'selected' : '' }} value="2">{{'Approval'}}</option>
                              <option {{ (old('general_ledger_approval_status') == 3)? 'selected' : '' }} value="3">{{'UnApproval'}}</option>
                            </select>
                            </td> 
                            @endif

                            <td></td>
                        </tr>


                    
                </thead>
                <tbody id="search">                                                               
                   @include('backoffice::GeneralLedger.generalLedger_list_ajax')
                </tbody>
                </table>
                  </div>
</div>				  
                 <div class="row"  id="pagination">                                 
                       {{$generalLedgers->appends(\Request::except('page'))->links()}}
                </div> 
                    
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="curr_url" id="curr_url" value="{{url()->current()}}">
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>


@endsection


@section('scripts') 
  

<script>
  $(document).ready(function() {

    $(document).on('click','.delete',function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Invoice?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
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


             // $.ajax({
             //      method: "GET",
             //      url: "{{route('maintenanceInvoiceFilter')}}",
             //      data: {"_token" : $('meta[name="csrf-token"]').attr('content'),'search_form':true},
             //      success: function(data){                            
             //                if(data != 0){
             //                $('#search_form').append(data);                           
             //                }               
             //            }           
             //  });              
    }); 



    $(document).on('click','.remove_search',function(){
        $(this).closest('div .row').remove();
    });
    



$(document).on('change keyup paste','.search_fields',function(){ 

 var general_ledger_type = $("#general_ledger_type").val();  
 var voucher_no = $("#voucher_no").val();  
 var jv_refer_no = $("#jv_refer_no").val();  
 var doc_date = $("#doc_date").val();  
 var jv_amount = $("#jv_amount").val();  
 var status = $("#status").val();  
 var bank__bank_name = $("#bank__bank_name").val();  
 var amount = $("#amount").val();  
 var general_ledger_approval_status = $("#general_ledger_approval_status").val();  
   
 var curr_url = $("#curr_url").val(); 

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
            operation.push(this.value);
      });      
      
      $(".fieldValue").each(function(){   
            fieldValue.push(this.value);
      });
      
      $(".logic").each(function(){    
            logic.push(this.value);
      });


@if(url()->current() != route('generalLedgerApproval'))
 var approval_status = $("#approval_status").val();  
 var post_status = $("#post_status").val();  
 @endif
 

   $.ajax({
        method: "GET",
        url: "{{url()->current()}}",
        data: { 'general_ledger_type' : general_ledger_type, 'voucher_no' : voucher_no , 
          'jv_refer_no' : jv_refer_no,
          'doc_date' : doc_date,
          'jv_amount' : jv_amount,
          'bank__bank_name' : bank__bank_name,
          'amount' : amount,'status':status,
          'general_ledger_approval_status' : general_ledger_approval_status,
          'curr_url' : curr_url,'ajax':true,
          @if(url()->current() != route('generalLedgerApproval'))
          'approval_status':approval_status , 'post_status' : post_status,
          @endif
           'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,
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

            var txt = 'general_ledger_type='+general_ledger_type+'&voucher_no='+
                      voucher_no+'&jv_refer_no='+jv_refer_no+'&'+
'doc_date='+doc_date+'&jv_amount='+jv_amount+'&bank__bank_name=' +bank__bank_name+'&amount='+ amount+'&general_ledger_approval_status=' +general_ledger_approval_status+'&curr_url='+curr_url+'&status='+status;
 
 @if(url()->current() != route('generalLedgerApproval'))
 txt +=  'approval_status='+approval_status+'&post_status='+post_status;
 @endif


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
        queryTxt =  decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));
                 
        href_txt = href_txt + '&'+queryTxt;
            
        //    txt = 'search_fields='+search_fields;
         //   txt = '&'+$.param(search_fields);
// split sort and direction

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


  (function($){

    $.fn.focusTextToEnd = function(){
        this.focus();
        var $thisVal = this.val();
        this.val('').val($thisVal);
        return this;
    }
  }(jQuery));



</script>

@endsection
