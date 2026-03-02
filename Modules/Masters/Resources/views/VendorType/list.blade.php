@extends('layouts.plms-app')

 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Vendor Type</div>
        </div>
        {{ Breadcrumbs::render('vendorType.index') }}
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
			  @can('add_vendor_type')
             <a href="{{route('vendorType.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
              @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('vendor_types_name','Name')</th>
                         @can('change_status_vendor_type')
                        <th>@sortablelink('vendor_types_status','Status')</th>
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($vendorTypes as $vendorType)
                    <tr>
                        <td>
							<a @can('edit_vendor_type') href="{{route('vendorType.edit',$vendorType->id)}}" title="Edit" @elsecan('view_vendor_type') href="{{route('vendorType.show',$vendorType->id)}}" title="Show"  @endcan class="no-link" >
								{{$vendorTypes->perPage()*($vendorTypes->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_vendor_type') href="{{route('vendorType.edit',$vendorType->id)}}" title="Edit" @elsecan('view_vendor_type') href="{{route('vendorType.show',$vendorType->id)}}" title="Show"  @endcan class="no-link" >
								{{$vendorType->vendor_types_name}}
							</a>
						</td>
                         @can('change_status_vendor_type')
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('vendorType.changeStatus',$vendorType->id)}}">@if($vendorType->vendor_types_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$vendorType->vendor_types_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>
                        @endcan      
                        <td>
						@can('view_vendor_type')
                        <a href="{{route('vendorType.show',$vendorType->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @endcan
                         @can('edit_vendor_type')
                        <a title="Edit" href="{{route('vendorType.edit',$vendorType->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                        @endcan                                                  

						@can('delete_vendor_type')
                        <a href="{{route('vendorType.destroy',$vendorType->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
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
					$vendorTypes->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
              {{ $vendorTypes->links() }}      
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

 
  jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Vendor Type?')) {
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
