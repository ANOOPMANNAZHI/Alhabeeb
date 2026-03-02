@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('complaint.index'))
@section('search_reset', route('complaint.index'))

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Complaint</div>
        </div>
        {{ Breadcrumbs::render('complaint.index') }}
    </div>
</div>
{{-- @if(!Request::exists('params')) --}}
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
{{-- @endif --}}
<div class="clearfix"></div>
{{-- @if(!Request::exists('params')) --}}
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

         @include('maintenance::enquiry_search')  

     </div>
 </div>
</div>
{{-- @endif --}}
<div class="row">
 <div class="col-md-12 col-sm-12">
    <div class="card  card-box">

        <div class="card-body ">
            <h4>
               <div id="pagination_info">
                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
               </div>
               @can('complaint_enquiries_add')
               <a href="{{route('complaint.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
               @endcan
               <div class="clr"></div>
           </h4>
           <div class="table-wrap">
            <div class="table-responsive">  
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('complaint_no','Comp No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('created_at','Time Since',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complainer_name','Comp   Name',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaint_mob_no','Comp Mob No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('building_id','Building',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('unit_id','Unit',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('location_id','Location',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('priority_status','Priority',[],[ 'class' => 'sort_url'])</th>

                        <th title="Status">@sortablelink('complaint_status','Status',[],[ 'class' => 'sort_url'])</th> 
                        <th>Action</th>
                    </tr>
                   {{-- @if(!Request::exists('params')) --}}
                    <tr>
                        <td> <input autocomplete="off" type="text" name="complaint_no" class="search_fields mob" id="complaint_no" value="{{old('complaint_no')}}" ></td>
                        <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complainer_name" class="search_fields mob" id="complainer_name" value="{{old('complainer_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complaint_mob_no" class="search_fields mob" id="complaint_mob_no"  value="{{old('complaint_mob_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit__unit_code')}}" >
                            <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('complaintSearch')}}" ></td>
                            <td><input autocomplete="off" type="text" name="location_id" class="search_fields mob" id="location_id"  value="{{old('location__locations_name')}}" ></td>

                            <td>
                                <select name="priority_status" class="searchFields search_fields mob" id="priority_status" style="width:100px;">
                                    <option value="">Show All</option>
                                    <option {{ (old('priority_status') === 0)? 'selected' : '' }} value="0">{{'Normal'}}</option>
                                    <option {{ (old('priority_status') === 1)? 'selected' : '' }} value="1">{{'High'}}</option>
                                </select> 
                            </td>



                            
                            <td>
                                <select name="status" class="searchFields search_fields mob" id="status" style="width:100px;">
                                    <option value="">Show All</option>
                                    
                                    

                                    <option {{ (old('complaint_status') != '')? ((old('complaint_status') == 0)? 'selected' : '' ) : ''}} value="0">{{'Open'}}</option>


                                    <option {{ (old('complaint_status') == 1)? 'selected' : '' }} value="1">{{'Partialy Closed'}}</option>
                                    <option {{ (old('complaint_status') == 2)? 'selected' : '' }} value="2">{{'Closed'}}</option>
                                    
                                </select>
                            </td>
                            <td></td>
                        </tr>
                       {{-- @endif --}}
                    </thead>
                    <tbody id="enquiry-search">

                        @include('maintenance::complaint_enquiry_list_ajax')  

                    </tbody>
                </table>
            </div>
        </div>

        <div id="pagination">                    
            {{$ComplaintEnquiries->appends(\Request::except('page'))->links()}}            
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
@include('maintenance::enquiry_search_js')
@include('maintenance::search_js')
<script>   
    jQuery('.delete_type').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Complaint?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
    });
    /**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
</script>

@endsection
