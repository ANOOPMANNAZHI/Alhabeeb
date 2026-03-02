@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Termination Request</div>
        </div>
         {{ Breadcrumbs::render('tenantTermination.index') }}
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('termination_requests_add')
             <a href="{{route('tenantTermination.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
            @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Contract</th>
                        <th>Renewal Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenantTerminations as $tenantTermination)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$tenantTermination->tenantContract->tenant_contract_no}}</td>
                        <td>{{$tenantTermination->renewal_or_termination_request_type}}</td>      
                        <td>
                        @can('termination_requests_list')
                        <a href="{{route('tenantTermination.show',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a>
                        @endcan
                        @can('termination_requests_edit') 
                        <a title="Edit" href="{{route('tenantTermination.edit',$tenantTermination->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>                                                   
                        @endcan
                        @can('termination_requests_delete')
                        <a href="{{route('tenantTermination.destroy',$tenantTermination->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>
                        @endcan                       
                        </td>
                    </tr>  
                    @empty
                    <tr class="no-record" align="center">
                        <td colspan="4">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
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
jQuery('.delete_type').click(function (event) {
    var action = $(this).attr("href");
    event.preventDefault();
    if (confirm('Do you want to Delete this Request?')) {
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
    } else {
        return false;
    }
});

</script>


@endsection