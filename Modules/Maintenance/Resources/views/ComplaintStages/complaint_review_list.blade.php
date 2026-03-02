@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('complaintReviewList'))
@section('search_reset', route('complaintReviewList'))
@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Review</div>
        </div>
         {{ Breadcrumbs::render('complaintReviewList') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
          @include('maintenance::enquiry_search')  
 
        </div>
    </div>
</div>
<div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                 </div>
                   <div class="clr"></div>
            </h4>
            <div class="float-left"></div>
            <div class="table-wrap">
                <div class="table-responsive"> 
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('service_report_no','Wk Ord No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaint_no','Comp No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaint_date','Date',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complainer_name','Name',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaint_mob_no','Mob No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('unit_code','Unit',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('locations_name','Location',[],[ 'class' => 'sort_url'])</th>
                        <th>Status</th> 
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td> <input autocomplete="off" type="text" name="work_order_no" class="search_fields mob" id="service_report_no" value="{{old('service_report_no')}}" ></td>
                        <td> <input autocomplete="off" type="text" name="complaint_no" class="search_fields mob" id="complaint_no" value="{{old('complaint_no')}}" ></td>
                        <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('complaint_date')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complainer_name" class="search_fields mob" id="complainer_name" value="{{old('complainer_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complaint_mob_no" class="search_fields mob" id="complaint_mob_no"  value="{{old('complaint_mob_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit__unit_code')}}" >
                        <td><input autocomplete="off" type="text" name="location_id" class="search_fields mob" id="location_id"  value="{{old('location__locations_name')}}" ></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('complaintSearch')}}" ></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                   @include('maintenance::ComplaintStages.complaint_review_list_ajax') 
                                                            
                </tbody>
                </table> 
                </div>
                </div>   
                 <div class="row "  id="pagination">                              
                     {{$ComplaintEnquiries->appends(\Request::except(['page','_token','ajax']))->links()}}
                </div>             
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal"></div>
@endsection
@section('scripts') 
@include('maintenance::enquiry_search_js')
@include('maintenance::search_js')
@include('maintenance::complaint_js')
<script>   
/**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
</script>

@endsection