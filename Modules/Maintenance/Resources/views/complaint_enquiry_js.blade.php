<script>
  $(document).ready(function() {

     $(".AccountSelectBox input:checkbox").change(function () {
 if(this.checked)  
   $(this).closest("tr").find('.AccountAmountBox').removeAttr("disabled");
 else
     $(this).closest("tr").find('.AccountAmountBox').prop("disabled",true);
              
            });

    /*********************************************************************/


    $(".read").attr('readonly',true);
    // Complaint date validation with past One Month
    /*********************************************************************/
    var dtToday = new Date();
     
    var oneMonth = dtToday.setMonth(dtToday.getMonth() - 1);
   
    var minDate = formatDate(oneMonth);
    var maxDate = formatDate(new Date());
    
    /*************************************************************************/
    $('#complaint_date').attr('min', minDate);
    $('#complaint_date').attr('max', maxDate);
    
    $("#complaint_date").addClass("complaint_date");

    $.validator.addMethod("dateRange", function(value, element, params) {
      try {
        var date = new Date(value);
        if (date >= params.from && date <= params.to) {
          return true;
        }
      } catch (e) {}
      return false;
    }, 'Today And One Month Back Only Allowed');

    $.validator.addClassRules({
      complaint_date: {
        dateRange: {
          from: new Date(minDate),
          to: new Date(maxDate)
        }
      }
    });

    /**************************************************************************************/
    @if(isset($complaintEnquiry)){
          $("#maintenance-form").validate({
            rules: {
          registerd_mob_no: {
             required:'#cmp_type_1:checked'
          }, 
          unit_text: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3' && $(".unit_text").css('display') != 'none'  &&  $("#unit_text").val() != '' ){
                        return true;
                    } 
                }
          },unit_id: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3' && $(".unit_select").css('display') != 'none'  &&  $("#unit_id").val() != '' ){
                        return true;
                    } 
                }
          },        
          unit: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3'){                     
                        return true;
                    } 
                }
          },
          tenant_name: {
             required: function (element) {
                    if($(".cmp_type").val() ==  '1'){
                        return true;
                    } 
                }
          },
        },

      submitHandler: function(form) {
        var worksIds=[];
        $(".worksIds").each(function(){
          worksIds.push($(this).val());
        });
        var uniqOb = {};
        /* create object attribute with name=value in array, this will not keep dupes*/
        for (var i in worksIds)
          uniqOb[worksIds[i]] = "";
        /* if object's attributes match array, then no dupes! */
        if (worksIds.length == Object.keys(uniqOb).length)
          return true;
        else
          alert('No Duplication allowed for Ticket !!');
        return false;
      }
    });
    }
    @else{
      $("#maintenance-form").validate({
       // ignore: [],
        rules: {
          registerd_mob_no: {
             required:'#cmp_type_1:checked'
          },
          // resident_card_id: {
          //    required:'#cmp_type_1:checked'
          // }, 
          unit_text: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3' && $(".unit_text").css('display') != 'none'  &&  $("#unit_text").val() != '' ){
                        return true;
                    } 
                }
          },unit_id: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3' && $(".unit_select").css('display') != 'none'  &&  $("#unit_id").val() != '' ){
                        return true;
                    } 
                }
          },unit: {
             required: function (element) {
                    if($(".cmp_type").val() !=  '3'){                     
                        return true;
                    } 
                }
          },
          tenant_name: {
             required: function (element) {
                    if($(".cmp_type").val() ==  '1'){
                        return true;
                    } 
                }
          },
        },
      submitHandler: function(form) {
        var works_id=[];

        var tstatus = $("#tenantStatus").val();
         if(tstatus == 2){
           alert("Cannot Accept Complaint Due To ON HOLD Status ");
           return false;
         }

        $(".group_ctrl:checked").each(function(){
          works_id.push($(this).val());
        });
        if(works_id.length == 0)
          {
          alert('Cannot Accept Complaint Without Tickets !');
          return false;
        }

       $('.save_compliant').prop('disabled', true);
       form.submit();
      }
    });
    }
    @endif
    

    /**************************************************************************************/
    /*$("#maintenance-form").validate({

      submitHandler: function(form) {
       var rowCount = $('#complaint_form tr').length;
       var tstatus = $("#tenantStatus").val();

       if(tstatus == 2){
         alert("Cannot Accept Complaint Due To ON HOLD Status ");
         return false;
       }
       if(rowCount < 1){
         alert("Cannot Accept Complaint Without Tickets ");
         return false;
       }
       $('.save_compliant').prop('disabled', true);
       form.submit();
     },
   });*/

   /**************************************************************************************/

var default_cmp_type = $('.cmp_type:checked').val();
complaintType(default_cmp_type);

$('.cmp_type').on('click',function(e){ 
    var cmp_type = $(this).val();
  
    if(default_cmp_type !=  cmp_type){
      default_cmp_type = cmp_type;

      resetForm();
    } 
      

    complaintType(cmp_type);
});

function complaintType(cmp_type){
  switch(cmp_type){

    case '1' :  $('.occupied').show();
                 if($('#unit_text').css('display') == 'none')
                    {
                      $('.unit_select').show();
                    }else{
                      $('.vacant').show();
                    } 
              break;
    case '2' :   $('.occupied').hide(); 
                   if($('#unit_text').css('display') == 'none')
                    {
                      $('.unit_select').show();
                    }else{
                      $('.vacant').show();
                    }             
               break;
    case '3' :  $('.occupied,.vacant,.unit_select').hide();   
              break;           

   } 
}



///Building Autocomplete 

 $('#building_text').autocomplete({
   source: function (request, response) {
   var cmp_type = $('.cmp_type:checked').val();
        $.ajax({
            method: 'POST',
            url: "{{route('combinationSearchMaintenance')}}",
            data: { fieldName : 'building_id', fieldValue :request.term , cmp_type : cmp_type , _token :  $('meta[name="csrf-token"]').attr('content')   },
            success: function (data) {
                var transformed = $.map(data, function (el) {
                    return {
                        label: el.building_name + '-'+el.building_code,
                        ids: el.id,
                        way_no: el.building_pc,
                        location_name: el.location.locations_name,
                        location_id: el.location.id,
                        building_maintenance_info: el.building_maintenance_info,
                    };
                });
                response(transformed);
            },
            error: function () {
                $('#unit_id').empty();
                $('.unit_text').show();
                $('.unit_select').hide();

                response([]);
            }
        });
    },
    minlenght:0,
    autoFocus:true,    
    change:function(e,ui){
		
        if(ui.item == null){
              resetForm();
              return true;
        }

       if(ui.item.ids !=null){     
   
          var building_id = ui.item.ids;
          var cmp_type = $('.cmp_type:checked').val(); 
          $('#buildings').val(ui.item.ids);
		  
          if(cmp_type != '3'){
			  
                $.ajax({
                  method: "POST",
                  url: "{{route('combinationSearchMaintenance')}}",
                  data: { fieldName : 'unit_id', searchByBuilding : true,  fieldValue :building_id ,cmp_type : cmp_type, "_token": "{{ csrf_token() }}"},
                  cache: false,
                  success: function(data)
                    {  
                   //$('#tenant_location_id').val(data.tenantContract.building.location_id);
               $('#unit_id').empty();
               $('.unit_text').hide();
               $('.unit_select').show();  
               $('#unit').val('');  
               selected = '';   
			   
               $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
               if(data.units.length ==1) selected = "selected";
                $.each(data.units, function(key, value) {
                  $('#unit_id').append('<option value="'+ value.id +'" '+ selected +'>'+ value.unit_no +'</option>');

					if(data.units.length ==1){
						$('#unit').val(value.id);
						$( "#unit_id" ).trigger( "change" );
					}
                });
                    
                    }
                });
          }

          $('#way_no').val(ui.item.way_no);
          $('#location_name').val(ui.item.location_name);
          $('#tenant_location_id').val(ui.item.location_id);
          buildingStatus(ui.item.building_maintenance_info);
       //   $(".read").attr('readonly',true);
           $("#maintenance-form").validate();
       }else{
      $('#unit_id').empty();
      $('#unit_text').val('');
      $('.unit_text').show();
      $('.unit_select').hide();
       }

    }

 })



///Unit Autocomplete 
$('#unit_text').autocomplete({   
   source: function (request, response) {
   var cmp_type = $('.cmp_type:checked').val();
        $.ajax({
            method: 'POST',
            url: "{{route('combinationSearchMaintenance')}}",
            data: { fieldName : 'unit_id', fieldValue :request.term , cmp_type : cmp_type , _token :  $('meta[name="csrf-token"]').attr('content')   },
            success: function (data) {
                var transformed = $.map(data.units, function (el) { 
  
                  if(cmp_type == '1'){

                    unit_parm = {
                        label: el.unit_code,
                        ids: el.id,
                        building_id: el.building.id,
                        building_name: el.building.building_name+ '-'+el.building.building_code,
                        way_no: el.building.building_pc,
                        location_name: el.building.location.locations_name,
                        location_id: el.building.location.id,
                        tenant_name: el.tenant_contract.tenant.tenant_name,
                        tenant_id: el.tenant_contract.tenant.id,
                        tenant_contact_no: el.tenant_contract.tenant.tenant_contact_no,
                        resident_id: el.tenant_contract.tenant.resident_id,
                        
                        tenant_contract_status: el.tenant_contract.status,
                        tenant_status: el.tenant_contract.tenant.status,

                       }; 

                   if(el.tenant_contract.occupant != null){
                       unit_parm.occupant_name =  el.tenant_contract.occupant.occupant_name;
                       unit_parm.occupant_id = el.tenant_contract.occupant.id;
                   }
                   return unit_parm;  

                  }
                  if(cmp_type == '2'){
                     return {
                        label: el.unit_code,
                        ids: el.id,
                        building_id: el.building.id,
                        building_name: el.building.building_name+ '-'+el.building.building_code,
                        way_no: el.building.building_pc,
                        location_name: el.building.location.locations_name,
                        location_id: el.building.location.id,
                         }; 
                  }
                });
                response(transformed);
            },
            error: function () {
                response([]);
            }
        });
    },
    minlenght:2,
    autoFocus:true,
     change:function(e,ui){

       if(ui.item == null){
              resetForm();
              return true;
        }

       if(ui.item.ids !=null){  
           var cmp_type = $('.cmp_type:checked').val(); 
           var unit_id = ui.item.ids;

          $('#unit').val(unit_id);
          $('#way_no').val(ui.item.way_no);
          $('#location_name').val(ui.item.location_name);         
          $('#building_text').val(ui.item.building_name);
          $('#buildings').val(ui.item.building_id);
          $('#tenant_location_id').val(ui.item.location_id);

          if(cmp_type == '1'){ 
          $('#tenant_name,#complainer_name').val(ui.item.tenant_name); 
          $('#resident_card_id').val(ui.item.resident_id);
          $('#registerd_mob_no,#complaint_mob_no').val(ui.item.tenant_contact_no);          
          $('#tenant_id').val(ui.item.tenant_id);
          $('#occupant_name').empty();
          $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
          if(ui.item.occupant_id){
          $('#occupant_name').append('<option value="'+ ui.item.occupant_id +'"  selected >'+ ui.item.occupant_name +'</option>');
		  }else{
            $('#occupant_name').append('<option value="'+ ui.item.occupant_id +'"  selected >'+ ui.item.tenant_name +'</option>');
          }
          buildingTenantStatus(ui.item.tenant_contract_status,ui.item.tenant_status);

           $("#maintenance-form").validate();
           
          } 

       }
     }

  })



//////////  Combination Search  /////////

  $('.cmp_comp_search').on('input change keyup',function(e){
   //  registerd_mob_no  ,   resident_card_id
   var id = $(this).attr('id');
   var value = $(this).val();

   if(value == ''){
     if('unit_id' ==  id)
      $('#unit').val(value);

     if('building_id' ==  id){
      $('#unit_id').empty();
      $('.unit_text').show();
      $('.unit_select').hide();
    }

    return true;
   }
   var tenant =  $.inArray( id, ['registerd_mob_no','resident_card_id']);      
       if(tenant > -1 ){
           
            if( id == 'registerd_mob_no'){  

             var data = {'fieldName': 'tenant_contact_no', 'fieldValue' :value }; 
              if(value.length  >  7){
               combinationSearch(data);
              }
            }else{
              var data = {'fieldName': 'resident_id',  'fieldValue' : value };   
              combinationSearch(data);
            }

    
   }else if('building_id' ==  id){
       var data = {'fieldName': 'unit_id','fieldValue' : value,  'searchByBuilding': true}; 
       if($('#registerd_mob_no').val() != ''  || $('#resident_card_id').val()){
         
          if($('#registerd_mob_no').val() != '')
          data.registerd_mob_no = $('#registerd_mob_no').val();
       
          if($('#resident_card_id').val() != '')
          data.resident_card_id = $('#resident_card_id').val();

      }  
       combinationSearch(data);
    }else if('unit_id' ==  id){
      $('#unit').val(value);
      var data = {'fieldName': 'unit_id_search','fieldValue' : value};         
      combinationSearch(data);

    }
 

  });

  function buildingStatus(status){
     if(status != null){ 
		
    switch(status){
      case 0:
          //$("#tenantStatus").val(0); 
          $(".tenantStatus").hide();
          break;
      case 1:
          $(".tenantStatus").show();
          $("#tenantStatus").val(1); 
          $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
         break;
    }
   }
  
	return ;
  }


  function buildingTenantStatus(status,tenantStatus){
    
     if(status != null){
                switch(status){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 2:
                  $("#tenantStatus").val(2);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                  break;
                  case 3: 
                  $("#tenantStatus").val(3);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                  break;
                  case 4: 
                  $("#tenantStatus").val(4);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                  break;
                  case 5: 
                  $("#tenantStatus").val(5);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                  break;
                }
              }else{
                switch(tenantStatus){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 6:
                  $("#tenantStatus").val(6);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                  break;
                }
              }  

  }


  function resetForm(fieldname = null){
              $('#tenant_name').val('');
              @if(!isset($complaintEnquiry))
              $('#complainer_name').val('');             
              $('#complaint_mob_no').val("");
              @endif
               $('#way_no').val('');
              $('#location_name').val("");                               
              $('#tenant_location_id').val(""); 
              $('#building_text_id').val("");
              $('#building_text').val(""); 
              $('#unit_id').empty(); 
              $('#building_id').empty();
              $('#buildings').val("");
              $('#occupant_name').empty();
              $('#tenant_name').val(""); 

              if(fieldname != 'resident_id')
              $('#resident_card_id').val(""); 

              if(fieldname != 'tenant_contact_no')
              $('#registerd_mob_no').val("00968");
              
              
               var validator = $( "#maintenance-form" ).validate();
               
                if(fieldname == 'tenant_contact_no'){ 
                validator.showErrors({
                  "registerd_mob_no": "It's Not A Registered Mobile Number,Please Try Again!"
                });
                }
               if(fieldname == 'resident_id'){ 
                 validator.showErrors({
                  "resident_card_id": "It's Not A Registered Resident Id,Please Try Again!"
                });
                  }
                $('#complaint_mob_no').val("");
                $('#tenant_id').val("");

                $('.building_text').show();
                $('.building_select').hide(); 

                $('#unit_text').val('');
                $('#unit_text_id').val('');
                $('.unit_text').show();
                $('.unit_select').hide();

                $(".tenantStatus").hide();
                $("#tenantStatus").val('');


//var validator = $("#maintenance-form").validate();
//validator.resetForm();  
  }


var progress = null;

  function combinationSearch(para_data){
  
   para_data._token =  $('meta[name="csrf-token"]').attr('content');
   para_data.cmp_type =  $('.cmp_type:checked').val();

   var fieldname = para_data.fieldName;
   var searchByBuilding = false;
   if(para_data.searchByBuilding)
   searchByBuilding =  para_data.searchByBuilding;     

   

    progress =     $.ajax({
          method: "POST",
          url: "{{route('combinationSearchMaintenance')}}",
          data: para_data ,
          beforeSend : function() {
                //checking progress status and aborting pending request if any
                if(progress != null) {
                    progress.abort();
                }
            },
            complete: function(){
                // after ajax xomplets progress set to null
                progress = null;
            },
            success: function(data){ 

          if(data == ''){
             resetForm(fieldname)
             return true;
          }    
       
          selected = '';           

          if(fieldname != 'unit_id'  && fieldname !=  'unit_id_search'){

             $('#unit_id').empty();
             $('#building_id').empty();

              $('#resident_card_id').val(data.tenant.resident_id);
              $('#registerd_mob_no,#complaint_mob_no').val(data.tenant.tenant_contact_no);
              $('#tenant_name,#complainer_name').val(data.tenant.tenant_name);        
              $('#tenant_id').val(data.tenant.id);  
              

              $('.building_text').hide();
              $('.building_select').show();   
              $('#building_id').append('<option value="">'+ 'Select Building' +'</option>')  
              if(data.building.length ==1) selected = "selected";

              $.each(data.building, function( index, value ) {
                $('#building_id').append('<option value="'+ value.id +'" '+ selected +'>'+ value.building_name  + '-'+ value.building_code +'</option>'); 

                   if(data.building.length ==1){
                  $('#buildings').val(value.id);
                  buildingStatus(data.building.building_maintenance_info);
                  $('#building_id').trigger('input'); 
                  } 
              });


          }else{
            if(fieldname == 'unit_id'){
                $('#unit_id').empty();
                $('.unit_text').hide();
                $('.unit_select').show();  
                
                if(data.units.length ==1) selected = "selected";

                $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                $.each(data.units, function(key, value) {
                  $('#unit_id').append('<option value="'+ value.id +'" '+ selected +'>'+ value.unit_code +'</option>');
           
            if(data.units.length ==1){
                $('#unit').val(value.id);
 
                $('#way_no').val(value.building.building_pc);
                $('#location_name').val(value.building.location.locations_name);
                $('#tenant_location_id').val(value.building.location_id);
                $(".read").attr('readonly',true);
                }

                });
            }else if(fieldname == 'unit_id_search'){            
              
              $('#resident_card_id').val(data.tenantContract.tenant.resident_id);
              $('#registerd_mob_no,#complaint_mob_no').val(data.tenantContract.tenant.tenant_contact_no);
              $('#tenant_name,#complainer_name').val(data.tenantContract.tenant.tenant_name);        
              $('#tenant_id').val(data.tenantContract.tenant.id);  


              $('#occupant_name').empty();
              $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')

              if(data.tenantContract.occupant != null)               
              $('#occupant_name').append('<option value="'+ data.tenantContract.occupant.id +'" '+ selected +'>'+ data.tenantContract.occupant.occupant_name +'</option>');               
              else
              $('#occupant_name').append('<option value="'+data.tenantContract.tenant.id +'" '+ selected +'>'+ data.tenantContract.tenant.tenant_name +'</option>');                
                             

            $('#way_no').val(data.tenantContract.building.building_pc);
            $('#location_name').val(data.tenantContract.building.location.locations_name);
            $('#tenant_location_id').val(data.tenantContract.building.location_id);

            $(".read").attr('readonly',true);
            buildingTenantStatus(data.tenantContract.status,data.tenantContract.tenant.status);
              
            }

          }
          $("#maintenance-form").validate()
            }
      });

  }


   
/***************************************************************************************/
$("#building_id").on('change',function(e){
  var building_id = $('#building_id').val();
  $('#buildings').val(building_id);
});
/***************************************************************************************/

/***************************************************************************************/

/*************************************************************************/
$(document).on('change',".year", function(){
  var year = $('#year').val();
  var monthArray = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
  var today = new Date();
  var fullyear = today.getFullYear();

  if(year == fullyear){
    today.setMonth(today.getMonth() - 1);
    var cur_month = today.getMonth() + 1;
    var i;
    $('#month').empty();
    $('#month').append('<option value="">'+ 'Select Month' +'</option>')
    for (i=cur_month; i<12; i++) {

      var optn = document.createElement("OPTION");
      optn.text = monthArray[i];
      optn.value = (i+1);
      document.getElementById('month').options.add(optn);
    }
  }else{
    var i;
    $('#month').empty();
    $('#month').append('<option value="">'+ 'Select Month' +'</option>')
    for (i=0; i<12; i++) {

      var optn = document.createElement("OPTION");
      optn.text = monthArray[i];
      optn.value = (i+1);
      document.getElementById('month').options.add(optn);
    }
  }
});
/*************************************************************************/
/*$("#occupant_no").on('change keyup input',function(e){

  var occupant_no = $('#occupant_no').val();
  var mobile_size = occupant_no.length;

  if(mobile_size  >  7 ){
    $.ajax({
      method: "POST",
      url: "{{route('contractSearchByOccupant')}}",
      data: { occupant_no : occupant_no , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){

            //alert(result[1].tenant_contract_no);                           
            if(data != 0){
              var result = $.parseJSON(data);
              //$('#occupant_name').val(result[0].occupant_name); 
              $('#occupant_name').append('<option value="'+ result[0].id +'" "selected">'+ result[0].occupant_name +'</option>');
              
            }else{                               
              $('#occupant_name').val('');                              
              
            }             
          }           
        });
  }

});*/
/*************************************************************************/
/*
$("#occupant_name").on('change',function(e){

  var occupant_id = $('#occupant_name').val();

  if(occupant_id != "") {
    $.ajax({
      method: "POST",
      url: "{{route('contractSearchByOccupant')}}",
      data: { occupant_id : occupant_id , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){

          if(data != 0){
            var result = $.parseJSON(data);
            //$('#occupant_name').val(result[0].occupant_name); 
            $('#occupant_no').val(result[0].occupant_primary_contact_no);
            $('#occupant_id').val(result[0].id);
            
          }else{                               
            $('#occupant_no').val('');  
            $('#occupant_id').val('');                              
            
          }             
        }           
      });
  }
  

});*/
/*************************************************************************/
/*
$(document).on('change',".Building", function(){
        //alert(55);
        var id = $("#building_id").val();
        var tenant = $("#tenant_id").val();
        $('#buildings').val(id);
        var selected = '';
        $.ajax({
          type: "POST",
          /*url: "{{url('/tenantContract/buildingByUnitOccuiped')}}",
          data: {"id":id,"_token": "{{ csrf_token() }}"},*/
       /*   url: "{{route('complaintBuildingByUnitOccuiped')}}",
          data: {"id":id,"tenant":tenant,"_token": "{{ csrf_token() }}"},
          cache: false,
          //dataType: "json",
          success: function(data)
          {
            var result = $.parseJSON(data);
            if(result[0].length > 0){
              if(result[0].length ==1){selected = "selected";}
              $('#unit_id').empty();
              $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
              $.each(result[0], function(key, value) {
                $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
              });
            }
            else{
              $('#unit_id').html('<option value="">No Data</option>');
            }
            $('#way_no').val(result[2].building_address);
            $('#tenant_location_id').val(result[2].location_id);
            $('#location_name').val(result[1].locations_name);
            var buil_status = result[2].building_maintenance_info;
            if(result[3].length > 0){
				$.each(result[3], function(key, value) {
                $('#occupant_name').html('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
                $('#occupant_id').val(value['id']);
              });
			}
            else{
				$('#occupant_name').empty().html('<option value="">No Occupant</option>');
				$('#occupant_id').val('');
				
			}
            if(buil_status != null){
              switch(buil_status){
                case 0:
                  //$("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 1:
                  $(".tenantStatus").show();
                  $("#tenantStatus").val(1);  
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                  break;
                }

              }
            } 
          });
        $.ajax({
          type: "POST",
          url: "{{url('/complaint/getBuildingDetail')}}",
          data: {"id":id,"_token": "{{ csrf_token() }}"},
          cache: false,
              //dataType: "json",
              success: function(data)
              {
                var result = $.parseJSON(data);
                $('#building_name').val(result[0].building_name);
                $('#way_no').val(result[0].building_address);
                $('#tenant_location_id').val(result[0].location_id);
                $('#location_name').val(result[1].locations_name);
                
              } 
            });


      });*/
/*************************************************************************/
/*
$(document).on('change',".complaintUnit", function(){
  var id = $("#building_text_id").val();
  if(id == null){id = $("#building_id").val();}
  var unit = $("#unit_id").val();
  var ocselected = '';
  if(id !="" && unit != ""){
    $.ajax({
      method: "POST",
      url: "{{route('contractDetailsByBuildingUnit')}}",
      data: { building: id, unit : unit , 
        "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
          var results = $.parseJSON(data);
          $('#registerd_mob_no-error').hide();
          $('#tenant_name').val(results[1].tenant_name); 
          $('#resident_card_id').val(results[1].resident_id);
          $('#registerd_mob_no').val(results[1].tenant_contact_no);
          $('#complaint_mob_no').val(results[1].tenant_contact_no);
          $('#tenant_id').val(results[1].id);
          $('#complainer_name').val(results[1].tenant_name);
          //$('#way_no').val(results[1].tenant_contact_address);
          if(results[2]!=null){
            //$('#location_name').val(results[2].locations_name);
            //$('#tenant_location_id').val(results[2].id);

          }
          if(results[5] != null){ocselected = "selected";}
          $('#occupant_name').empty();
          $('#occupant_name').append('<option value="">'+ 'Select Occupant' +'</option>')
          
            //alert(value['building_id']);

            $('#occupant_name').append('<option value="'+ results[5].id +'" '+ ocselected +'>'+ results[5].occupant_name +'</option>');

            $('#way_no').val(results[3].building_address);
            $('#location_name').val(results[4].locations_name);                               
            $('#tenant_location_id').val(results[3].location_id);

            $(".read").attr('readonly',true);
            var status = results[0].status;
            var tenantStatus = results[1].status;
            var buil_status = results[3].building_maintenance_info;
            if(buil_status != null){
              switch(buil_status){
                case 0:
                  //$("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 1:
                  $(".tenantStatus").show();
                  $("#tenantStatus").val(1); 
                  $('.tenantStatus').html('<div class="alert bg-success " role="alert">'+'Maintained By Landlord'+'</div>');
                  break;
                }

              } if(status != null){
                switch(status){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 2:
                  $("#tenantStatus").val(2);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'On Hold'+'</div>');
                  break;
                  case 3: 
                  $("#tenantStatus").val(3);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-warning " role="alert">'+'No Maintenance'+'</div>');
                  break;
                  case 4: 
                  $("#tenantStatus").val(4);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-primary " role="alert">'+'Blacklisted'+'</div>');
                  break;
                  case 5: 
                  $("#tenantStatus").val(5);
                  $(".tenantStatus").show();
                  $('.tenantStatus').html('<div class="alert bg-info " role="alert">'+'Legal'+'</div>');
                  break;
                }
              }else{
                switch(tenantStatus){
                  case 0:
                  $("#tenantStatus").val(0); 
                  $(".tenantStatus").hide();
                  break;
                  case 6:
                  $("#tenantStatus").val(6);
                  $(".tenantStatus").show(); 
                  $('.tenantStatus').html('<div class="alert bg-danger " role="alert">'+'VIP'+'</div>');
                  break;
                }
              }    

            }
          });
  }
});
*/
/*************************************************************************/

});


 //Unit Type

 /***sortablePriceRange1***/


 $("#myModal").on("hidden.bs.modal", function(){
  $("#myModal").html("");
  $(this).removeData('bs.modal');
});

 /*************************************************************************/
 $(document).ready(function() {
  $('.group_ctrl').change(function () {
     // alert(1);
        // gets data-group value and uses it in the outer selector
        // to select the inputs it controls and sets their disabled 
        // property to the negated value of it's checked property 
        $("." + $(this).data("group_b")).prop('disabled', !this.checked);
      }).change();
});
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}




</script>