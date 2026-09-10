@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Tenant's Payment History - Agreement Wise (V2)</div>
    </div>
    {{ Breadcrumbs::render('showPaymentHistoryReportV2') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      @can('view_payment_history_v2') 
      <form action="{{route('paymentHistoryReportV2Download')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <div class="dataSearchBox ">

          <div class="row">
          

           <div class="col-sm-6">
              <div class="form-group">
                <label for="building_id">Building Name <small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="icon icon-building" aria-hidden="true"></i>
                 <input required type="text" class="form-control" id="building"  name="building_name"  placeholder="Enter Building Name">
                 <input type="hidden" class="form-control"  name="building_id" id="building_id">
                 <input type="hidden" class="form-control"  name="u_id" id="u_id">
                 <input type="hidden" class="form-control"  name="t_id" id="t_id">
               </div>
             </div>
           </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label for="unit_id">Unit <small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="icon icon-unit" aria-hidden="true"></i>
               <select id="unit_id" class="form-control unit_id" name="unit_id" style="display: none">
                 <option value="">Select Unit</option>
               </select>

               <input type="text" class="form-control" id="unit"  name="unit"   placeholder="Enter Unit Name">

               <input type="hidden" class="form-control" class="units_id" name="units_id" id="units_id">

             </div>
           </div>
         </div>
         <div class="col-sm-6">
          <div class="form-group">
            <label for="vendor_id">Tenant Name<small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="icon icon-tenant" aria-hidden="true"></i>

             <select id="tenant_id" class="form-control tenant_id" name="tenant_id" style="display: none">
                 <option value="">Select Tenant</option>
               </select>


             <input type="text" class="form-control" id="tenant"  name="tenant"  placeholder="Enter Tenant Name">

             <input type="hidden" class="form-control"  name="tenants_id" id="tenants_id">
           </div>
         </div>
       </div>

          <div class="col-sm-6">
              <div class="form-group">
                <label for="download_type">Download Type<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="download_type"  name="download_type" required>
                  <option value="">Select Download Type</option>                
                  <option value="pdf">PDF</option>                
                  <option value="excel">Excel</option>               
                </select> 
              </div>               
            </div>
          </div>
          <div class="w-100"></div>
          <div class="w-100"></div>
          <div class="col">
            <div class="w-100"></div>
            <button type="submit" name="search" class="btn btn-primary">Generate</button>
          </div>

        </div>

      </div>
      <div class="clearfix"></div>
    </form>
   @endcan  
  </div>
</div>
</div>
@endsection
@section('scripts')
<script>
  $( document ).ready(function() {
   //AutoComplete For Building Code
 /*********************************************************************************/ 
 $('#building_name').autocomplete({
  source : '{!!URL::route('rentBuildingAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#building_name").val('');
      $('#building_name-error').show();
    }
  }
});
  });
</script>

<script>
  $(document).ready(function() {

    $(document).on('change keyup paste','#building',function(){ 

      var building = $("#building").val();
      var unit = $("#unit").val();
      var tenant = $("#tenant_id").val();

      if(building==""){
        $("#agreementDetail").empty();
        $("#unit_id").hide();
        $("#tenant_id").hide();
        $("#unit").show();
        $("#unit").val("");
        $("#tenant").show();
        $("#tenant").val("");
      }else{
        $("#agreementDetail").show();
      }

    });
    $(document).on('change keyup paste','#unit',function(){ 

      var building = $("#building").val();
      var unit = $("#unit").val();
      var tenant = $("#tenant_id").val();

      if(unit == ""){
        $("#agreementDetail").empty();
        $("#tenant_id").hide();
        $("#building").val("");
        $("#tenant").show();
        $("#tenant").val("");
      }else{
        $("#agreementDetail").show();
      }

    });

    $(document).on('change keyup paste','#tenant',function(){ 

      var building = $("#building").val();
      var unit = $("#unit").val();
      var tenant = $("#tenant").val();

      if(tenant == ""){
        $("#agreementDetail").empty();
        $("#unit_id").hide();
        $("#building").val("");
        $("#unit").show();
        $("#unit").val("");
      }else{
        $("#agreementDetail").show();
      }

    });


    /***************************Building*********************************/
    $('#building').autocomplete({
      source : '{!!URL::route('allBuildingsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      select:function(e,ui){
        if(ui.item.ids != null){
          $('#building_id').val(ui.item.ids);
          var building_id = ui.item.ids;
          var building = $('#building').val();

          if(building_id != "" && building != ""){
            $('#unit').hide();
            
            $('#unit_id').show();
            $.ajax
            ({
              type: "POST",
              url: "{{route('allUnitsDetail')}}",
              data: {"building_id":building_id,"_token": "{{ csrf_token() }}"},
              cache: false,
              success: function(data)
              {
                var result = $.parseJSON(data);
                $("#agreementDetail").empty();
                //$("#tenant_id").val("");
                selected = "";
                if(result[0].length > 0){
                  if(result[0].length ==1){selected = "selected";}
                  $('#unit_id').empty();
                  $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                  $.each(result[0], function(key, value) {
                    $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_no'] +'</option>');
                  }); 
                  var unit_id = $('#unit_id').val();
                  if(building_id != "" && unit_id != ""){
                    $('#tenant').hide();
                    $('#tenant_id').show();
                    $.ajax
                    ({
                      type: "POST",
                      url: "{{route('allTenantsDetail')}}",
                      data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      success: function(data)
                      {
                        var result = $.parseJSON(data);
                        $("#agreementDetail").empty();
                        selected = "";
                        if(result[0].length > 0){
                          if(result[0].length ==1){selected = "selected";}
                          $('#tenant_id').empty();
                          $('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
                          $.each(result[0], function(key, value) {
                            $('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
                          });  

                          var tenant_id = $('#tenant_id').val();

                          if(building_id != "" && tenant_id != "" && unit_id != ""){

                            $.ajax
                            ({
                              type: "POST",
                              url: "{{route('contractDetails')}}",
                              data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                              cache: false,
                              success: function(data)
                              {
                                $("#agreementDetail").html(data);
                              } 
                            });
                          } 
                          

                        } 
                        
                      }
                    });

                  }          
                } else{
                  $('#unit_id').html('<option value="">No Available Units</option>');
                }
              }
            });

          } 

        }

      }
    });



    /***********************************UNit Change*******************************/
    $("#unit_id").on('change input',function(e){
      var building_id = $('#building_id').val();
      var unit_id = $("#unit_id").val();

      $("#u_id").val(unit_id);

      var tenant_id = $("#tenant_id").val();

      $("#t_id").val(tenant_id);

      if(building_id != "" && unit_id != ""){
        $('#tenant').hide();
        $('#tenant_id').show();
        $.ajax
        ({
          type: "POST",
          url: "{{route('allTenantsDetail')}}",
          data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
            var result = $.parseJSON(data);
            $("#agreementDetail").empty();
            selected = "";
            if(result[0].length > 0){
              if(result[0].length ==1){
                selected = "selected";
               // console.log("result grater than 0");
               // console.log(result[0][0].id);
               $("#t_id").val(result[0][0].id);
            }
              $('#tenant_id').empty();
              $('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
              $.each(result[0], function(key, value) {
                $('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
              });  
              if($('#tenant_id').val() == "")
              {
                var tenant_id = $('#tenants_id').val();
              }else{
                var tenant_id = $('#tenant_id').val();
              }
              if(building_id != "" && tenant_id != "" && unit_id != ""){


                
                $.ajax
                ({
                  type: "POST",
                  url: "{{route('contractDetails')}}",
                  data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                  cache: false,
                  success: function(data)
                  {
                    $("#agreementDetail").html(data);
                  } 
                });
              }          
            } else{
              $('#tenant_id').html('<option value="">No Available Tenants</option>');
              if(building_id != ""  && unit_id != ""){
                
                $.ajax
                ({
                  type: "POST",
                  url: "{{route('contractDetails')}}",
                  data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                  cache: false,
                  success: function(data)
                  {
                    $("#agreementDetail").html(data);
                  } 
                });
              } 
            }
          }
        });

      }
      if($('#tenant_id').val() == "")
      {
        var tenant_id = $('#tenants_id').val();
      }else{
        var tenant_id = $('#tenant_id').val();
      }
      if(tenant_id != "" && unit_id != ""){

        $.ajax
        ({
          type: "POST",
          url: "{{route('contractDetails')}}",
          data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
            $("#agreementDetail").html(data);
          } 
        });
        $.ajax
        ({
          type: "POST",
          url: "{{route('getBuildingCompleteDetails')}}",
          data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
            var result = $.parseJSON(data);
            $("#building").val(result[0].building_name);
            $("#building_id").val(result[0].id);
          }
        });
      }


    }); 

    /***********************************Tenant Change*******************************/
    $("#tenant_id").on('change input',function(e){
      var building_id = $('#building_id').val();

      if($("#unit_id").val() == "")
        var unit_id = $("#units_id").val();
      else
        var unit_id = $("#unit_id").val();

      if($("#tenant_id").val() == "")
        var tenant_id = $("#tenants_id").val();
      else
        var tenant_id = $("#tenant_id").val();

      $("#u_id").val(unit_id);
      $("#t_id").val(tenant_id);

     
      $('#tenant_id').show();
      $('#building_id').show();
      if(building_id != "" && tenant_id != "" && unit_id != ""){
        $.ajax
        ({
          type: "POST",
          url: "{{route('contractDetails')}}",
          data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
            $("#agreementDetail").html(data);
          } 
        });
      }          

    }); 


    /***************************UNit  autocomplete**********************************/
    $('#unit').autocomplete({
      source : '{!!URL::route('allUnitsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      select:function(e,ui){
        if(ui.item.ids != null){
          $('#units_id').val(ui.item.ids);
          $('#u_id').val(ui.item.ids);
          var unit_id = ui.item.ids;
          $('#tenant').hide();
          $('#tenant_id').show();
          if(unit_id){
            $.ajax
            ({
              type: "POST",
              url: "{{route('getBuildingDetails')}}",
              data: {"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
              cache: false,
              success: function(data)
              {
                var result = $.parseJSON(data);
                selected = "";
                if(result[0].length > 0){
                  if(result[0].length ==1){selected = "selected";}
                  $('#tenant_id').empty();
                  $('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
                  $.each(result[0], function(key, value) {
                    $('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
                  });             

                  $("#building").val(result[1].building_name);
                  $("#building_id").val(result[1].id);

                  var tenant_id = $('#tenant_id').val();
                  if(tenant_id != "" && unit_id != ""){

                    $.ajax
                    ({
                      type: "POST",
                      url: "{{route('contractDetails')}}",
                      data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      success: function(data)
                      {
                        $("#agreementDetail").html(data);
                      } 
                    });
                  }
                }else{
                  $("#building").val(result[1].building_name);
                  $("#building_id").val(result[1].id);
                  $('#tenant_id').html('<option value="">No Available Tenants</option>');

                  var building_id = $('#building_id').val();
                  if(tenant_id != "" && unit_id != ""){

                    $.ajax
                    ({
                      type: "POST",
                      url: "{{route('contractDetails')}}",
                      data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      success: function(data)
                      {
                        $("#agreementDetail").html(data);
                      } 
                    });
                  }
                } 
              }
            });

          }
        }

      }
    });

    /***************************Tenant  autocomplete**********************************/
    $('#tenant').autocomplete({
      source : '{!!URL::route('allTenantsAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      select:function(e,ui){
        if(ui.item.ids != null){
          $('#tenants_id').val(ui.item.ids);
          $('#t_id').val(ui.item.ids);
          var tenant_id = ui.item.ids;
          $('#unit').hide();
          $('#unit_id').show();
          if(tenant_id){
            $.ajax
            ({
              type: "POST",
              url: "{{route('getUnitDetails')}}",
              data: {"tenant_id":tenant_id,"_token": "{{ csrf_token() }}"},
              cache: false,
              success: function(data)
              {
                var result = $.parseJSON(data);
                selected = "";
                if(result[0].length > 0){
                  if(result[0].length ==1){selected = "selected";}
                  $('#unit_id').empty();
                  $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                  $.each(result[0], function(key, value) {
                    $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
                  });             
                } 
                

                $("#building").val(result[1].building_name);
                $("#building_id").val(result[1].id);

                var unit_id = $('#unit_id').val();
                if(tenant_id != "" && unit_id != ""){

                  $.ajax
                  ({
                    type: "POST",
                    url: "{{route('contractDetails')}}",
                    data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                    cache: false,
                    success: function(data)
                    {
                      $("#agreementDetail").html(data);
                    } 
                  });
                }  
              }
            });

          }
        }

      }
    });
    /**********************************************************************/
    /**********************************************************************/
  });


</script>

@endsection
