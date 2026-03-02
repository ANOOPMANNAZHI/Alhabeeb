@extends('layouts.plms-app')

 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Vendor</div>
        </div>
        {{ Breadcrumbs::render('vendors.index') }}
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
                     @include('includes.pagination_info',['paginator' => $vendors])         
                 </div>
			 @can('add_vendor')
             <a href="{{route('vendors.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             @endcan
             <div class="clr"></div>
            </h4>
              <div class="table-wrap">
				  <div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('vendor_name','Name')</th>
                        <th>@sortablelink('vendor_code','Vendor Code')</th>
                        <th>@sortablelink('vendorType.vendor_types_name','Vendor Type')</th>
                        <th>@sortablelink('vendor_status','Status')</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($vendors as $vendor)
                    <tr>
                        <td>
							<a @can('edit_vendor') href="{{route('vendors.edit',$vendor->id)}}" title="Edit" @elsecan('view_vendor') href="{{route('vendors.show',$vendor->id)}}" title="View"  @endcan class="no-link" >
								{{$vendors->perPage()*($vendors->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_vendor') href="{{route('vendors.edit',$vendor->id)}}" title="Edit" @elsecan('view_vendor') href="{{route('vendors.show',$vendor->id)}}" title="View"  @endcan class="no-link" >
								{{$vendor->vendor_name}}
							</a>
						</td>                            
                        <td>
							<a @can('edit_vendor') href="{{route('vendors.edit',$vendor->id)}}" title="Edit" @elsecan('view_vendor') href="{{route('vendors.show',$vendor->id)}}" title="View"  @endcan class="no-link" >
								{{$vendor->vendor_code}}
							</a>
						</td>                            
                        <td>
							<a @can('edit_vendor') href="{{route('vendors.edit',$vendor->id)}}" title="Edit" @elsecan('view_vendor') href="{{route('vendors.show',$vendor->id)}}" title="View"  @endcan class="no-link" >
								{{$vendor->vendorType->vendor_types_name}}
							</a>
						</td> 
                        @can('change_status_vendor')                           
                        <td>
                        <a title="Change Status" class="change_status" 
                        href="{{route('vendors.changeStatus',$vendor->id)}}">
                        @if($vendor->vendor_status==1)
                        <button type="button" class="btn btn-circle btn-success btn-sm m-b-10 status">{{$vendor->vendor_status_name}}</button>
                        @else 
                        <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">{{$vendor->vendor_status_name}}</button>
                        @endif
                        </a>
                        
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$vendor->vendor_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td> 
                        @endcan                           
                        <td>
							@can('view_vendor')        
                        <a href="{{route('vendors.show',$vendor->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                           @endcan
                        @can('edit_vendor')        
                        <a title="Edit" href="{{route('vendors.edit',$vendor->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
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
					$vendors->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			@endphp  
              {{ $vendors->links() }}      
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
