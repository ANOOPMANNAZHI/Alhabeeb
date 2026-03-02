@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<style>
    input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
</style>
@endsection

@section('search_url', route('areBuildingSearch')) 
@section('search_reset', route('areBuildingAssign.index'))  

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">ARE Building Assign</div>
        </div>
        {{ Breadcrumbs::render('areBuildingAssign.index') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

         @include('masters::AreBuildingAssign.are_assign_search')  

     </div>
 </div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
    <div class="card  card-box">

        <div class="card-body ">
        <h4>
        <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $areBuildingAssigns])         
                 </div>
            @can('are_building_assign_view')

            <a href="{{route('areBuildingAssign.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
            @endcan
             <div class="clr"></div>
            </h4>
          <table class="table display product-overview mb-30" id="dtBasicExample">
            <thead>
                <tr>
                    <th>@sortablelink('areUser.username','ARE')</th>
                    <th>Buildings</th>
                    <th>@sortablelink('assign_from','From Dt')</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td> <input autocomplete="off" type="text" name="user_id" class="search_fields mob" id="user_id" value="{{old('user_id')}}" ></td>


                    <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building_id')}}" ></td>

                    <td><input autocomplete="off" type="date" name="assign_from" class="search_fields mob" id="assign_from" value="{{old('assign_from')}}" ></td>

                    <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('areBuildingSearch')}}" ></td>
                    <td></td>
                </tr>
            </thead>
            <tbody id="enquiry-search">

                @include('masters::AreBuildingAssign.are_building_assign_list_ajax')  

            </tbody>
        </table>

        <div class="row "  id="pagination">
         @php 
         if(!empty($request->user_id))
         $areBuildingAssigns->appends(['user_id' => $request->user_id]);


         if(!empty($request->building_id))
         $areBuildingAssigns->appends(['building_id' => $request->building_id]);

         if(!empty($request->assign_from))
         $areBuildingAssigns->appends(['assign_from' => $request->assign_from]);

         $sort =  app('request')->input('sort');

         if(!empty($sort)){
         $direction =  app('request')->input('direction') ;
         $areBuildingAssigns->appends(['sort' => $sort, 'direction' => $direction ]);

     }

     $fieldName =  app('request')->input('fieldName');

     if(!empty($fieldName)){
     $fieldName =  app('request')->input('fieldName');
     $operation =  app('request')->input('operation');
     $fieldValue =  app('request')->input('fieldValue');
     $logic =  app('request')->input('logic');

     $areBuildingAssigns->appends(['fieldName' => $fieldName,
     'operation' => $operation,
     'fieldValue' => $fieldValue,
     'logic' => $logic,
     ]);    
 }


 @endphp                
 {{$areBuildingAssigns->links()}} 
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
{{-- @include('maintenance::enquiry_search_js') --}}
@include('masters::AreBuildingAssign.are_assign_js')
@include('masters::AreBuildingAssign.are_assign_search_js')
<script>  
 
 $(document).ready(function(){

   $(document).on('click','.delete_type',function(event){
       var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Complaint?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
   });


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
