@extends('layouts.plms-app')

 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Workflow Categories</div>
        </div>
        {{ Breadcrumbs::render('workFlowCategory.index') }}
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
				@if( Auth::id() == 1)
             <a href="{{route('workFlowCategory.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
             @endif
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('assign_field_name','Name')</th>
                        <th>@sortablelink('priceRange.price_ranges_name','Price Range')</th>
                        <th>@sortablelink('location.locations_name','Location')</th>
                        <th>@sortablelink('assign_field_status','Status')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lists as $list)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$list->assign_field_name}}</td>
                        <td>{{$list->priceRange->price_ranges_name}}</td>
                        <td>{{$list->location->locations_name}}</td>
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('workFlowCategory.changeStatus',$list->id)}}">@if($list->assign_field_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$list->assign_field_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>      
                        <td>
                        <a href="{{route('workFlowCategory.show',$list->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        <a title="Edit" href="{{route('workFlowCategory.edit',$list->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>                                                   
@if( Auth::id() == 1)
                        <a href="{{route('workFlowCategory.destroy',$list->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>  @endif                        
                        </td>
                    </tr>  
                    @empty
                    <tr>
                        <td colspan="4" align="center">
                        <p>No records</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
              {{ $lists->links() }}      
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
                if (confirm('Do you want to Delete this BuildingType?')) {
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
