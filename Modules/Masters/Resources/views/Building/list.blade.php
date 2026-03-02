@extends('layouts.plms-app')

@section('css') 
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<!-- Owl Carousel Assets -->
<link href="{{ asset('public/plugins/owl-carousel/owl.carousel.css')}}" rel="stylesheet">
<link href="{{ asset('public/plugins/owl-carousel/owl.theme.css')}}" rel="stylesheet">
@endsection 

@section('content')
<!-- start widget -->

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Building</div>
    </div>
    {{ Breadcrumbs::render('building.index') }}
  </div>
</div>
@can('view_building_details')
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
@endcan
@can('view_building_details')


<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
   <h4>
   <div class="row">
   <div class="col-sm-9">
     <div id="pagination_info" class="page_info">
       @include('includes.pagination_info',['paginator' => $buildings])         
     </div>
     </div>
      <div class="col-sm-2">
     <div class="swipe_togg">
        <select name="swipe" id="swipe" class="form-control switch-swipe">

         <option {{ isset($_GET['swipe'])? 
         ((old('swipe',$_GET['swipe']) == 1) ? 'selected' : '') : ((old('swipe') == 1)? 'selected' : '' ) }}  value="1">Grid</option>

         <option {{ isset($_GET['swipe'])? 
         ((old('swipe',$_GET['swipe']) == 2) ? 'selected' : '') : ((old('swipe') == 1)? 'selected' : '' ) }}  value="2">Picture</option>
       </select>
       </div>
       </div>
        <div class="col-sm-1">
     @can('edit_building') 
     <a href="{{route('building.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
     @endcan
     </div>
     </div>
     <div class="clr"></div>
   </h4>
   <div class="table-responsive1 building-card1" id="building-card1">
    <h4>

     <div class="clr"></div>
   </h4>
   <table class="table display product-overview mb-30" id="dtBasicExample">
    <thead>
      <tr>
        <th>Sl No.</th>
        <th>@sortablelink('building_name','Building Name',[], ['class' => 'sort_class'])</th>                       
        <th>@sortablelink('vendor.vendor_name','Landlord Name',[], ['class' => 'sort_class'])</th>
        <th>@sortablelink('management.management_types_name','Management Type',[], ['class' => 'sort_class'])</th>
        @can('change_status_building')   
        <th>@sortablelink('building_status','Status',[], ['class' => 'sort_class'])</th>
        @endcan
        <th>Action</th>
      </tr>

      <tr>
        <td></td>
        <td><input  type="text" name="building_name" class="search_fields " id="building_name" value="{{old('building_name')}}" ></td>
        <td><input  type="text" name="vendor__vendor_name" class="search_fields " id="vendor__vendor_name" value="{{old('vendor__vendor_name')}}" ></td>
        <td><input  type="text" name="management__management_types_name" class="search_fields " id="management__management_types_name" value="{{old('management__management_types_name')}}" ></td>
        @can('change_status_building')
        <td>
          <select name="building_status" class="searchFields search_fields mob" id="building_status" style="width:100px;">
            <option value="">Show All</option>
            <option {{ (old('building_status') != '')? ((old('building_status') ==  0)? 'selected':'') : ''}}   value="0">Inactive</option>
            <option {{(old('building_status') ==  1)? 'selected':''}}  value="1">Active</option>
          </select>
        </td>
        @endcan
        <td></td>
      </tr>
    </thead>
    <tbody  id="search">
     @include('masters::Building.list_ajax')
   </tbody>
 </table>
 <div id="pagination">
  {{-- @php
  $buildings->appends(['swipe'=>1]);
  @endphp --}}
  {{$buildings->appends(\Request::except(['page','_token']))->links()}}
</div>
</div>

<!-- card view starts-->
<div class="row are-building building-card2" id="building-card2">
  @forelse ($building_lists as $building_list)
  <div class="col-md-3">
    <div class="card">
      @if($building_list->buildingDefaultImage)<a href="{{route('building.show',$building_list->id)}}"><img src="{{asset('storage/app/'.$building_list->buildingDefaultImage->building_path_thumbnail)}}" alt="dd" style="width: 100%;"></a>
      @else
      <a href="{{route('building.show',$building_list->id)}}"><img src="{{asset('public/img/default-building.png')}}" alt="dd" style="width: 100%;"></a>
      @endif
      <div class="container">
        <div class="profile-usertitle">
         <header><b>Name : {{$building_list->building_name}}</b></header>
       </div>
       <ul class="list-group list-group-unbordered">
         <li class="list-group-item">
          <p>Building No : {{$building_list->building_no}} </p>
        </li>
        <li class="list-group-item">
          <p>Way No : @if(isset($building_list->building_pc))
            {{$building_list->building_pc}}
            @endif </p>


          </li>
          <li class="list-group-item">
            <p>Location :{{$building_list->buildingLocation->locations_name}}</p>
          </li>
          <li class="list-group-item">
            <p>Total No of Units :{{$building_list->unit->count()}}</p>
          </li>
        </ul>
        <div class="profile-userbuttons">
          <a href="{{route('building.show',$building_list->id)}}" class="btn btn-circle btn-primary">View</a>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-md-12">       
    <div class="card card-topline">
      <div class="card-body  height-9">
        <div class="profile-usertitle">
          <h4> No Records </h4>
        </div>
      </div>
    </div>   
  </div>
  @endforelse
  <div id="pagination">
    {{-- @php
    $buildings->appends(['swipe'=>2]);
    @endphp --}}
    {{-- {{$buildings->appends(\Request::except(['page','_token']))->links()}} --}}
  </div>
</div>

<!--Card View Ends -->


</div>
</div>
</div>
</div>

@endcan

<!--starts -->
@can('are_building_details')
<div class="row are-building">
  @forelse ($buildings as $building)
  <div class="col-md-3">
    <div class="card">
      @if($building->buildingDefaultImage)<a href="{{route('building.show',$building->id)}}"><img src="{{asset('storage/app/'.$building->buildingDefaultImage->building_path_thumbnail)}}" alt="dd" style="width: 100%;"></a>
       @else
      <a href="{{route('building.show',$building->id)}}"><img src="{{asset('public/img/default-building.png')}}" alt="dd" style="width: 100%;"></a>
      @endif
      <div class="container">
        <div class="profile-usertitle">
         <header><b>Name : {{$building->building_name}}</b></header>
       </div>
       <ul class="list-group list-group-unbordered">
         <li class="list-group-item">
          <p>Building No : {{$building->building_no}} </p>
        </li>
        <li class="list-group-item">
          <p>Way No : 
            {{ $building->building_pc ?? "NA"}} </p>


          </li>
          <li class="list-group-item">
            <p>Location :{{$building->buildingLocation->locations_name}}</p>
          </li>
          <li class="list-group-item">
            <p>Total No of Units :{{$building->unit->count()}}</p>
          </li>
          @if(\Auth::user()->hasRole('are_team_lead'))
          <li class="list-group-item">
            <p>Are :{{$building->buildingAssignToAre->buildingAssignToName->areUser->employee->employee_name ?? 'Not Assigned'}}</p>
          </li>
          @endif
        </ul>
        <div class="profile-userbuttons">
          <a href="{{route('building.show',$building->id)}}" class="btn btn-circle btn-primary">View</a>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-md-12">       
    <div class="card card-topline">
      <div class="card-body  height-9">
        <div class="profile-usertitle">
          <h4> No Records </h4>
        </div>
      </div>
    </div>   
  </div>
  @endforelse
</div>
<div id="pagination">
 {{$buildings->appends(\Request::except(['page','_token']))->links()}}
</div>

@endcan

<!-- ends-->
<input type="hidden" name="curr_url" id="curr_url" value="{{url()->current()}}">
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


  $(document).on('change keyup paste','.search_fields',function(){ 

   var building_name = $("#building_name").val();  
   var vendor__vendor_name = $("#vendor__vendor_name").val();  
   var management__management_types_name = $("#management__management_types_name").val();  
   var building_status = $("#building_status").val();  

   var curr_url = $("#curr_url").val();  



   $.ajax({
    method: "POST",
    url: "{{route('buildingFilter')}}",
    data: { 'building_name' : building_name, 'vendor__vendor_name' : vendor__vendor_name , 
    'management__management_types_name' : management__management_types_name,
    'building_status' : building_status,

    'curr_url' : curr_url,"_token" : $('meta[name="csrf-token"]').attr('content')},

    beforeSend: function(){
                // Show image container
                $('#search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");        
              },  


              success: function(data){  


               if(data != 0){

                var temp = $(data);
                var paginate_info = temp.find('.pagination_info').clone();
                temp.find('.pagination_info').remove();
                var paginate = temp.find('#pagination_ajax').clone();

                temp.find('#pagination_ajax').remove();
                $('#search').html(temp);
                $("#pagination" ).html(paginate);
                $( "#pagination_info" ).html(paginate_info);    
              //   $('.search_fields').trigger('blur');             
            }  

            var txt = 'building_name='+building_name+'&vendor__vendor_name='+vendor__vendor_name+'&management__management_types_name='+
            management__management_types_name+'&building_statu='+building_status+'&curr_url='+curr_url ;

            var txt_hashes = txt.split('&');
            var href_txt = '';
            for(var i = 0; i < txt_hashes.length; i++)
            {
              txt_hash = txt_hashes[i].split('=');

              if(txt_hash[1] !=  ''){                             
               href_txt =  (href_txt != '')? href_txt + '&': href_txt;

               href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
             }                       
           }          



           $('.sort_class').each(function (i, n) {
            var href = $(n).attr('href'); 
            var hashes =  href.slice(href.indexOf('sort'));
            href = href.split('?')[0];
            $(n).attr('href',href+'?'+href_txt+'&'+hashes);    
          });       
           
         } 

       });

 });
  /****************************** *******************************************/
  $("#swipe").change(function () {
    var swipe_val =   value = $("#swipe").val();
    if(value == 1){
      $("#building-card2").hide();
      $("#building-card1").show();  
      $(".page_info").show();   
    }else{
      $("#building-card1").hide();
      $(".page_info").hide();
      $("#building-card2").show();      
    }

    $('.page-link').each(function (i, n) {
      var href = $(n).attr('href'); 
      var  base_href = href.split('?')[0];   
         //   alert(base_href);

         if(href.search("swipe")) {

           base_href = href.split('?')[0];
           var href_param =  href.split('?')[1];

           var txt_hashes = href_param.split('&');
           var href_txt = '';
           for(var i = 0; i < txt_hashes.length; i++)
           {
            txt_hash = txt_hashes[i].split('=');

            if(txt_hash[1] !=  ''){        

             href_txt =  (href_txt != '')? href_txt + '&': href_txt;        

             if(txt_hash[0] == 'swipe'){
               href_txt =  href_txt + txt_hash[0]+'='+swipe_val;  //alert(href_txt);
             }
             else {              

              href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
            }
            
          }                       
        }

        
        $(n).attr('href',base_href+'?'+href_txt);  

      }
      else{
        $(n).attr('href',href+'&swipe='+swipe_val);    
      }
      
    });

  });
  var value = $("#swipe").val();
  if(value == 1){
    $("#building-card2").hide();
    $("#building-card1").show();
    
  }else{
    $("#building-card1").hide();
    $("#building-card2").show();     
  }
  $('.page-link').each(function (i, n) {
    var href = $(n).attr('href');           
    $(n).attr('href',href+'&swipe='+value);  
  });
});

</script> 
<!-- owl carousel -->
<script src="{{ asset('public/plugins/owl-carousel/owl.carousel.js')}}"></script>
<script src="{{ asset('public/plugins/owl-carousel/owl.carousel.min.js')}}"></script>
<script src="{{ asset('public/js/owl_data.js')}}"></script>

@endsection
