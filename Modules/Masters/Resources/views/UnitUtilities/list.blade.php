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
            <div class="page-title">Unit Utilities</div>
        </div>
        {{ Breadcrumbs::render('unit-utility.index') }}
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
			  @can('add_unit_utility')
             <a href="{{route('unit-utility.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a>
              @endcan
             <div class="clr"></div>
            </h4>
              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('homeUtility.home_utilities_code','Home Utility') </th>
                        <th>@sortablelink('unit.unit_code','Unit')</th>
                        <th>@sortablelink('amc_contract_no','AMC Contract No')</th>                        
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($unit_utilities as $unit_utility)
                    <tr>
                        <td>
							<a @can('edit_unit_utility') href="{{route('unit-utility.edit',$unit_utility->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('unit-utility.show',$unit_utility->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit_utilities->perPage()*($unit_utilities->currentPage()-1)+$count}}
							</a>						
						</td>
                        <td>
							<a @can('edit_unit_utility') href="{{route('unit-utility.edit',$unit_utility->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('unit-utility.show',$unit_utility->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit_utility->homeUtility->home_utilities_code}}
							</a>
						</td>
                        <td>
							<a @can('edit_unit_utility') href="{{route('unit-utility.edit',$unit_utility->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('unit-utility.show',$unit_utility->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit_utility->unit->unit_code}}
							</a>
						</td>
                        <td>
							<a @can('edit_unit_utility') href="{{route('unit-utility.edit',$unit_utility->id)}}" title="Edit" @elsecan('tenant_list') href="{{route('unit-utility.show',$unit_utility->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit_utility->amc_contract_no}}
							</a>
						</td>
                        <td>
							@can('view_unit_utility')
                        <a href="{{route('unit-utility.show',$unit_utility->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-list "></i>
                        </a>
                             @endcan
                        
                        @can('edit_unit_utility') 
                        <a title="Edit" href="{{route('unit-utility.edit',$unit_utility->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan                                            
                        </td>
                    </tr>  
                     @php $count++; @endphp
                    @empty
                    <tr>
                        <td colspan="4" align="center">
                        <p>No records</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
              @php
					$sort =  app('request')->input('sort') ;
					if(!empty($sort)){
					$direction =  app('request')->input('direction') ;
					$unit_utilities->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
              {{ $unit_utilities->links() }}      
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
                if (confirm('Do you want to Delete this Vendor Type?')) {
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
