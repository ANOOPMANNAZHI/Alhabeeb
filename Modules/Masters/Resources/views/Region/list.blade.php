@extends('layouts.plms-app')

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Region</div>
        </div>
        {{ Breadcrumbs::render('region.index') }}
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
                     @include('includes.pagination_info',['paginator' => $locations])         
                 </div>
			 @can('add_location')
             <a href="{{route('region.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('region_code','Code')</th>
                        <th>@sortablelink('region_name','Name')</th>
                        @can('change_status_region')
                        <th>@sortablelink('region_status','Status')</th>
                        @endcan
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($locations as $location)
                    <tr>
                        <td>
							<a @can('edit_region') href="{{route('region.edit',$location->id)}}" title="Edit" @elsecan('view_region') href="{{route('region.show',$location->id)}}" title="Show"  @endcan class="no-link" >
								{{$locations->perPage()*($locations->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_region') href="{{route('region.edit',$location->id)}}" title="Edit" @elsecan('view_region') href="{{route('region.show',$location->id)}}" title="Show"  @endcan class="no-link" >
								{{$location->region_code}}
							</a>
						</td>
                        <td>
							<a @can('edit_region') href="{{route('region.edit',$location->id)}}" title="Edit" @elsecan('view_region') href="{{route('region.show',$location->id)}}" title="Show"  @endcan class="no-link" >
								{{$location->region_name}}
							</a>
						</td>
                         @can('change_status_region')
                        <td >
                            <a title="Change Status" class="change_status" href="{{route('region.changeStatus',$location->id)}}">@if($location->region_status==1)<button type="button" class="btn btn-circle btn-success btn-sm m-b-10">Active</button>@else <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">Inactive</button> @endif
                            
                            </a>
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$location->region_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>
                        @endcan      
                        <td>
						@can('view_region')
                        <a href="{{route('region.show',$location->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @endcan
                         @can('edit_region')
                        <a title="Edit" href="{{route('region.edit',$location->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan    
                        @can('delete_region')
                        <a href="{{route('location.destroy',$location->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
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
              
                @php
					$sort =  app('request')->input('sort') ;
					if(!empty($sort)){
					$direction =  app('request')->input('direction') ;
					$locations->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
			
			
               {{ $locations->links() }}     
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
	
    $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
       $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });

  jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Location?')) {
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
