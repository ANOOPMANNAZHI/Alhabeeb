@extends('layouts.plms-app')

 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Enquiry Source</div>
        </div>
        {{ Breadcrumbs::render('enquirySource.index') }}
    </div>
</div>

<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show">
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
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $enquirySources])         
                 </div>
			 @can('add_enquiry_source')
             <a href="{{route('enquirySource.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             @endcan 
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('enquiry_sources_name','Source')</th>
                        @can('change_status_enquiry_source')
                        <th>@sortablelink('enquiry_sources_status','Status')</th>
                        @endcan 
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($enquirySources as $enquirySource)
                    <tr>
                        <td>
							<a @can('edit_enquiry_source') href="{{route('enquirySource.edit',$enquirySource->id)}}" title="Edit" @elsecan('view_enquiry_source') href="{{route('reason.show',$enquirySource->id)}}" title="Show"  @endcan class="no-link" >
								{{$enquirySources->perPage()*($enquirySources->currentPage()-1)+$count}}
							</a>
                        </td>
                        <td>
							<a @can('edit_enquiry_source') href="{{route('enquirySource.edit',$enquirySource->id)}}" title="Edit" @elsecan('view_enquiry_source') href="{{route('reason.show',$enquirySource->id)}}" title="Show"  @endcan class="no-link" >
								{{$enquirySource->enquiry_sources_name}}
							</a> 
						</td>
                        @can('change_status_enquiry_source')
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('enquirySource.changeStatus',$enquirySource->id)}}">@if($enquirySource->enquiry_sources_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                             
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$enquirySource->enquiry_sources_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>
                        @endcan       
                        <td>
						@can('view_enquiry_source')
                        <a href="{{route('enquirySource.show',$enquirySource->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                        @endcan  
                        @can('edit_enquiry_source')
                        <a title="Edit" href="{{route('enquirySource.edit',$enquirySource->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan 
                        
                        @can('delete_enquiry_source')                                                   

                        <a href="{{route('enquirySource.destroy',$enquirySource->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a> 
                        @endcan                       
                        </td>
                    </tr>  
                     @php $count++; @endphp 
                    @empty
                    <tr>
                        <td colspan="4">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
              {{ $enquirySources->links() }}      
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
                if (confirm('Do you want to Delete this Enquiry Source?')) {
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
       
    $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });


   });

</script> 


@endsection
