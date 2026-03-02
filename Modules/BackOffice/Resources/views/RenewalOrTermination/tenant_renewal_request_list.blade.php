@extends('layouts.plms-app')
@section('css')  
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('tenantRenewal.tenantRenewalRequestSearch'))
@section('search_reset', route('tenantRenewal.index'))

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Renewal Request</div>
        </div>
         {{ Breadcrumbs::render('tenantRenewal.index') }}
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

          @include('sales::enquiry_search')   
                
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('renewal_request_add')
             <a href="{{route('tenantRenewal.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
            @endcan
             <div class="clr"></div>
            </h4>
               <div class="table-wrap">
     <div class="table-responsive">           
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('tenantContract.tenant_contract_id','Contract')</th>
                        <th>Renewal Note</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td> 
                        <td><input type="text" name="contract_no" class="search_fields Contract"  value="{{old('contract_no')}}" ></td>
                        <td><input type="text" name="note" class="search_fields Contract"  value="{{old('note')}}" ></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tbody id="enquiry-search">                                                             
                                @include('backoffice::RenewalOrTermination.tenant_renewal_request_list_ajax')                                 
                            </tbody>
                   <!--  @forelse ($tenantRenewals as $tenantRenewal)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$tenantRenewal->tenantContract->tenant_contract_no}}</td>
                        <td>{{$tenantRenewal->renewalOrTermination->renewal_or_termination_request_note}}</td>      
                        <td>
                        @can('renewal_request_view')
                        <a href="{{route('tenantRenewal.show',$tenantRenewal->renewalOrTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a>
                        @endcan
                        @can('renewal_request_edit') 
                        <a title="Edit" href="{{route('tenantRenewal.edit',$tenantRenewal->renewalOrTermination->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>                                                   
                        @endcan
                        @can('renewal_request_delete')
                        <a href="{{route('tenantRenewal.destroy',$tenantRenewal->renewalOrTermination->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>
                        @endcan                       
                        </td>
                    </tr>  
                    @empty
                    <tr class="no-record" align="center">
                        <td colspan="4">
                        <p>No Records</p>
                       </td>
                    </tr>
                    @endforelse -->
                </tbody>
              </table>
              </div>
			  </div>
			  <div class="row "  id="pagination">
                     @php                                        
                                        
                        if(isset($request->ajax))   
                        $tenantRenewals->withPath($route);
                                                        
                        if(!empty($request->contract_no))
                        $tenantRenewals->appends(['contract_no' => $request->contract_no]);
                        
                        if(!empty($request->note))
                        $tenantRenewals->appends(['note' => $request->note]);
                        
                        
                        
                        
                        $fieldName =  app('request')->input('fieldName');
                        
                        if(!empty($fieldName)){
                            $fieldName =  app('request')->input('fieldName');
                            $operation =  app('request')->input('operation');
                            $fieldValue =  app('request')->input('fieldValue');
                            $logic =  app('request')->input('logic');
                        $tenantRenewals->appends(['fieldName' => $fieldName,
                                     'operation' => $operation,
                                     'fieldValue' => $fieldValue,
                                     'logic' => $logic,
                             ]);    
                        }
                                                        
                        @endphp   
                        
                        {{$tenantRenewals->links()}}               
                    </div>  
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
@include('sales::enquiry_search_js') 
<script>   
jQuery('.delete_type').click(function (event) {
    var action = $(this).attr("href");
    event.preventDefault();
    if (confirm('Do you want to Delete this Request Type?')) {
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
    } else {
        return false;
    }
});

</script>


@endsection