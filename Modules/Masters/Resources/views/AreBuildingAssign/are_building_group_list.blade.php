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

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">ARE'S</div>
        </div>
        {{ Breadcrumbs::render('group_are_list') }}
    </div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
    <div class="card  card-box">

        <div class="card-body ">
        <h4>
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
                
            </thead>
            <tbody id="enquiry-search">
           
                @forelse ($areBuildingAssigns as $areBuildingAssign)

                       
               <tr>
            
                   <td><a class="no-link" href="{{route('groupView',[$areBuildingAssign->id])}}">{{$areBuildingAssign->areUser->username }}</a></td>

                   <td><a class="no-link" href="{{route('groupView',[$areBuildingAssign->id])}}">{{$areBuildingAssign->assignedBuildingNames->implode('building_name',' ,   ')}}</a></td>

                   <td><a class="no-link" href="{{route('groupView',[$areBuildingAssign->id])}}">{{$areBuildingAssign->assign_from->format('d/m/Y')}}</a></td>
                   
                      
                    <td>
                      
                      <a href="{{route('groupView',[$areBuildingAssign->id])}}" title="View" class="btn btn-tbl-view btn-xs">
                        <i class="fa fa-eye "></i>
                    </a>
              
          
                  </td>
            </tr>
              
         @empty 
         <tr>
           <td colspan="10" align="center">
              <p>No Record</p>
          </td>
        </tr>
      @endforelse



            </tbody>
        </table>
   

</div>
</div>
</div>
</div>
@endsection

