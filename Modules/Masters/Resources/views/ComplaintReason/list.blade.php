@extends('layouts.plms-app')

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Complaint Reason</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Complaint Reason</li>
        </ol>
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
			  @can('add_complaint_reason')
             <a href="{{route('complaintReason.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
              @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('complaint_reason_name','Name')</th>
                        @can('change_status_complaint_reason')
                        <th>@sortablelink('complaint_reason_status','Status')</th>
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($complaints as $complaint)
                    <tr>
                        <td>
							<a @can('edit_complaint_reason') href="{{route('complaintReason.edit',$complaint->id)}}" title="Edit" @elsecan('view_enquiry_source') href="{{route('complaintReason.show',$complaint->id)}}" title="Show"  @endcan class="no-link" >
								{{$complaints->perPage()*($complaints->currentPage()-1)+$count}}
							</a>
                        </td>
                        <td>
							<a @can('edit_complaint_reason') href="{{route('complaintReason.edit',$complaint->id)}}" title="Edit" @elsecan('view_enquiry_source') href="{{route('complaintReason.show',$complaint->id)}}" title="Show"  @endcan class="no-link" >
								{{$complaint->complaint_reason_name}}
							</a>
						</td>
                        @can('change_status_complaint_reason')
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('complaintReason.changeStatus',$complaint->id)}}">@if($complaint->complaint_reason_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$complaint->complaint_reason_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>  
                        @endcan    
                        <td>
						@can('view_complaint_reason')
                        <a href="{{route('complaintReason.show',$complaint->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @endcan 
                        @can('edit_complaint_reason')
                        <a title="Edit" href="{{route('complaintReason.edit',$complaint->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>   
                        @endcan                                                 
                         @can('delete_complaint_reason')
                        <a href="{{route('complaintReason.destroy',$complaint->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a>  
                        @endcan                      
                        </td>
                    </tr>  
                    @php $count++; @endphp 
                    @empty
                    <tr class="no-record">
                        <td colspan="4">
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
					$complaints->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
				@endphp  
              {{$complaints->links()}}     
                    
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
                if (confirm('Do you want to delete this Complaint Reason')) {
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
