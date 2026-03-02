@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Renewal Request</div>
        </div>
         {{ Breadcrumbs::render('landlordRenewal.index') }}
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('landlord_renewal_request_add')
             <a href="{{route('landlordRenewal.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
            @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('landlordContract.landlord_contract_no','Contract')</th>
                        <th>Renewal Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$request->landlordContract->landlord_contract_no}}</td>
                        <td>{{$request->renewalOrTermination->renewal_or_termination_request_note}}</td>      
                        <td>
                        @can('landlord_renewal_request_list')
                        <a href="{{route('landlordRenewal.show',$request->renewalOrTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a>
                        @endcan
                        @can('landlord_renewal_request_edit') 
                        <a title="Edit" href="{{route('landlordRenewal.edit',$request->renewalOrTermination->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>                                                   
                        @endcan
                        @can('landlord_renewal_request_delete')
                        <a href="{{route('landlordRenewal.destroy',$request->renewalOrTermination->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
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
    if (confirm('Do you want to Delete this Request Type?')) {
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
    } else {
        return false;
    }
});

</script>

@endsection