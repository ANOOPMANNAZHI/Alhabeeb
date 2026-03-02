@extends('layouts.plms-app')
 


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance Invoice View</div>
    </div>

    {{ Breadcrumbs::render('maintenanceInvoice.show') }} 

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">

     <div class="row">
      <div class="col-sm-12">
        @if(auth()->user()->can('approve_maintenance_invoice'))
    
          @if(in_array($maintenanceInvoice->maintenance_invoice_approval_status,[1,4]) &&
          $maintenanceInvoice->maintenance_invoice_status != 3)
          <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'approve'])}}" title="{{ ($maintenanceInvoice->maintenance_invoice_approval_status == 1)? 'Approve' : 'Unapprove' }} " class="btn btn-circle btn-primary align-right confirm">
               {{ ($maintenanceInvoice->maintenance_invoice_approval_status == 1)? 'Approve' : 'Unapprove' }}                
          </a> 
          @if(in_array($maintenanceInvoice->maintenance_invoice_approval_status,[1]) ) 
           
            <a href="{{route('maintenanceInvoice.destroy',$maintenanceInvoice->id)}}" title="Cancel" class="btn btn-circle btn-danger delete align-right">
               Delete 
            </a> 
          @endif
          @endif
          @if(in_array($maintenanceInvoice->maintenance_invoice_approval_status,[2,3]) )
          
             <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'reject'])}}" title="Reject" class="btn btn-circle btn-warning align-right">
                  Reject
              </a>  
              
            <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'approve'])}}" title="{{ ($maintenanceInvoice->maintenance_invoice_approval_status == 2)? 'Approve' : 'Unapprove' }} " class="btn btn-circle btn-primary align-right confirm">
               {{ ($maintenanceInvoice->maintenance_invoice_approval_status == 2)? 'Approve' : 'Unapprove' }}                
                            </a> 
                  
          
          @endif
        
        @else
       
          @if(($maintenanceInvoice->send_for_approval ||  $maintenanceInvoice->send_for_unapproval) &&  !auth()->user()->can('approve_maintenance_invoice'))
            <a href="{{route('maintenanceInvoice.sendToApproveUnapprove',$maintenanceInvoice->id)}}" title="{{ ($maintenanceInvoice->send_for_approval)? 'Send For Approval ' : ( ($maintenanceInvoice->send_for_unapproval)? 'Send For Unapproval ': '' ) }}" class="btn ml-1 btn-primary confirm align-right">
                {{ ($maintenanceInvoice->send_for_approval)? 'Send for Approval' : ( ($maintenanceInvoice->send_for_unapproval)?  'Send for Unapproval' : "") }}
            </a>
          @endif 

        	
          @if($maintenanceInvoice->maintenance_invoice_approval_status == 1)
            <a href="{{route('maintenanceInvoice.destroy',$maintenanceInvoice->id)}}" title="Cancel" class="btn btn-circle btn-primary delete align-right">
               Delete 
            </a> 
          @endif
        	 @endif
          @if(auth()->user()->can('post_maintenance_invoice') && $maintenanceInvoice->maintenance_invoice_status == 2)                     
               <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'post'])}}" title="Post" class="btn btn-circle btn-success align-right confirm">
                    Post
          </a>
         @endif

          @if(auth()->user()->can('edit_maintenance_invoice') && in_array($maintenanceInvoice->maintenance_invoice_approval_status,[0,1,5]) )  
          
              <a class="btn btn-circle btn-primary align-right confirm" title="Edit" href="{{route('maintenanceInvoice.edit',$maintenanceInvoice->id)}}">
                Edit
          </a>  
        @endif
      </div>
      </div>
      <div class="clr"></div>
      <div class="dataSearchBox">
        <div class="card-body row">
          
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Invoice No. :  </b><span>{{$maintenanceInvoice->maintenance_invoice_no}}</span></h5>
            </div>
          </div>
           
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Invoice Date  :  </b><span>{{$maintenanceInvoice->maintenance_invoice_date->format('d/m/Y')}}</span></h5>
            </div>
          </div>
           
        </div>
      </div>

      <div class="sub-head">Contractor Details</div>

      <div class="dataSearchBox">    
        <div class="card-body row">         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Name :  </b><span>{{$maintenanceInvoice->vendor->vendor_name}}</span></h5>
           </div>
         </div>         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Contractor Code :  </b><span>{{$maintenanceInvoice->vendor->vendor_code}}</span></h5>
           </div>
         </div>
         <div class="col-lg-12 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Description:  </b><span>{{$maintenanceInvoice->maintenance_invoice_desc}}</span></h5>
           </div>
         </div>
       </div>
     </div>


      <div class="sub-head">Invoice Details</div>

      <div class="dataSearchBox">    
        <div class="card-body row">         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Ref No :  </b><span>{{$maintenanceInvoice->maintenance_invoice_refer_no}}</span></h5>
           </div>
         </div>         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Amount :  </b><span>{{numberFormat($maintenanceInvoice->maintenance_invoice_refer_amt)}} OMR</span></h5>
           </div>
         </div>
		 <!--
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Payment Method:  </b><span>{{--$maintenanceInvoice->payment_method--}}</span></h5>
           </div>
         </div>
		-->
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>Comments:  </b><span>{{$maintenanceInvoice->maintenance_invoice_comment}}</span></h5>
         </div>
         </div>
       </div>
     </div>


 

      <div class="dataSearchBox">    
        <div class="card-body row">         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>AX-Batch ID:  </b><span>{{$maintenanceInvoice->ax_batch_id}}</span></h5>
           </div>
         </div>         
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
            <h5 class="details"><b>AX-Payment No :  </b><span>{{$maintenanceInvoice->ax_invoice_no}}</span></h5>
           </div>
         </div>         
       </div>
     </div>


{{-- dd($maintenanceInvoice->maintenanceInvoiceDetails) --}}
<div class="dataSearchBox ">    
        <div class="row">
          <table class="table" >
             <thead>
                    <tr>
                       <th>Account Code</th>
                       <th>Description</th>
                       <th>Bldg</th>
                       <th>Unit</th>
                       <th>Inv.Desc</th>
                       <th>Mat.Charges</th>
                       <th>Labour Charges</th>
                       <th>Debit Amount</th>
                       <th>Credit Amount</th>
                       <th>Recovery</th>
                       <th>Dim1</th>
                       <th>Dim2</th>                       
                       <th></th>                       
                    </tr>
                    
                </thead>

                 <tbody>
                  @foreach($maintenanceInvoice->maintenanceInvoiceDetails as  $nvoiceDetails)
                    <tr>
                      <td>{{$nvoiceDetails->accountCode->acc_code_val??''}}</td>
                      <td>{{$nvoiceDetails->description ??''}}</td>
                      <td>{{$nvoiceDetails->building->building_name ??''}}</td>
                      <td>{{$nvoiceDetails->unit->unit_code ??''}}</td>
                      <td>{{$nvoiceDetails->invoice_desc ?? ''}}</td>
                      <td>{{numberFormat($nvoiceDetails->material_charge) ?? ''}}</td>
                      <td>{{numberFormat($nvoiceDetails->labour_charge) ?? ''}}</td>
                      <td>{{numberFormat($nvoiceDetails->debit_amt) ?? ''}}</td>
                      <td>{{numberFormat($nvoiceDetails->credit_amt) ?? ''}}</td>
                      <td>{{$nvoiceDetails->technician_recovery_status ?? ''}}</td>
                      <td>{{$nvoiceDetails->dim2able->building_name ?? ''}}</td>
                      <td>
                        @if(isset($nvoiceDetails->dim2able->ax_division))
                        {{($nvoiceDetails->dim2able->ax_division=='01')?'HO':'PLM'}}
                        @endif
                      </td>
                    </tr>
                  @endforeach  
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



     });
  </script>

 @endsection
