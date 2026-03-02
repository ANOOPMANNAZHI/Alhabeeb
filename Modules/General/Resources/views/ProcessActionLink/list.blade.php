@extends('layouts.plms-app')


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Process Action Link</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Process Action Link</li>
        </ol>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $processActionLink])         
                 </div>
            @can('add_process_action_link')
			@if( Auth::id() == 1)	
             <a href="{{route('processActionLink.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
             @endif
            @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('process_name.work_flow_processes_name','Process Name')</th>
                        <th>@sortablelink('action_name.action_name','Action')</th>
                        <th>@sortablelink('process_name_next_stage.work_flow_processes_name','Next Process Name')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($processActionLink as $process)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$process->process_name->work_flow_processes_name??"NA"}}</td>
                        <td>{{$process->action_name->action_name}}</td>
                        <td >
                           {{$process->process_name_next_stage->work_flow_processes_name??"NA"}}               
                        </td>      
                        <td>
                        @can('process_action_link_list')
                        <a href="{{route('processActionLink.show',$process->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                        @endcan
                        @can('edit_process_action_link') 
                        <a title="Edit" href="{{route('processActionLink.edit',$process->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                        @endcan
                        @can('delete_process_action_link')                                                  
                        @if( Auth::id() == 1)
                        <a href="{{route('processActionLink.destroy',$process->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
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
                {{$processActionLink->links()}}    
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
