@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Action</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Action</li>
        </ol>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            @can('add_action')
				@if( Auth::id() == 1)
             <a href="{{route('action.create')}}" class="btn btn-circle btn-primary  align-right"  >Add Action</a>
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
                        <th>Action Name</th>
                        <th>Action Key</th>
                        @can('change_status_action')
                        @if( Auth::id() == 1)
                        <th>Status</th>
                        @endif
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($actions as $action)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$action->action_name}}</td>
                        <td>{{$action->action_key}}</td>
                        @can('change_status_action')
                        @if( Auth::id() == 1)
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('action.changeStatus',$action->id)}}">@if($action->action_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$action->work_flows_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>
                        @endif
                        @endcan      
                        <td>
                        @can('action_list')
                        <a href="{{route('action.show',$action->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a> 
                        @endcan
                        @can('edit_action')
                        <a title="Edit" href="{{route('action.edit',$action->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan
                        @can('delete_action')                                                   
                        @if( Auth::id() == 1)
                        <a href="{{route('action.destroy',$action->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>  @endif
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

  jQuery('#dtBasicExample').DataTable({
    "paging": false ,  "searching": false ,"info": false 
  });
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
