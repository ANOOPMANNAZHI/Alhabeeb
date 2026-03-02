@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Status</div>
        </div>
        {{ Breadcrumbs::render('tenantStatus.index') }}
    </div>
</div>



<div class="row">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
		   @include('masters::search')     
    	</div>
	</div>
</div>



 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
			@can('add_tenant_status')
             <a href="{{route('tenantStatus.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
            @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('tenant_statuses_name','Name')</th>
                        @can('change_status_tenant_status')
                        <th>@sortablelink('tenant_statuses_status','Status')</th>
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($tenantStatuses as $tenantStatus)
                    <tr>
                        <td>
							<a @can('edit_tenant_status') href="{{route('tenantStatus.edit',$tenantStatus->id)}}" title="Edit" @elsecan('view_tenant_status') href="{{route('tenantStatus.show',$tenantStatus->id)}}" title="Show"  @endcan class="no-link" >
								{{$tenantStatuses->perPage()*($tenantStatuses->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_tenant_status') href="{{route('tenantStatus.edit',$tenantStatus->id)}}" title="Edit" @elsecan('view_tenant_status') href="{{route('tenantStatus.show',$tenantStatus->id)}}" title="Show"  @endcan class="no-link" >
								{{$tenantStatus->tenant_statuses_name}}
							</a>
						</td>
                        @can('change_status_tenant_status')
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('tenantStatus.changeStatus',$tenantStatus->id)}}">@if($tenantStatus->tenant_statuses_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$tenantStatus->tenant_statuses_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td> 
                         @endcan     
                        <td>
						@can('view_tenant_status')
                        <a href="{{route('tenantStatus.show',$tenantStatus->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a> 
                        @endcan
                        @can('edit_tenant_status')
                        <a title="Edit" href="{{route('tenantStatus.edit',$tenantStatus->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                        @endcan                                                  
                        @can('delete_tenant_status')
                        <a href="{{route('tenantStatus.destroy',$tenantStatus->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a> 
                        @endcan                      
                        </td>
                    </tr> 
                    @php $count++; @endphp   
                    @empty
                    <tr>
                        <td colspan="4" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
              
		        @php
					$sort =  app('request')->input('sort') ;
					if(!empty($sort)){
					$direction =  app('request')->input('direction') ;
					$tenantStatuses->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
				@endphp  
              {{$tenantStatuses->links()}}      
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


 jQuery(document).ready(function() {

  jQuery('#dtBasicExample').DataTable({
    "paging": false ,  "searching": false ,"info": false 
  });
  jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Tenant Status?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });
            jQuery('.change_status').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Change Status?')) {
                    jQuery("#status-form").attr('action', action);
                    jQuery("#status-form").submit();
                } else {
                    return false;
                }
            })
        });

</script> 


@endsection
