            
@php 


$curr_loop =  ($maintenanceInvoices->currentPage() == 1)? 1 :   ( (($maintenanceInvoices->currentPage() - 1) * $maintenanceInvoices->perPage()) + 1 );

if(\Request::input('curr_url'))
$curr_url =  \Request::input('curr_url');
else
$curr_url =  url()->current(); 


if($curr_url == route('maintenanceInvoiceApproval'))
$view_url = 'maintenanceInvoiceApproval.show';
else 
$view_url = 'maintenanceInvoice.show';              

@endphp                        
@forelse ($maintenanceInvoices as $maintenanceInvoice)
<tr>
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{ $curr_loop + $loop->index }}  
    </a></td> 
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{$maintenanceInvoice->maintenance_invoice_no}}</a></td>  
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{$maintenanceInvoice->maintenance_invoice_date->format('d/m/Y')}}</a></td>
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{$maintenanceInvoice->vendor->vendor_name}}</a></td>
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{$maintenanceInvoice->vendor->vendor_code}}</a></td>
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">{{numberFormat($maintenanceInvoice->maintenance_invoice_refer_amt)}}</a></td> 

    @if($curr_url !=  route('maintenanceInvoiceApproval'))
	<td>
		<a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}">
        @if($maintenanceInvoice->maintenance_invoice_status == 3 )
		  <span class="label label-info label-mini">Post</span>
		@else
			@php $status = explode('|',$maintenanceInvoice->MaintenanceInvoiceApprovalStatusName) @endphp
		   <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span>
		@endif
		</a>
	</td>
    @endif
    @if($curr_url ==  route('maintenanceInvoiceApproval'))
    <td><a class="no-link" href="{{route($view_url,$maintenanceInvoice->id)}}"> 
        {{$maintenanceInvoice->approval_type}}
    </a></td> 
    @endif 

    <td>
        @can('view_maintenance_invoice')
        <a href="{{route($view_url,$maintenanceInvoice->id)}}" title="View" class="btn btn-tbl-view btn-xs">
            <i class="fa fa-eye "></i>
        </a>
        @endcan


        @if(url()->current() != route('maintenanceInvoiceApproval') && auth()->user()->can('post_maintenance_invoice') && $maintenanceInvoice->maintenance_invoice_status == 2)                     
        <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'post'])}}" title="Post" class="btn btn-tbl-violet btn-xs confirm">
            <i class="fa fa-pie-chart"></i>
        </a>
        @endif


        @if(auth()->user()->can('approve_maintenance_invoice') == false && in_array($maintenanceInvoice->maintenance_invoice_status,[2,3]) == false)    
        <a href="{{route('maintenanceInvoice.edit',$maintenanceInvoice->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
            <i class="fa fa-pencil "></i>
        </a>
        @endif  


        @if(auth()->user()->can('approve_maintenance_invoice') == false && in_array($maintenanceInvoice->maintenance_invoice_status,[2,3]) == false) 
        <a href="{{route('maintenanceInvoice.destroy',$maintenanceInvoice->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete">
            <i class="fa fa-trash-o"></i>
        </a> 
        @endif

        @if(url()->current() != route('maintenanceInvoiceApproval') &&  auth()->user()->can('approve_maintenance_invoice') == false && ($maintenanceInvoice->send_for_approval ||  $maintenanceInvoice->send_for_unapproval) && $maintenanceInvoice->maintenance_invoice_status != 3)
        <a href="{{route('maintenanceInvoice.sendToApproveUnapprove',$maintenanceInvoice->id)}}" title="{{ ($maintenanceInvoice->send_for_approval)? 'Send For Approval ' : ( ($maintenanceInvoice->send_for_unapproval)? 'Send For Unapproval ': '' ) }}" class="btn btn-tbl-general btn-xs confirm">
            <i class="fa {{ ($maintenanceInvoice->send_for_approval)? 'fa-hand-o-right' : ( ($maintenanceInvoice->send_for_unapproval)? 'fa-hand-o-left': '' ) }}"></i>
        </a>
        @endif 
        
        @if(url()->current() == route('maintenanceInvoice.index') &&  auth()->user()->can('approve_maintenance_invoice') == true && in_array($maintenanceInvoice->maintenance_invoice_approval_status,[1,4]) && $maintenanceInvoice->maintenance_invoice_status != 3)   
        <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'approve'])}}" title="{{ ($maintenanceInvoice->maintenance_invoice_approval_status == 1)? 'Approve' : 'Unapprove' }} " class="btn btn-tbl-general btn-xs confirm">
          <i class="fa {{ ($maintenanceInvoice->maintenance_invoice_approval_status ==1)? 'fa-check' : 'fa-reply' }}"></i>              
        </a>

          @if(url()->current() == route('maintenanceInvoice.index') &&  auth()->user()->can('approve_maintenance_invoice') == true && in_array($maintenanceInvoice->maintenance_invoice_approval_status,[1]))  
            <a href="{{route('maintenanceInvoice.destroy',$maintenanceInvoice->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete">
                <i class="fa fa-trash-o"></i>
            </a> 
          @endif
        @endif
         @if(url()->current() == route('maintenanceInvoiceApproval') && in_array($maintenanceInvoice->maintenance_invoice_approval_status,[2,3]) && $maintenanceInvoice->maintenance_invoice_status != 3)   
         <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'reject'])}}" title="Reject" class="btn btn-tbl-reject btn-xs assignLead">
             <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
          </a>  
          
        <a href="{{route('maintenanceInvoice.approve',[$maintenanceInvoice->id,'approve'])}}" title="{{ ($maintenanceInvoice->maintenance_invoice_approval_status == 2)? 'Approve' : 'Unapprove' }} " class="btn btn-tbl-general closed">
           <i class="fa fa-check "></i>                
        </a> 
     @endif

  </td>
</tr>

@empty 
<tr>
  <td colspan="10" align="center">
     <p>No Record</p>
 </td>
</tr>
@endforelse 




        @if(isset($request->ajax))  
        <tr>     
                              
          <td colspan="6" id="pagination_ajax">
                {{$maintenanceInvoices->withPath($route)->appends(\Request::except(['page','_token','ajax','route']))->links()}}

                 <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $maintenanceInvoices])         
                               </div>
           </td>                           

         </tr>
         @endif






