@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 


@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">{{ (isset($landlordInvoice))? 'Edit' : 'Add'  }} Landlord Invoice Booking</div>
          </div> 
           {{ (isset($landlordInvoice))?  
            Breadcrumbs::render('landlordInvoice.edit',$landlordContract) :  Breadcrumbs::render('landlordInvoice.create',$landlordContract) }}
      </div>
  </div>

<form action="{{ !isset($landlordInvoice)? route('landlordInvoice.store',$landlordContract->id): route('landlordInvoice.update',$landlordContract->id)}}" method="POST" id="form_sample_2" class="form-horizontal" data-toggle="validator">
  {{csrf_field()}} @if(isset($landlordInvoice)){{method_field('PUT')}}@endif
<div class="row">
<div class="col">

<div class="card card-box salesSearchBox">

  
        <div class="row">
          <div class="col-sm-12">
            <div class="pull-right">
              @if(isset($landlordInvoice))
              <!--<a href="{{route('landlordInvoice.action',[$landlordContract->id,'post'])}}" class="btn btn-primary">POST</a> -->

              @if($landlordInvoice->landlord_invoice_status == 1 && $landlordInvoice->landlord_invoice_approval_status != 2 && auth()->user()->can('send_for_approval_landlord_invoice') && auth()->user()->can('landlord_invoice_approval') == false) 
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'send-for-approval'])}}" class="btn btn-primary">Send for Approval</a>
              @endif

              @if(in_array($landlordInvoice->landlord_invoice_status,[2,3]) && $landlordInvoice->landlord_invoice_approval_status != 3 && (auth()->user()->can('send_for_unapproval_landlord_invoice') == true) &&  (auth()->user()->can('landlord_invoice_approval') == false) )  
              <a href="{{route('landlordInvoice.action',[$landlordContract->id,'send-for-unapproval'])}}" class="btn btn-primary">Send for Unapproval</a>
              @endif
              {{--

              @if( ((!in_array($landlordInvoice->landlord_invoice_status,[2,3]) &&  auth()->user()->can('landlord_invoice_approval') == false) || (auth()->user()->can('landlord_invoice_approval')) ) && auth()->user()->can('delete_landlord_invoice')) 
              <a href="{{route('landlordInvoice.delete',$landlordContract->id)}}" class="btn btn-primary">Delete</a>
              @endif
              --}}

              @endif
              <button type="submit" class="btn btn-primary save_form">SAVE</button>
            </div>
          </div>
        </div>
           



<div class="dataSearchBox ">    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_invoice_voucher_no">Voucher No.:<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="landlord_invoice_voucher_no" disabled name="landlord_invoice_voucher_no" value="{{ isset($landlordInvoice)? $landlordInvoice->landlord_invoice_voucher_no : $nextCode}}" >
              </div>
            </div>
          </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Voucher Date :<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input required type="date" class="form-control " id="landlord_invoice_voucher_date" name="landlord_invoice_voucher_date" value="{{ old('landlord_invoice_voucher_date', isset($landlordInvoice)? (\Carbon\Carbon::parse($landlordInvoice->landlord_invoice_voucher_date)->format('Y-m-d')) : date('Y-m-d',strtotime(today())
               ) )}}"  placeholder="Enter Invoice Date" onkeydown="return false">
                </div>
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
        <h5 class="details"><b>Ref No.<small class="textRed">*</small>:</b>   
            <span>               
            <input  type="text" required  class="txt_box" id="landlord_given_invoice_no"  name="landlord_given_invoice_no" value="{{ old('landlord_given_invoice_no',isset($landlordInvoice)? $landlordInvoice->landlord_given_invoice_no :'')}}" >            
           </span>
         </h5> 
      </div>
    </div>     

  <div class="col-lg-6 p-t-20"> 
   <div class="txt-full-width">
     <h5 class="details"><b>Contract Amount:  </b>
     <span>{{numberFormat($landlordContract->landlord_contract_amt) }} OMR</span></h5>
   </div>     
  </div> 


  <div class="col-lg-12 p-t-20"> 
      <div class="form-group">
        <label><b>Description :</b> </label>               
            <textarea id="landlord_invoice_desc" class="form-control"  name="landlord_invoice_desc" >{{ old('landlord_invoice_desc',isset($landlordInvoice)? $landlordInvoice->landlord_invoice_desc :'')}}</textarea> 
      </div>
    </div> 

               
  </div>
</div>
   <!-- starts -->
<div class="dataSearchBox">
  <div class="card-body row">
    <div class="col-sm-6">
      <div class="form-group">
        <label for="ax_batch_id">AX Batch ID</label>
        <div class="p-relative">
         <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
         <input  type="text" class="form-control" id="ax_batch_id" name="ax_batch_id" value="{{ old('ax_batch_id', isset($landlordInvoice)? $landlordInvoice->ax_batch_id : '' )}}"  placeholder="Enter AX Batch ID" readonly>
       </div>
     </div>
   </div>

   <div class="col-sm-6">
    <div class="form-group">
      <label for="ax_invoice_no">AX Batch No</label>
      <div class="p-relative">
       <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
       <input type="text" class="form-control" id="ax_invoice_no"  name="ax_invoice_no" value="{{ old('ax_invoice_no', isset($landlordInvoice)? $landlordInvoice->ax_invoice_no : '' )}}"  placeholder="Enter AX Batch No" readonly>
     </div>
   </div>
 </div>
</div>
</div>
<!--ends --> 

 <h3>Distribution Details</h3>

<div class="dataSearchBox ">    
        <div class="row">
          <table class="table" >
             <thead>
                    <tr>
                       <th>Account Code</th>
                       <!--<th>Description</th> -->
                       <th>Type</th>
					   <th>Debit Amount</th>
                       <th>Credit Amount</th>
                       <th>Dimension1</th>
                       <th>Dimension2</th>                       
                                            
                    </tr>                    
                </thead>
                <tbody>
                   <tr>
                    @php 
                    if(isset($landlordInvoice))
                    $second_landlord = $landlordInvoice->landlordInvoiceDimension->get(1);
					@endphp
					
                    <td>
                       <input  class="form-control " type="text" id="ac_codes_id[0]"  name="ac_codes_id[0]" value="{{($distribution_details->acc_params_dr_type =='VENDOR')?$landlordContract->vendorInfo->vendor_code:$distribution_details->acc_params_dr_acc}}" readonly>   
                    </td>
					<td>
                       <input  class="form-control " type="text" readonly  id="acc_type[0]"  name="acc_type[0]" value="{{$distribution_details->acc_params_dr_type}}" >   
                    </td>
                   
                    <td>
                       <input  class="form-control " type="text"  readonly  id="contract_debit_amt[1]"  name="contract_debit_amt[1]" value="{{numberFormat($landlordContract->landlord_contract_amt)}}" >   
                    </td>
					 <td>
                       <input  class="form-control " type="text"   readonly id="contract_amt[1]"  name="contract_cr_amt[1]" value="0.000" >   
                    </td>
                     <td>
                     <input  class="form-control " type="text"  readonly id="building_code[1]"  name="building_code[1]" value="{{$landlordContract->buildingInfo->building_code}}" >   
                    </td>
                    <td>
						<input  class="form-control " type="text"  name="dim2" value="{{($landlordContract->buildingInfo->ax_division=='01')?'HO':'PLM'}}">
						<input  class="form-control " type="hidden"  name="dim2_hidden" value="{{($landlordContract->buildingInfo->ax_division=='01')?'01':'02'}}">
                    </td>

                   

                   </tr>
                   <tr>
                    @php 
                    if(isset($landlordInvoice))
                    $first_landlord = $landlordInvoice->landlordInvoiceDimension->first();
                    @endphp
          
                    <td>
                       <input  class="form-control " type="text" id="ac_codes_id[0]"  name="ac_codes_id[0]" value="{{($distribution_details->acc_params_cr_type =='VENDOR')?$landlordContract->vendorInfo->vendor_code:$distribution_details->acc_params_cr_acc}}" readonly>   
                    </td>
          <td>
                       <input  class="form-control " type="text" readonly  id="acc_type[0]"  name="acc_type[0]" value="{{$distribution_details->acc_params_cr_type}}" >   
                    </td>
                   
                    <td>
                       <input  class="form-control allownumericwithdecimal" type="text" readonly  id="contract_debit_amt[0]"  name="contract_debit_amt[0]" value="0.000" >   
                    </td>
					 <td>
                       <input  class="form-control " type="text"   id="contract_amt[0]"  readonly name="contract_cr_amt[0]" value="{{numberFormat($landlordContract->landlord_contract_amt)}}" >   
                    </td>
          <td>
                     <input  class="form-control " type="text"  readonly id="building_code[1]"  name="building_code[1]" value="{{$landlordContract->buildingInfo->building_code}}" >   
                    </td>
                    <td>
                      <input  class="form-control " type="text"  name="dim1" value="{{($landlordContract->buildingInfo->ax_division=='01')?'HO':'PLM'}}">
            <input  class="form-control " type="hidden"  name="dim1_hidden" value="{{($landlordContract->buildingInfo->ax_division=='01')?'01':'02'}}">
                    </td>
                   </tr>

                   <tr>
                     <td colspan="2"> <span class="pull-right" >Total</span></td>
                    
                     <td >  <input  disabled class="form-control " type="text"    value="{{(isset($landlordInvoice))?  numberFormat($second_landlord->debit_amount) : numberFormat($landlordContract->landlord_contract_amt)}}" >  </td>
					 <td >  <input  disabled class="form-control " type="text"   value="{{(isset($landlordInvoice))? numberFormat($first_landlord->credit_amount) : numberFormat($landlordContract->landlord_contract_amt) }}" >  </td>
                     <td colspan="2">
                     <button data-toggle="modal" data-target="#myModal" type="button" class="btn btn-primary pull-right">Distribution Break up</button>

                     </td>
                   </tr>               
                 
                 
                </tbody>
          </table>
        </div>
      </div>



<!-- <div class="dataSearchBox ">    
        <div class="row">
          <div class="col-sm-12">
  <button type="submit" class="btn btn-primary">SAVE</button>
          </div>
        </div>
      </div> -->


<div class="clearfix"></div>
    
</div>
</div>
</div>
</form>


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
					   <th>Type</th>
                       <th>Doc.Date</th>
                       <th>Debit Amount</th>
                       <th>Credit Amount</th>      
					   <th>Status</th>  					   
                    </tr>
                  </thead>
                    <tbody>
                     
                      @php $i=0; @endphp
                      @foreach($distbkparry as $distbk)
                       
                      <tr>
                        <td>{{$i+1}}</td>
                        <td>{{($distribution_breakup_details->acc_params_cr_type =='VENDOR')?$landlordContract->vendorInfo->vendor_code:$distribution_breakup_details->acc_params_cr_acc}}</td>
						            <td>{{$distribution_breakup_details->acc_params_cr_type}}</td>
                        <td>
                       {{$distbk['inv_date']}}
                        </td>
                        <td>0.000</td>
                        <td>{{numberFormat($distbk['rent'])}}</td>
						            <td>Not Posted</td>
                      </tr>
                      <tr>
                        <td>{{$i+1}}</td>
                        <td>{{($distribution_breakup_details->acc_params_dr_type =='VENDOR')?$landlordContract->vendorInfo->vendor_code:$distribution_breakup_details->acc_params_dr_acc}}</td>
						            <td>{{$distribution_breakup_details->acc_params_cr_type}}</td>
                        <td> {{$distbk['inv_date']}}</td>
                        <td>{{numberFormat($distbk['rent'])}}</td>
                        <td>0.000</td>
					              <td>Not Posted</td>
                      </tr>
                       @php $i++; @endphp
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
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate({
       submitHandler: function(form) {
        $('.save_form').prop('disabled', true);
        form.submit();
      }
    });
    @if($isYearCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
        @endif

  });
</script>
@endsection
