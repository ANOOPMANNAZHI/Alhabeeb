@extends('layouts.plms-app')

@section('css') 
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Building Amenity</div>
        </div>
        {{ Breadcrumbs::render('building-amentity.index') }}
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
                     @include('includes.pagination_info',['paginator' => $building_amentities])         
                 </div>
			  @can('add_building_amentity')
             <a href="{{route('building-amentity.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
              @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('amentityType.amentity_types_name','Amenity Type ')</th>
                        <th>@sortablelink('building.building_name','Building') </th>
                        <th>@sortablelink('amc_contract_no','AMC Contract No')</th>                       
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($building_amentities as $building_amentity)
                    <tr>
                        <td>
							<a @can('edit_building_amentity') href="{{route('building-amentity.edit',$building_amentity->id)}}" title="Edit" @elsecan('view_building_amentity') href="{{route('building-amentity.show',$building_amentity->id)}}" title="Show"  @endcan class="no-link" >
								{{$building_amentities->perPage()*($building_amentities->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_building_amentity') href="{{route('building-amentity.edit',$building_amentity->id)}}" title="Edit" @elsecan('view_building_amentity') href="{{route('building-amentity.show',$building_amentity->id)}}" title="Show"  @endcan class="no-link" >
								{{$building_amentity->amentityType->amentity_types_name}}
							</a>
						</td>                            
                        <td>
							<a @can('edit_building_amentity') href="{{route('building-amentity.edit',$building_amentity->id)}}" title="Edit" @elsecan('view_building_amentity') href="{{route('building-amentity.show',$building_amentity->id)}}" title="Show"  @endcan class="no-link" >
								{{$building_amentity->building->building_name}}
							</a>
						</td>                            
                        <td>
							<a @can('edit_building_amentity') href="{{route('building-amentity.edit',$building_amentity->id)}}" title="Edit" @elsecan('view_building_amentity') href="{{route('building-amentity.show',$building_amentity->id)}}" title="Show"  @endcan class="no-link" >
								{{$building_amentity->amc_contract_no}}
							</a>
						</td>                            
                        <td>
						 @can('view_building_amentity')
                        <a href="{{route('building-amentity.show',$building_amentity->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                        </a>
                        @endcan
                        @can('edit_building_amentity') 
                        <a title="Edit" href="{{route('building-amentity.edit',$building_amentity->id)}}" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil"></i>
                        </a>  
                        @endcan
                        @can('delete_building_amentity') 
                        @if($building_amentity->amc_contract_no == '')
                        <a href="{{ route('building-amentity.destroy',$building_amentity->id) }}" class="btn btn-tbl-delete btn-xs delete_type" title="Delete">
                           <i class="fa fa-trash-o "></i>
                        </a> 
                        @endif 
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
					$building_amentities->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
			
              {{ $building_amentities->links() }}      
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
  jQuery('#dtBasicExample').DataTable({
    "paging": false ,  "searching": false ,"info": false 
  });
  jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Amenity?')) {
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
