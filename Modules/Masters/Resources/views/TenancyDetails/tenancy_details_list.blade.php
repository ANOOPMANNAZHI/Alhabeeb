@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

 @section('search_url', route('tenancyDetails.index')) 
@section('search_reset', route('tenancyDetails.index')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenancy</div>
            <h5><b>As on Date:{{date('d/m/Y')}}</b></h5>
        </div>
        {{ Breadcrumbs::render('tenancyDetails.index') }} 
    </div>
</div>

<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

           @include('sales::enquiry_search')  

     </div>
 </div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
    <div class="card  card-box">

        <div class="card-body ">
            <h4>
             {{-- @can('amc_contract_add') --}}

             <!-- <a href="{{route('amcContract.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a> -->  
             {{-- @endcan --}}
             <div class="clr"></div>
         </h4>
         <table class="table display product-overview mb-30" id="dtBasicExample">
            <thead>
                <tr>
                    <th>Unit No</th>
                    <th>Unit Type</th>
                    <th>Tenant Name</th>
                    <th>Contact No</th>
                    <th>Contract Period From</th>
                    <th>Contract Period  To</th>
                    <th>Rent</th>
                    <th>Payment Mode</th>
                    <th>Rent Received Till</th>
                </tr>              
            </thead>
            <tbody id="enquiry-search">

                @include('masters::TenancyDetails.tenancy_details_list_ajax')  

            </tbody>
        </table>

<div class="row "  id="pagination">                        
   
</div> 

</div>
</div>
</div>
</div>
<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 
@include('sales::enquiry_search_js')
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
    /**********************************************************************************/
</script>

@endsection