@extends('layouts.plms-app')

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title"> 
              Building Insurance 
            </div>
        </div>
        {{ Breadcrumbs::render('building-insurance.index') }}
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
                     @include('includes.pagination_info',['paginator' => $insurance])         
                 </div>
             {{-- @can('add_complaint_reason')  --}}
             <a href="{{route('building-insurance.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             {{-- @endcan--}}
             <div class="clr"></div>
            </h4>
              <div class="table-wrap">
				  <div class="table-responsive1">
              <table class="table display product-overview mb-30 " id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('insurance_company','Insurance Company')</th>
                        <th>@sortablelink('building.building_name','Building Name')</th>
                        <th>@sortablelink('insurance_start','Valid From')</th>
                        <th>@sortablelink('insurance_end','Valid From')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
				   @php $count = 1; @endphp
                   @forelse ($insurance as $insurance_item)
                    <tr>
                        <td>
							<a @can('edit_building_insurance') href="{{route('building-insurance.edit',$insurance_item->id)}}" title="Edit" @elsecan('view_building_insurance') href="{{route('insurance_item.show',$insurance_item->id)}}" title="Show"  @endcan class="no-link" >
								{{$insurance->perPage()*($insurance->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_building_insurance') href="{{route('building-insurance.edit',$insurance_item->id)}}" title="Edit" @elsecan('view_building_insurance') href="{{route('insurance_item.show',$insurance_item->id)}}" title="Show"  @endcan class="no-link" >
								{{$insurance_item->insurance_company}}
							</a>
						</td>
                        <td>
							<a @can('edit_building_insurance') href="{{route('building-insurance.edit',$insurance_item->id)}}" title="Edit" @elsecan('view_building_insurance') href="{{route('insurance_item.show',$insurance_item->id)}}" title="Show"  @endcan class="no-link" >
								{{$insurance_item->building->building_name}}
							</a>
						</td>
                        <td>
							<a @can('edit_building_insurance') href="{{route('building-insurance.edit',$insurance_item->id)}}" title="Edit" @elsecan('view_building_insurance') href="{{route('insurance_item.show',$insurance_item->id)}}" title="Show"  @endcan class="no-link" >
								{{$insurance_item->insurance_start->format('d-m-Y')}}
							</a>
						</td> 
						<td>
							<a @can('edit_building_insurance') href="{{route('building-insurance.edit',$insurance_item->id)}}" title="Edit" @elsecan('view_building_insurance') href="{{route('insurance_item.show',$insurance_item->id)}}" title="Show"  @endcan class="no-link" >
								{{$insurance_item->insurance_end->format('d-m-Y')}}
							</a>
                        </td>
                        <td>
                   
							<a href="{{route('building-insurance.show',$insurance_item->id)}}" title="View" class="btn btn-tbl-view btn-xs">
								<i class="fa fa-eye"></i>
							</a> 
					   
							@can('edit_building_insurance')
							<a title="Edit" href="{{route('building-insurance.edit',$insurance_item->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
								<i class="fa fa-pencil"></i>
							</a>   
							@endcan                                        
							 @can('delete_building_insurances')
							<a href="{{route('building-insurance.destroy',$insurance_item->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
								<i class="fa fa-trash-o "></i>
							</a>  
							@endcan                      
                        </td>
                    </tr>  
                     @php $count++; @endphp
                    @empty
                    <tr class="no-record">
                        <td colspan="6">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse 
                </tbody>
              </table> 
              
              </div>
              @php
                    $sort =  app('request')->input('sort') ;
                    if(!empty($sort)){
                    $direction =  app('request')->input('direction') ;
                    $insurance->appends(['sort' => $sort, 'direction' => $direction ]);                    
                    }                               
                @endphp  
              {{--$complaints->links()--}}     
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
                if (confirm('Do you want to delete this Building Insurance ?')) {
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
