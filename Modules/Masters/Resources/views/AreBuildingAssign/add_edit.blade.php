@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
<style>
  #grid, #drop {
    border: 1px solid #e1dede;
    width: 42%;
    min-height: 20px;
    list-style-type: none;
    margin: 0;
    padding: 5px 0 0 0;
    float: left;
    min-height: 200px;
    /* margin-right: 10px;*/
    cursor:pointer;
    position: relative;
    overflow-x: hidden;
    max-height: 200px
  }
  #grid li, #drop li {

    padding: 5px 10px;
    font-size: 14px;
    width: 100%;
  }
  #grid li:hover, #drop li:hover {
    background-color: #32ab56;
    color: #fff;
  }
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
      <div class="page-title">Are Building Assign {{ (isset($areBuildingAssign))? 'Edit' : 'Add'}}</div>
    </div>  {{ (isset($areBuildingAssign))?   Breadcrumbs::render('areBuildingAssign.edit',$areBuildingAssign,Session::get('current')) :  Breadcrumbs::render('areBuildingAssign.create') }} 
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <form autocomplete="off"  action="{{isset($areBuildingAssign)? route( 'areBuildingAssign.update',$areBuildingAssign->id) : route( 'areBuildingAssign.store')}}" method="POST" id="form_sample_2" class="form-horizontal" >
        {{csrf_field()}} @if(isset($areBuildingAssign)){{method_field('PUT')}}@endif
        <input type="hidden" name="backurl" value="" >

        <div class="dataSearchBox ">

          <div class="row">

           <div class="col-sm-6">
            <div class="form-group">
              <label for="building_id">ARE<small class="textRed">*</small> </label>
              <div class="p-relative">
                <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" placeholder="Enter ARE Name" autocomplete="off"  required name="user_name" class="form-control user_name" id="user_name" value="{{ old('user_name', isset($areBuildingAssign)? $areBuildingAssign->areUser->employee->employee_name : '' )}}"  {{  isset($areBuildingAssign)?'disabled' : '' }}>

                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id', isset($areBuildingAssign)? $areBuildingAssign->areUser->id : '' )}}">  
              </div>               
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label for="building_prefix">From Date<small class="textRed">*</small> </label>
              <div class="p-relative">
                <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input required type="date" class="form-control" id="assign_from" name="assign_from" value="{{ old('assign_from', isset($areBuildingAssign)? $areBuildingAssign->assign_from->format('Y-m-d') : ''  )}}"  placeholder="Enter Start Date" >
              </div>

            </div>            
          </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="col no-padding" for="sales_unit_type_id">Building Name <small class="textRed">*</small></label>         

            @php  
            if(!empty($buildings))
            if(old('building_id')){

            if(!is_array(old('building_id')))
            $building = explode(',',old('building_id'));

            $source_building = $buildings->whereNotIn('id',old('building_id'));
            $destination_building = $buildings->whereIn('id',old('building_id'));

          }elseif(isset($areBuildingAssign)){           
          $buildings_val = $areBuildingAssign->buildingNames()->where('assign_to',null)->pluck('building_id');    
          $buildings_val = (array)array_flatten($buildings_val); 
          $source_building = $buildings->whereNotIn('id',$buildings_val);
          $destination_building = $buildingAll->whereIn('id',$buildings_val);
        }else{
        $source_building = $buildings; 
      }
      
      @endphp    

      <ul id="grid" class="connectedSortable">
       @foreach($source_building as $buildings)
       <li id="{{$buildings->id}}" class="list_item draggable">{{$buildings->building_name}}</li>
       @endforeach

     </ul> 
     <div class="sort_arrow-l ui-state-disabled">  
      <span><i class="fa fa-exchange" aria-hidden="true"></i> </span>      
    </div>
    <ul id="drop" class="connectedSortable">
     <li class="ui-state-disabled">(Drag And Drop Buildings)</li>
     @if(isset($destination_building))
     @foreach($destination_building as $buildings)
     <li id="{{$buildings->id}}" class="list_item draggable">{{$buildings->building_name}}</li>
     @endforeach
     @endif 

   </ul>
   <div class="clr"></div>
   <input type="hidden" value="{{ isset($destination_building)?  $destination_building->implode('id',',') : ''}}" required id="assign_building_id" name="building_id" class="buildingname">
 </div>
</div>
<div class="w-100"></div>

<div class="col">
  <div class="w-100"></div>
  <button type="submit" class="btn btn-primary">SAVE</button>
</div>

</div>

</div>
<div class="clearfix"></div>
</form>

</div>
</div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
//AutoComplete For ARE Name
/************************************************************/ 
$('#user_name').autocomplete({
  source : '{!!URL::route('areAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#user_name").val('');
      $('#create_build_span').hide();
      $('#user_name-error').show();
    }else {

      $('#user_id').val(ui.item.ids);       
      $('#create_build_span').show();       
    }

  }
});
/************************************************************/ 
$(".draggable").draggable({
  revert: "invalid"
})
.dblclick(function(){
  var myarr = [];
  if ($(this).parent().attr("id") != "drop") { 
   $(this).hide().appendTo("#drop").show('fast');
   $("#drop .list_item").each(function(){
    if($(this).attr('id'))

      myarr[myarr.length] = $(this).attr('id');       
  });
   $('.buildingname').val(myarr.toString());
   if($('.buildingname').val() != '')
    $('#assign_building_id-error').hide();
  else
   $('#assign_building_id-error').show();
} else {
 $(this).hide().appendTo("#grid").show('fast');
 $("#drop .list_item").each(function(){
  if($(this).attr('id'))

    myarr[myarr.length] = $(this).attr('id');       
});
 $('.buildingname').val(myarr.toString());
 if($('.buildingname').val() != '')
  $('#assign_building_id-error').hide();
else
 $('#assign_building_id-error').show();
}
});
$("#drop").droppable({
  accept: ".draggable",
  drop: function (event, ui) {
          //alert(1222);
          var myarr = [];
          console.log("drop");
          $(this).addClass("over");
          var dropped = ui.draggable;
          var droppedOn = $(this);

          $(dropped).detach().css({
            top: 0,
            left: 0
          }).appendTo(droppedOn);
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))

              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.buildingname').val(myarr.toString());
          if($('.buildingname').val() != '')
            $('#assign_building_id-error').hide();
          else
           $('#assign_building_id-error').show();

       },
       over: function (event, elem) {
          //alert(222);
          $(this).addClass("over");
          console.log("over");
          var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))

              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.buildingname').val(myarr.toString());
          if($('.buildingname').val() != '')
            $('#assign_building_id-error').hide();
          else
           $('#assign_building_id-error').show();

       },
       out: function (event, elem) {
          //alert(555);
          var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))
              //alert($(this).attr('id'));
            myarr[myarr.length] = $(this).attr('id');       
          });
          $('.buildingname').val(myarr.toString());
          if($('.buildingname').val() != '')
            $('#assign_building_id-error').hide();
          else
           $('#assign_building_id-error').show();
         $(this).removeClass("over");
       }
     });
$("#drop").sortable();

$("#grid").droppable({
  accept: ".draggable",
  drop: function (event, ui) {
          //alert(1111);
          
          if($('#drop img').length == 1)                 
            console.log("drop");
          $(this).removeClass("over");
          var dropped = ui.draggable;
          var droppedOn = $(this);
          $(dropped).detach().css({
            top: 0,
            left: 0
          }).appendTo(droppedOn);
          var myarr = [];
          $("#drop .list_item").each(function(){
            if($(this).attr('id'))
              myarr[myarr.length] = $(this).attr('id');       
          });
          $('.buildingname').val(myarr.toString());
          if($('.buildingname').val() != '')
            $('#assign_building_id-error').hide();
          else
           $('#assign_building_id-error').show();

         if ($(this).find('.draggable').length === 0) {
          $(this).append($(ui.draggable));

        }

      }
    });
/************************************************************/ 
$(document).ready(function(){
  var dtToday = new Date();

  var month = dtToday.getMonth() + 1;
  var day = dtToday.getDate();
  var year = dtToday.getFullYear();
  if(month < 10)
    month = '0' + month.toString();
  if(day < 10)
    day = '0' + day.toString();

  var maxDate = year + '-' + month + '-' + day;
  $('#assign_from').attr('max', maxDate);
});
/************************************************************/ 
</script> 
@endsection
