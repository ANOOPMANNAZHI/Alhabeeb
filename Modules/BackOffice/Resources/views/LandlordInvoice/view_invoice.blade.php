@extends('layouts.plms-app')
 


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">
        @if(url()->current() !=  route('landlordInvoiceApprovalShow',$landlordContract->id)) Landlord Invoice Booking 
        @else
        Landlord Invoice Approval View
        @endif
      </div>
    </div>
     @if(url()->current() ==  route('landlordInvoiceApprovalShow',$landlordContract->id)) 
     {{ Breadcrumbs::render('landlordInvoicesApproval.show') }} 
     @else
    {{ Breadcrumbs::render('landlordInvoice.show') }} 
    @endif
  </div>
</div>

<div class="row">
<div class="col">

<div class="card card-box salesSearchBox">


     <div class="row">
          <div class="col-sm-12">
            <div class="pull-right"> 
             
             @if(url()->current() !=  route('landlordInvoiceApprovalShow',$landlordContract->id))

              @if($landlordInvoice->landlord_invoice_status == 2 && in_array($landlordInvoice->landlord_invoice_approval_status,[4])  && auth()->user()->can('post_landlord_invoice') )              
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'post'])}}" class="btn btn-circle btn-success align-right">POST</a>
              @endif

              @if(!in_array($landlordInvoice->landlord_invoice_approval_status,[2,3,4]) && auth()->user()->can('edit_landlord_invoice') )              
              <a href="{{route('landlordInvoice.edit',$landlordContract->id)}}" class="btn btn-primary">Edit</a>
              @endif

              @if(in_array($landlordInvoice->landlord_invoice_approval_status,[0,1,5]) && auth()->user()->can('landlord_invoice_approval'))  
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'approve'])}}" class="btn btn-primary">Approve</a> 
              @elseif(in_array($landlordInvoice->landlord_invoice_approval_status,[0,1,5]) && auth()->user()->can('send_for_approval_landlord_invoice'))
                <a href="{{route('landlordInvoice.action',[$landlordContract->id,'send-for-approval'])}}" class="btn btn-primary">Send for Approve</a>
              @endif

              @if(in_array($landlordInvoice->landlord_invoice_approval_status,[4]) && $landlordInvoice->landlord_invoice_status != 3 && (auth()->user()->can('send_for_unapproval_landlord_invoice') == true))  
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'unapprove'])}}" class="btn btn-circle btn-primary  align-right">Unapprove</a>
              @elseif(in_array($landlordInvoice->landlord_invoice_approval_status,[4]) && $landlordInvoice->landlord_invoice_status != 3 && auth()->user()->can('landlord_invoice_approval')) 
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'send-for-unapproval'])}}" class="btn btn-primary">Send For Draft</a> 
              @endif
            
              @if( (!in_array($landlordInvoice->landlord_invoice_approval_status,[2,3,4])) && auth()->user()->can('delete_landlord_invoice')) 
              <a href="{{route('landlordInvoice.delete',$landlordContract->id)}}" class="btn btn-primary">Delete</a>  
              @endif   
           @else
         <!---  Approval Reject     -->
         
             @if(in_array($landlordInvoice->landlord_invoice_approval_status,[2,3]))  
            
              @if($landlordInvoice->landlord_invoice_approval_status == 2 && auth()->user()->can('landlord_invoice_approval'))
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'approve'])}}" class="btn btn-primary">Approve</a>  
              @endif
              
              @if($landlordInvoice->landlord_invoice_approval_status == 3 && auth()->user()->can('landlord_invoice_unapproval'))
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'unapprove'])}}" class="btn btn-primary">Unapprove</a> 
              @endif 

              @can('landlord_invoice_reject')
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'reject'])}}" class="btn btn-danger">Reject</a>
              @endcan

             @endif

            @endif  

             
            </div>
          </div>
        </div>



<div class="dataSearchBox ">    
        <div class="row">

          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Voucher No.:  </b><span>{{$landlordInvoice->landlord_invoice_voucher_no}}</span></h5>
           </div>
         </div> 
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Voucher Date :  </b><span>{{\Carbon\Carbon::parse($landlordInvoice->landlord_invoice_voucher_date)->format('d-m-Y')}}</span></h5>
           </div>
         </div>      
        

        <div class="w-100"></div> 
           
      </div>
    
</div>

 
<h3>Contract Details</h3>

<div class="dataSearchBox ">    
  <div class="row">

    <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Landlord name:  </b><span>{{$landlordContract->vendorInfo->vendor_name}}</span></h5>
           </div>
    </div> 

 <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Landlord code:  </b><span>{{$landlordContract->vendorInfo->vendor_code}}</span></h5>
           </div>
    </div>   
  <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No.:  </b><span>{{$landlordContract->landlord_contract_no}}</span></h5>
           </div>
    </div>

    <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Payment Term:  </b><span>{{$landlordContract->paymentMethodInfo->payment_method_code}}</span></h5>
           </div>
    </div>

  <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Doc Type:  </b><span>Invoice</span></h5>
           </div>
    </div>

      <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Ref No.:  </b><span>{{$landlordInvoice->landlord_given_invoice_no}}</span></h5>
           </div>
    </div> 

    

  <div class="col-lg-6 p-t-20"> 
   <div class="txt-full-width">
     <h5 class="details"><b>Contract Amount:  </b>
     <span>{{numberFormat($landlordContract->landlord_contract_amt)}}  OMR</span></h5>
   </div>     
  </div>  

   <div class="col-lg-6 p-t-20"> 
   <div class="txt-full-width">
     <h5 class="details"><b>Description:  </b>
     <span>{{$landlordInvoice->landlord_invoice_desc}}</span></h5>
   </div>     
  </div>  
             
  </div>
</div>

  <div class="dataSearchBox">    
    <div class="card-body row">

      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Batch ID :  </b><span>{{$landlordInvoice->ax_batch_id}}</span></h5>
        </div>
      </div> 


      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>AX Invoice No :  </b><span>{{$landlordInvoice->landlord_invoice_voucher_no}}</span></h5>
        </div>
      </div> 
     
    </div>
  </div>
 
<!-- AX Details ends -->
 <h3>Distribution Details</h3>


<div class="dataSearchBox ">    
        <div class="row">
          <table class="table" >
             <thead>
                    <tr>
                       <!--<th>Account Code</th>
                       <th>Description</th> -->
                       <th>Type</th>                     
                       <th>Debit Amount</th>
					   <th>Credit Amount</th>
                       <th>Dimension1</th>
                       <th>Dimension2</th>                       
                                       
                    </tr>                    
                </thead>
                <tbody>
                  <tr>@php 
                    $second_landlord = $landlordInvoice->landlordInvoiceDimension->get(1);
                    @endphp
                    {{--<td>
                       <input  disabled class="form-control " type="text" id="ac_codes_id[0]"  name="ac_codes_id[0]" value="{{$second_landlord->accountCode->acc_code_val}}" >   
                    </td>
                    <td>
                       <input  disabled class="form-control " type="text"   id="account_code_des[0]"  name="account_code_des[0]" value="{{$second_landlord->accountCode->acc_code_desc}}" >   
                    </td> --}}
                    <td>
                       <input disabled class="form-control " type="text"   id="acc_type[0]"  name="acc_type[0]" value="{{AX_LEDGER_DR}}" >   
                    </td>
					 <td>
                       <input disabled class="form-control " type="text"   id="contract_debit_amt[0]"  name="contract_debit_amt[0]" value="{{numberFormat($second_landlord->debit_amount)}}" >   
                    </td>
                    <td>
                       <input disabled class="form-control " type="text"   id="contract_amt[0]"  name="contract_cr_amt[0]" value="{{numberFormat($second_landlord->credit_amount)}}" >   
                    </td>
                   
                    <td>
                    <input  disabled class="form-control " type="text"   id="building_code[0]"  name="building_code[0]" value="{{$second_landlord->dim2able->building_code}}" >   
                    </td>

                    <td>
                      <input disabled class="form-control " type="text"   id="dim1[0]"  name="dim1[0]" value="{{$second_landlord->dim1able->dim_value}}" >                       
                    </td>

                   </tr>
                  <tr>@php 
                    $first_landlord = $landlordInvoice->landlordInvoiceDimension->first();
                    @endphp
          {{-- <td>
                       <input disabled class="form-control " type="text" id="ac_codes_id[0]"  name="ac_codes_id[0]" value="{{$first_landlord->accountCode->acc_code_val}}" >   
                    </td>
                    <td>
                       <input  disabled class="form-control " type="text"   id="account_code_des[0]"  name="account_code_des[0]" value="{{$first_landlord->accountCode->acc_code_desc}}" >   
                    </td> --}}
                    <td>
                       <input disabled  class="form-control " type="text"   id="acc_type[0]"  name="acc_type[0]" value="{{AX_VENDOR_CR}}" >   
                    </td>
					<td>
                       <input disabled  class="form-control " type="text"   id="contract_debit_amt[0]"  name="contract_debit_amt[0]" value="{{numberFormat($first_landlord->debit_amount)}}" >   
                    </td>
                    <td>
                       <input disabled  class="form-control " type="text"   id="contract_amt[0]"  name="contract_cr_amt[0]" value="{{numberFormat($first_landlord->credit_amount)}}" >   
                    </td>
                    
                      
                    <td>
                    <input disabled class="form-control " type="text"   id="building_code[0]"  name="building_code[0]" value="{{$first_landlord->dim2able->building_code}}" >   
                    </td>
                    <td>
                      <input disabled  class="form-control " type="text"   id="dim1[0]"  name="dim1[0]" value="{{$first_landlord->dim1able->dim_value}}" >                       
                    </td>


                   </tr>
                   <tr>
                     <td colspan="1"><span class="pull-right" > Total</span></td>
                    <td >  <input  disabled class="form-control " type="text"   id="building_code[0]"  name="building_code[0]" value="{{numberFormat($second_landlord->debit_amount)}}" >  </td>
					<td >  <input  disabled class="form-control " type="text"   id="building_code[0]"  name="building_code[0]" value="{{numberFormat($first_landlord->credit_amount)}}" >  </td>
                     
                     <td colspan="2"><button data-toggle="modal" data-target="#myModal" type="button" class="btn btn-primary pull-right" data-backdrop="static" data-keyboard="false">Distribution Break up</button></td>
                   </tr>               
                 
                </tbody>
          </table>
        </div>
      </div>


 


<div class="clearfix"></div>
    
</div>
</div>
</div>

  <div class="modal" id="myModal">
     <div class="modal-dialog assign modal-lg">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Distribution Break up</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">       
        <div class="dataSearchBox ">            
                <div class="row">
                  <div class="col-sm-12">

                     <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                       <th>Sl No.</th>
                       <th>Account Code</th>
                       <th>Doc.Date</th>
                       <th>Debit Amount</th>
                       <th>Credit Amount</th>    
					   <th>Ax-Batch Id</th>   
						<th>Status</th>					   
                    </tr>
                  </thead>
                    <tbody>
                      @foreach($dstbrkup as $distributionBreakup)
                      <tr>
                        <td>{{(!$loop->first)? ( ($loop->iteration%2 == 0)? $loop->iteration/2 : ($loop->iteration+1)/2 ) : $loop->iteration }}</td>
                        <td>{{$distributionBreakup->account_code}}</td>
                        <td>{{date('d/m/Y',strtotime($distributionBreakup->date))}}</td>
                        <td>{{numberFormat($distributionBreakup->debit_amount)}}</td>
                        <td>{{numberFormat($distributionBreakup->credit_amount)}}</td>
						<td>{{isset($distributionBreakup->credit_amount)?$distributionBreakup->ax_batch_id:''}}</td>
						<td>{{($distributionBreakup->is_posted==1)?'Posted':'Not Posted'}}</td>
                      </tr>
                      @endforeach
                    </tbody>

                  </table>
                   
                  </div>
                  
               <div class="w-100"></div>
              </div>        
            </div>
                
            </div>
        </div>
    </div> 

        </div>
        
   

    </div>
</div>




  </div>

 @endsection
 @section('scripts')
 @endsection
