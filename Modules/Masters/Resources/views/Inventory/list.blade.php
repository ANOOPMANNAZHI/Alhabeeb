@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Inventory</div>
        </div>
         {{ Breadcrumbs::render('inventory.index') }}
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
                     @include('includes.pagination_info',['paginator' => $inventories])         
                 </div>
			  @can('add_inventory')
             <a href="{{route('inventory.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
              @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('inventories_name','Name')</th>
                        <th>@sortablelink('inventories_brand_name','Brand Name')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($inventories as $inventory)
                    <tr>
                        <td>
							<a @can('edit_inventory') href="{{route('inventory.edit',$inventory->id)}}" title="Edit" @elsecan('view_inventory') href="{{route('inventory.show',$inventory->id)}}" title="Show"  @endcan class="no-link" >
								{{$inventories->perPage()*($inventories->currentPage()-1)+$count}}
							</a>
                        </td>
                        <td>
							<a @can('edit_inventory') href="{{route('inventory.edit',$inventory->id)}}" title="Edit" @elsecan('view_inventory') href="{{route('inventory.show',$inventory->id)}}" title="Show"  @endcan class="no-link" >
								{{$inventory->inventories_name}}
							</a>
						</td>
                        <td>
							<a @can('edit_inventory') href="{{route('inventory.edit',$inventory->id)}}" title="Edit" @elsecan('view_inventory') href="{{route('inventory.show',$inventory->id)}}" title="Show"  @endcan class="no-link" >
								{{$inventory->inventories_brand_name}}
							</a>
						</td>      
                        <td>
						 @can('view_inventory')
                        <a href="{{route('inventory.show',$inventory->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                        </a>
                         @endcan
                         @can('edit_inventory') 
                        <a title="Edit" href="{{route('inventory.edit',$inventory->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a> 
                         @endcan
                         @can('delete_inventory')
                        <a href="{{route('inventory.destroy',$inventory->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
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
					$inventories->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
              {{ $inventories->links() }}      
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
                if (confirm('Do you want to Delete this Inventory?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });
    });
           

</script> 


@endsection
