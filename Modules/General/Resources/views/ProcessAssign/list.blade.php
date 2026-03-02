@extends('layouts.plms-app')

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Process Assign</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Process Assign </li>
        </ol>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('add_workflow_assign')
			@if( Auth::id() == 1)
             <a href="{{route('processAssign.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
             @endif
            @endcan
             <div class="clr"></div>
            </h4>
               <div class="table-wrap">
				  <div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('work_process.workflow','Work-Flow Name')</th>
                        <th>@sortablelink('work_process.work_flow_processes_name','Work-Flow Process')</th>
                        <th>@sortablelink('location.locations_name','Location')</th>
                        <th>@sortablelink('price.price_ranges_name','Range')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                      @forelse ($processAssigns as $assign)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$assign->work_process->workflow->work_flows_name}}</td>
                       <td>{{$assign->work_process->work_flow_processes_name}}</td>
                       <td>@if($assign->location_id){{$assign->location->locations_name}} @endif</td>
                       <td>@if($assign->price_range_id){{$assign->price->price_ranges_name}}@endif</td>
                        <td>
                        @can('view_workflow_assign')
                        <a href="{{route('processAssign.show',$assign->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @endcan
                        @can('edit_workflow_assign')
                        <a title="Edit" href="{{route('processAssign.edit',$assign->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                        @endcan 
                        @can('delete_workflow_assign')                                                 
                        @if( Auth::id() == 1)
                        <a href="{{route('processAssign.destroy',$assign->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>
                        @endif                    
                        @endcan
                        </td>
                    </tr>  
                    @empty
                    <tr class="no-record">
                        <td colspan="2">
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
                if (confirm('Do you want to Delete this Process Assign?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });
          
        });

</script> 


@endsection
