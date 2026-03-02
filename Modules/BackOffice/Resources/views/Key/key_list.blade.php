@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
<style>
    input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
</style>
@endsection

@section('search_url', route('keyManagement.index')) 
@section('search_reset', route('keyManagement.index')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Key</div>
        </div>
         {{ Breadcrumbs::render('keyManagement.index') }} 
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
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $keys])         
                 </div>
                   <div class="clr"></div>
        </h4>
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        
                        <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('unit.unit_code','Unit',[],[ 'class' => 'sort_url' ])</th>
                        <th><a class="sort_url" href="">Role <i class="fa fa-sort"></i></a></th>
                        <th><a class="sort_url" href="">User <i class="fa fa-sort"></i></a></th>
                        <th>Action</th>
                    </tr>
                    <tr> 
                                             
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building__building_name"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="Unit__unit_code"  value="{{old('Unit__unit_code')}}" >
                        </td>
                        <td> <input autocomplete="off" type="text" name="role" class="search_fields mob" id="role"  value="" ></td>
                        <td> <input autocomplete="off" type="text" name="user" class="search_fields mob" id="user"  value="" > </td> 
                        <td> </td> 
                    </tr>
                </thead>
                <tbody id="enquiry-search">                 
                  @include('backoffice::Key.key_list_ajax')
                </tbody>
            </table>
            
            <div class="row "  id="pagination">                         
            {{$keys->appends(\Request::except(['page','ajax','_token','route']))->links()}} 
           </div> 
   
</div>
</div>
</div>
</div>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts') 
@include('sales::enquiry_search_js')
@include('backoffice::Key.key_quick_search_js') 
 {{--quick search --}}
 <script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
$(document).on('click','.keyHandover',function(){       
        var key_id =  $(this).attr('data-id');
        var type =  $(this).attr('datas-id');

        $.ajax({
          method: 'POST', // Type of response and matches what we said in the route
          url: "{{route('keyAcceptAginstLandlordTenant')}}", // This is the url we gave in the route
          data: {'key_id':key_id,'type' : type,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
          success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
          },
        });
        return true; 
         
});   
/**********************************************************************************/
 $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
  $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    });
/**********************************************************************************/


</script>

@endsection
