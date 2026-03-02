<script>  
/************************************************************/
  $(function() {
    $('#subcontractor').show(); 
    $('#technician').hide(); 
    $('#contract_type').change(function(){
      if($('#contract_type').val() == 'sub_contractor') {
        $('#subcontractor').show(); 
        $('#technician').hide(); 
      } else {
        $('#technician').show(); 
        $('#subcontractor').hide(); 
      } 
    });
  });
  /************************************************************/



//AutoComplete For Amc Contract No-Subcontractor
/************************************************************/ 
$('#amc_contract_no').autocomplete({
  source : '{!!URL::route('amcContractNoAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#amc_contract_no").val('');
      $('#create_build_span').hide();
      $('#amc_contract_no-error').show();
    }else {
      $('#amc_contract_num').val(ui.item.ids);         
      $('#building_id').val(ui.item.building);         
      $('#frequency').val(ui.item.frequency);         
      $('#amc_schedule_period_from').val(ui.item.from);       
      $('#amc_schedule_period_to').val(ui.item.to);         
      $('#create_build_span').show();  
      $.ajax({
        method: "POST",
        url: "{{route('amcDetailsByContractNo')}}",
        data: { 'contract_no': ui.item.ids, 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          var results = $.parseJSON(data);
          $('#building_name').val(results[1].building_name); 
          if(results[2]!=null){
            $('#vendor_name').val(results[2].vendor_name);
          }
          if(results[3]!=null){
            $('#payment_method_id').val(results[3].payment_method_code);
          }
          $(".read").attr('readonly',true);
          var status = results[0].status;
        }
      }); 
    
      //fetching units
      if(ui.item.building !=null){
        var buil = ui.item.building;
        var uselected = '';
        $.ajax({
          method: "POST",
          url: "{{route('unitByBuilding')}}",
          data: {"id":buil,"_token": "{{ csrf_token() }}"},
          cache: false,
          dataType: "json",
          success: function(data){
            if(data.length > 0){
              $('#unit_id').empty();
              $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
              $.each(data, function(key, value) {
                $('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
              });
            }
            else{
              $('#unit_id').html('<option value="">No Available Units</option>');
            }

          }
        });
      }   
      //fetching amenities
      $.ajax({
          method: "POST",
          url: "{{route('amenitiesByContractNo')}}",
          data: {"id":ui.item.ids,"_token": "{{ csrf_token() }}"},
          cache: false,
          dataType: "json",
          success: function(data){
            console.log(data);
            if(data.length > 0){
              $('#complaint_form').empty();
              var i=0;
              $.each(data, function(key, value) {
                i=i+1;
                  $('#complaint_form').append('<tr><td>'+ i +'</td><td>'+value['amenity_type']["amentity_types_name"]+'</td><td>'+value["amenities_type_id"]+'</td></tr>'); 
                  $('#amenities_type_id').val(value["amenities_type_id"]);    
              });
            }
            else{
              $("#complaint_form").html('<tr><td><p>No Records</p></td></tr>');
            }

          }
        }); 
    }

  }
}); 
//building auotocomplete-Technician
/**************************************************************************************/

$('#building_text').autocomplete({
  source : '{!!URL::route('amcBuildingAutocomplete')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if(ui.item.ids !=null){
      $('#building_text_id').val(ui.item.ids);
      var buil = ui.item.ids;
      var uselected = '';
      //unit selecting
      $.ajax({
        method: "POST",
        url: "{{route('unitByBuilding')}}",
        data: {"id":buil,"_token": "{{ csrf_token() }}"},
        cache: false,
        dataType: "json",
        success: function(data){
          if(data.length > 0){
            $('#unit_text_id').empty();
            $('#unit_text_id').append('<option value="">'+ 'Select Unit' +'</option>')
            $.each(data, function(key, value) {
              $('#unit_text_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
            });
          }
          else{
            $('#unit_text_id').html('<option value="">No Available Units</option>');
          }

        }
      });
      //amenity selecting
      $.ajax({
       type: "GET",
       url: "{!!URL::route('getBuildingAmenity')!!}",
       data:'building_id='+ ui.item.ids,
       success: function(data){
        $("#amentity_types_id").html(data);
      }
    }); 

    }

  }
});
/***************************************************************************************/
//adding amenity
$(document).on('click','.add_amenity',function(){ 
  var amentity_types_id = $("#amentity_types_id").val();
  var no = $('#complaint_form tr').length+1;
  if(amentity_types_id !=""){
    $.ajax({
      method: "POST",
      url: "{{route('addAmcAmenity')}}",
      data: {no:no,amentity_types_id:amentity_types_id, "_token" : $('meta[name="csrf-token"]').attr('content')},
      success: function(data){                            
        if(data != 0){
          $('#amenity_form').append(data);                           
        }               
      }           
    });
  }else{
    alert("Please Select Amenity");
  }             
});
/***************************************************************************/
//date range
  $('.date_range').change(function(){
        var d1 = $('#amc_schedule_period_from_text').val();
        var d2 = $('#amc_schedule_period_to_text').val();

        var date1 = new Date(d1);
        var date2 = new Date(d2);

        var date1_ms = date1.getTime();
        var date2_ms = date2.getTime();

        var diff = date2_ms-date1_ms;

          // get days
        var days = diff/1000/60/60/24;
        var no_days=days + 1;
        $('#no_days').val(no_days); 
        if(no_days>=30)
        {
           $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
        }
        if(no_days>=90)
        {
           $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
           $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
        }
        if(no_days>=180)
        {
           $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
           $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
           $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
        } 
        if(no_days>=360)
        {
           $('#payment_method_id_text').append('<option value="6">'+ 'Monthly' +'</option>')
           $('#payment_method_id_text').append('<option value="1">'+ 'Quarterly' +'</option>')
           $('#payment_method_id_text').append('<option value="2">'+ 'Half-yearly' +'</option>')
           $('#payment_method_id_text').append('<option value="7">'+ 'Yearly' +'</option>')
        }
        if(no_days<30)
        {
          alert('No Of days must be atleast 30 days !');
        }
     

  });

/***************************************************************************/
  $('#payment_method_id_text').change(function(){
     var sel=  $('#payment_method_id_text option:selected').html();
     $('#frequency_type').val(sel);   
    });
/***************************************************************************/
//PROCESS
$(document).on('click','.process_amenity',function(){ 
  var amentity_types_id = $("#amentity_types_id").val();
  var amc_schedule_period_from_text = $("#amc_schedule_period_from_text").val();
  var amc_schedule_period_to_text = $("#amc_schedule_period_to_text").val();
  var frequency_type = $("#frequency_type").val();
  var no_days = $("#no_days").val();
  var no = $('#process_form tr').length+1;
  if(amentity_types_id !=""){
    $.ajax({
      method: "POST",
      url: "{{route('processAmcAmenity')}}",
      data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
      success: function(data){                            
        if(data != 0){
          $('#process_form').append(data);                           
        }               
      }           
    });
  }else{
    alert("Please Select Amenity");
  }             
});
/***************************************************************************/
    $(document).on('click','.AmenityEdit',function(){

        var amentity_types_id = $(this).attr('data-id');
        var amc_schedule_period_from = $(this).attr('data-frm');
        var amc_schedule_period_to = $(this).attr('data-to');
        var no = $(this).attr('data-idNo');
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
            url: "{{route('amenityEdit')}}",
            data: {'amentity_types_id' : amentity_types_id,'amc_schedule_period_from' : amc_schedule_period_from, 'amc_schedule_period_to' : amc_schedule_period_to,'no' : no,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    /***************************************************************************/
$(document).on('click','.update_amenity',function(){ 
   var amentity_types_id = $("#amentity_types_id").val();
   var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
  var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
  var no = $('#No').val();
  if(amentity_types_id !=""){
    $.ajax({
        method: "POST",
        url: "{{route('updateScheduleAmenity')}}",
        data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
           //and there for the td elements
          $("#amentity_types_id"+no).html(data['amentity_types_name']+'<input type="hidden" name="amentity_types_id[]" value="'+data['amentity_types_id']+'">');

          $("#amc_schedule_period_from_text"+no).html(data['amc_schedule_period_from_text']+'<input type="hidden" name="amc_schedule_period_from_text[]" value="'+data['amc_schedule_period_from_text']+'">');

          $("#amc_schedule_period_to_text"+no).html(data['amc_schedule_period_to_text']+'<input type="hidden" name="amc_schedule_period_to_text[]" value="'+data['amc_schedule_period_to_text']+'">');

          $("#amc_schedule_status"+no).html(' <button type="button" class="btn label label-primary label-mini">OPEN</button>');



         


          $("#action"+no).html('<button class="btn btn-tbl-delete btn-xs remove_amenity" type="button"><i class="fa fa-trash-o "></i></button> <button type="button" class="btn btn-tbl-edit btn-xs AmenityEdit" data-toggle="modal" data-target="#myModal" data-id = "'+data['amentity_types_id']+'" data-frm = "'+data['amc_schedule_period_from_text']+'" data-to = "'+data['amc_schedule_period_to_text']+'" data-idNo="'+data['no']+'"><i class="fa fa-pencil-square-o"></i></button>');
          
          //$('tbody#complaint_form tr#'+no).html(data);
          $('#myModal').modal('toggle');                    
                           
        }       
    });
  }else{
    alert("Please Select Category");
}             
});

//remove amenity
/***************************************************************************/
    $(document).on('click','.remove_amenity',function(){
      var row = $(this).closest('tr').attr('id'); // Or continue to use the invalid ID selector: '#'+id
     
      var siblings =  $(this).closest('td').siblings('td.selected').text();
      $(this).closest('tbody .tr').remove();
      
        
        //$(this).closest('tbody .tr').remove();
        
    });

/***************************************************************************/
//add new schedule
    $(document).on('click','.AddTask',function(){
        var building_text_id = $("#building_text_id").val();
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
            url: "{{route('addTask')}}",
            data: {'building_text_id' : building_text_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    /***************************************************************************/
    //PROCESS add amenity
$(document).on('click','.process_addamenity',function(){ 
  var amentity_types_id = $("#amentity_types_idd").val();
  var amc_schedule_period_from_text = $("#amc_schedule_period_from_textt").val();
  var amc_schedule_period_to_text = $("#amc_schedule_period_to_textt").val();
  var frequency_type = $("#frequency_type").val();
  var no_days = $("#no_days").val();
  var no = $('#process_form tr').length+1;
  if(amentity_types_id !=""){
    $.ajax({
      method: "POST",
      url: "{{route('processAmcAmenity')}}",
      data: {no:no,amentity_types_id:amentity_types_id,amc_schedule_period_from_text:amc_schedule_period_from_text,amc_schedule_period_to_text:amc_schedule_period_to_text,frequency_type:frequency_type,no_days:no_days, "_token" : $('meta[name="csrf-token"]').attr('content')},
      success: function(data){                            
        if(data != 0){
          $('#process_form').append(data);                           
        }               
      }           
    });
  }else{
    alert("Please Select Amenity");
  }             
});
</script>
