@extends('layouts.plms-app')
 
@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Workflow Stage</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Workflow Stage</li>
        </ol>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('add_workflow_process')
			@if( Auth::id() == 1)	
             <a href="{{route('workFlowProcess.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
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
                        <th>@sortablelink('work_flow_processes_name','Stage Name')</th>
                        <th>@sortablelink('work_flow_processes_code','Workflow Code')</th>
                        <th>@sortablelink('work_flows_name','Workflow Name')</th>
                        @can('change_status_workflow_process')
                        @if( Auth::id() == 1)
                        <th>@sortablelink('changeStatus','Status')</th>
                        @endif
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workFlowProcessList as $process)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$process->work_flow_processes_name}}</td>
                        <td>{{$process->work_flow_processes_code}}</td>
                        <td>{{$process->workflow->work_flows_name}}</td>
                        @can('change_status_workflow_process')
                        @if( Auth::id() == 1)
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('workFlowProcess.changeStatus',$process->id)}}">@if($process->work_flow_processes_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        
                        <form id="status-form" action="" method="POST">
                             {{csrf_field()}}
                             <input type="hidden" name="status" value="{{$process->work_flow_processes_status}}">
                            <input style="display: none;" type="submit">
                        </form>
                        </td> 
                        @endif
                        @endcan     
                        <td>
                        @can('workflow_process_list')
                        <a href="{{route('workFlowProcess.show',$process->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                        @endcan
                        @can('edit_workflow_process') 
                        <a title="Edit" href="{{route('workFlowProcess.edit',$process->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                        @endcan
                        @can('delete_workflow_process')                                                  
                        @if( Auth::id() == 1)
                        <a href="{{route('workFlowProcess.destroy',$process->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>     
                        @endif 
                        @endcan                    
                        </td>
                    </tr>  
                    @empty
                    <tr class="no-record">
                        <td colspan="2">
                        <p>No Records</p>
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
                if (confirm('Do you want to Delete this designation?')) {
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
