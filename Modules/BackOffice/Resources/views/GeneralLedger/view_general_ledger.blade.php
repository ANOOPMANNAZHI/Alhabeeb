@extends('layouts.plms-app')
 


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">
     @if(url()->current() !=  route('generalLedgerApproval.show',$generalLedger->id)) 
      General Ledger View
      @else
      General Ledger Approval View
     @endif
    </div>
    </div>

     @if(url()->current() !=  route('generalLedgerApproval.show',$generalLedger->id)) 
       {{ Breadcrumbs::render('generalLedger.show') }} 
      @else
       {{ Breadcrumbs::render('generalLedgerApproval.show') }} 
     @endif  

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">

     <div class="row "> 
      <div class="col-sm-12">
            <div class="pull-right"> 

 @if(url()->current() !=  route('generalLedgerApproval.show',$generalLedger->id))
            
              @if($generalLedger->general_ledger_status == 2  && auth()->user()->can('post_general_ledger') )              
              <a href="{{route('generalLedger.action',[$generalLedger->id,'post'])}}" class="btn btn-circle btn-success align-right confirm post_type">Post</a>
              @endif

              @if(url()->current() != route('generalLedgerApproval.show',$generalLedger->id) && auth()->user()->can('ledger_approval') == true && in_array($generalLedger->general_ledger_approval_status,[0,1,5]) && $generalLedger->general_ledger_status != 3)   
              <a href="{{route('generalLedger.action',[$generalLedger->id,'approve'])}}" title="{{ ($generalLedger->general_ledger_approval_status == 1)? 'Approve' : 'UnApprove' }} " class="btn btn-circle btn-info  align-right confirm">
             {{ (in_array($generalLedger->general_ledger_approval_status,[0,1,5]))?'Approve' : 'UnApprove' }}                    
              </a>
            @elseif($generalLedger->general_ledger_status == 1 && $generalLedger->general_ledger_approval_status != 2 && auth()->user()->can('send_for_approval_general_ledger') && $generalLedger->general_ledger_status != 3)  
              <a href="{{route('generalLedger.action',[$generalLedger->id,'send-for-approval'])}}" class="btn btn-primary">Send for Approval</a>
              @endif

            @if(url()->current() != route('generalLedgerApproval.show',$generalLedger->id) && auth()->user()->can('ledger_approval') == true && in_array($generalLedger->general_ledger_approval_status,[4])  && $generalLedger->general_ledger_status != 3)   
              @if($generalLedger->general_ledger_approval_status == 4)
              <a href="{{route('generalLedger.action',[$generalLedger->id,'unapprove'])}}" title="{{ ($generalLedger->general_ledger_approval_status == 1)? 'Approve' : 'UnApprove' }} " class="btn btn-circle btn-info  align-right confirm">
                UnApprove
              </a>
              @else
              <a href="{{route('generalLedger.action',[$generalLedger->id,'approve'])}}" title="{{ ($generalLedger->general_ledger_approval_status == 1)? 'Approve' : 'UnApprove' }} " class="btn btn-circle btn-info  align-right confirm">
              Approve              
              </a>
              @endif
             @elseif(in_array($generalLedger->general_ledger_status,[2,3]) && $generalLedger->general_ledger_approval_status != 3 && auth()->user()->can('send_for_unapproval_general_ledger')  && $generalLedger->general_ledger_status != 3)  
              <a href="{{route('generalLedger.action',[$generalLedger->id,'send-for-unapproval'])}}" class="btn btn-circle ">Send for Unapproval</a>
              @endif

                
           @else
         <!---  Approval Reject     ----->
         
             @if(in_array($generalLedger->general_ledger_approval_status,[2,3]))  
            
              @can('ledger_approval')

              @if($generalLedger->general_ledger_approval_status == 2)
              <a href="{{route('generalLedger.action',[$generalLedger->id,'approve'])}}" class="btn btn-primary">Approve</a>  
              @endif
              
              @if($generalLedger->general_ledger_approval_status == 3 )
              <a href="{{route('generalLedger.action',[$generalLedger->id,'unapprove'])}}" class="btn btn-primary">Unapprove</a> 
              @endif 
              
              <a href="{{route('generalLedger.action',[$generalLedger->id,'reject'])}}" class="btn btn-danger">Reject</a>              
              @endcan

              @endif

            @endif  
            @if(!in_array($generalLedger->general_ledger_status,[2,3]) && auth()->user()->can('delete_general_ledger')) 
              <a href="{{route('generalLedger.destroy',$generalLedger->id)}}" class="btn btn-circle btn-custom_delete delete_type align-right ">Delete</a>  
            @endif 
            @if(!in_array($generalLedger->general_ledger_status,[2,3]) && auth()->user()->can('edit_general_ledger'))  
           
              <a class="btn btn-circle btn-primary align-right" title="Edit" href="{{route('generalLedger.edit',$generalLedger->id)}}">
                Edit
              </a>  
            @endif
          </div>
        </div>
    </div>

      <div class="dataSearchBox">
        <div class="card-body row">
          
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Voucher No. :  </b><span>{{$generalLedger->voucher_no}}</span></h5>
            </div>
          </div>
           
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Ledger Type  :  </b><span>{{$generalLedger->ledger_type}}</span></h5>
            </div>
          </div>
           
              
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>JV Ref No. :  </b><span>{{$generalLedger->jv_refer_no}}</span></h5>
           </div>
         </div>

          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Doc Date :  </b><span>{{$generalLedger->doc_date->format('d/m/Y')}}</span></h5>
           </div>
         </div>

       
            
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Description :  </b><span>{{$generalLedger->general_ledger_desc}}</span></h5>
           </div>
         </div>         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Bank :  </b><span>{{(!empty($generalLedger->bank_id))?$generalLedger->bank->bank_name : ''}}</span></h5>
           </div>
         </div>
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Amount:  </b><span>{{numberFormat((float)$generalLedger->amount)}} OMR</span></h5>
           </div>
         </div>
		 @if($generalLedger->agreement_no)	
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No:  </b><span>{{$generalLedger->agreement_no}}</span></h5>
         </div>
         </div> 
          @endif
         @if($generalLedger->maintenance_id && isset($generalLedger->maintenance->maintenance_invoice_no))
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Invoice No :  </b><span>{{$generalLedger->maintenance->maintenance_invoice_no}}</span></h5>
         </div>
         </div>
         @endif

       </div>
     </div>

 

      <div class="dataSearchBox">    
        <div class="card-body row">         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>AX-Batch ID:  </b><span>{{$generalLedger->ax_batch_id}}</span></h5>
           </div>
         </div>         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>AX-Payment No :  </b><span>{{$generalLedger->voucher_no}}</span></h5>
           </div>
         </div>         
       </div>
     </div>

<div class="dataSearchBox ">    
        <div class="row">
		 <table class="table" >
             <thead>
                    <tr>
                       <th>Account Code</th>
                       <th>Description</th>                    
                       <th>Bld Code</th>
                       <th>Unit Code</th>
                       <th>JV Desc</th>
                       <th>Dr.Amt</th>
                       <th>Cr.Amt</th>                      
                       <th>Recovery</th>                       
                       <th>Dim1</th>
                       <th>Dim2</th>                 
                    </tr>
                    
                </thead>

                 <tbody>
                   @php
                      $totalCr = 0;
                      $totalDr = 0;
                    @endphp
                  @foreach($generalLedger->generalLedgerDim as  $dimDetails)
                    <tr>
                      <td>{{$dimDetails->accountCode->acc_code_val}}</td>
                      <td>{{$dimDetails->accountCode->acc_code_desc}}</td>
                      <td>{{isset($dimDetails->building)?$dimDetails->building->building_name:''}}</td>
                      <td>{{(!empty($dimDetails->unit_id))?$dimDetails->unit->unit_code: ''}}</td>
                      <td>{{$dimDetails->jv_desc}}</td>
                      <td>{{numberFormat($dimDetails->debit_amt)}}</td>
                      <td>{{numberFormat($dimDetails->credit_amt)}}</td>
                      <td>{{$dimDetails->recovery_val}}</td>
					  <td>{{ $dimDetails->dim1able->dim_value ?? ''}}</td>
                      <td>{{ $dimDetails->dim2able->building_name ?? ''}}</td>
                      @php
                        $totalCr += $dimDetails->credit_amt;
                        $totalDr += $dimDetails->debit_amt
                      @endphp
                    </tr>
                  @endforeach  
                    <tr>
                      <td colspan="5" ><span class="pull-right" >Total : </span></td>
                      <td >{{numberFormat($totalDr)}}</td>                      
                      <td>{{numberFormat($totalCr)}}
                      </td>
                      <td colspan="3" ></td>
                    </tr>
                 </tbody>
              </table>
            </div>
          </div>



          
       
       
   </div>    
 </div> 

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
                if (confirm('Do you want to Delete this General Ledger?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });

   $(document).on('click','.post_type',function(){  

        if (confirm('Do you want to Post?')) {
             return true;
        } else {
            return false;
        }
    });
 });

</script>

 @endsection
