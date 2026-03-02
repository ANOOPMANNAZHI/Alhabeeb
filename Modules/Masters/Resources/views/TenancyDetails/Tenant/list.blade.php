@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant</div>
        </div>
         {{ Breadcrumbs::render('tenants.index') }}
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
            @can('tenant_add')
            <h4>
             <a href="{{route('tenants.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
             <div class="clr"></div>
            </h4>
            @endcan  
			<div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('tenant_code','Code')</th>
                        <th>@sortablelink('tenant_name','Name')</th>
                        <th>@sortablelink('tenant_contact_no','Mobile No')</th>
                        <th>@sortablelink('tenant_status','Status')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($tenants as $tenant)
                    <tr>
                        <td>
							<a @can('tenant_edit') href="{{route('tenants.edit',$tenant->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('tenants.show',$tenant->id)}}" title="View"  @endcan class="no-link" >
								{{$tenants->perPage()*($tenants->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('tenant_edit') href="{{route('tenants.edit',$tenant->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('tenants.show',$tenant->id)}}" title="View"  @endcan class="no-link" >
								{{$tenant->tenant_code}}
							</a>
						</td>
                        <td>
							<a @can('tenant_edit') href="{{route('tenants.edit',$tenant->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('tenants.show',$tenant->id)}}" title="View"  @endcan class="no-link" >
								{{$tenant->tenant_name}} 
							</a>
						</td>
                        <td>
							<a @can('tenant_edit') href="{{route('tenants.edit',$tenant->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('tenants.show',$tenant->id)}}" title="View"  @endcan class="no-link" >
								{{$tenant->tenant_contact_no}} 
							</a>
						</td>
                                                                         
                        <td>
                        
                            @if($tenant->tenant_status == 1)
                            <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>{{$tenant->tenant_status_name}}</b></span>
                            @else 
                            <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>{{$tenant->tenant_status_name}}</b></span>
                            @endif
                          
                            </td> 
                       
                        <td>
                        @can('tenant_list')
                        <a href="{{route('tenants.show',$tenant->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @endcan
                        @can('tenant_edit')
                        <a title="Edit" href="{{route('tenants.edit',$tenant->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan                                                   
                      
                        </td>
                    </tr>  
                    @php $count++; @endphp
                    @empty
                    <tr>
                        <td colspan="5" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
			  </div>
              @php
                    $sort =  app('request')->input('sort') ;
                    if(!empty($sort)){
                    $direction =  app('request')->input('direction') ;
                    $tenants->appends(['sort' => $sort, 'direction' => $direction ]);                 
                    }                               
            @endphp 
              {{$tenants->links()}}      
            </div>
        </div>
    </div>
</div>

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
                if (confirm('Do you want to Delete this Work?')) {
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
