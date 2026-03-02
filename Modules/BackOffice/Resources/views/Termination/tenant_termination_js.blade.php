<script>
  $(document).ready(function() {
    $('#unit_id').hide();
    $('#unit').show();
    //edit detail show
    var contractNo = $('#tenant_contract_id').val();
    if(contractNo){

      $.ajax({
        type: "POST",
        url: "{{route('agreementDetail')}}",
        data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
        cache: false,
        success: function(data)
        {
          $("#agreementDetail").html(data);

        } 
      });
    }




    $(document).on('change keyup paste','#tenant_contract_no,#building,#unit,#tenant_id',function(){ 

      var contractNum = $("#tenant_contract_no").val();
      var building = $("#building").val();
      var unit = $("#unit").val();
      var tenant = $("#tenant_id").val();

      if(contractNum =="" && building=="" && unit=="" && tenant==""){
        $("#agreementDetail").empty();
        $("#tenant_contracts_id").hide();
        $("#tenant_contract_no").show();
        $("#unit_id").hide();
        $("#unit").show();
      }else{
        $("#agreementDetail").show();
      }

    });
  });
  /*************************************TeanantContact****************************/
  $('#tenant_contract_no').autocomplete({
    source : '{!!URL::route('agreementAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    select:function(e,ui){
      if(ui.item.ids != null){
        $('#tenant_contract_id').val(ui.item.ids);
        var contractNo = ui.item.ids;

        if(contractNo){
          $.ajax({
            type: "POST",
            url: "{{route('agreementDetail')}}",
            data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              $("#agreementDetail").html(data);

            } 
          });
          $.ajax({
            type: "POST",
            url: "{{route('agreementDetailAgainstBulUnit')}}",
            data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              var result = $.parseJSON(data);
              $("#tenant_contract_no").val(result[3].tenant_contract_no);
              $("#tenant_id").val("");
              $("#building").val("");
              $("#unit").val("");
              $("#unit_id").hide();
              $("#unit").show();
            } 
          });

        }
      }

    }
  });


  /***************************Building*********************************/
  $('#building').autocomplete({
    source : '{!!URL::route('allBuildingAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    select:function(e,ui){
      if(ui.item.ids != null){
        $('#building_id').val(ui.item.ids);
        var building_id = ui.item.ids;
        var building = $('#building').val();

        if(building_id != "" && building != ""){
          $('#unit').hide();
          $('#units_id').hide();
          $('#unit_id').show();
          $.ajax
          ({
            type: "POST",
            url: "{{route('occupiedUnitDetail')}}",
            data: {"building_id":building_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              var result = $.parseJSON(data);
              $("#tenant_contract_no").val("");
              $("#agreementDetail").empty();
              $("#tenant_id").val("");
              $("#tenant_contracts_id").hide();
              $("#tenant_contract_no").show();
              selected = "";
              if(result[0].length > 0){
                if(result[0].length ==1){selected = "selected";}
                $('#unit_id').empty();
                $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                $.each(result[0], function(key, value) {
                  $('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_no'] +'</option>');
                });
                var unit_id = $("#unit_id").val();  
                if(unit_id){
                  $.ajax({
                    type: "POST",
                    url: "{{route('agreementDetail')}}",
                    data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                    cache: false,
                    success: function(data)
                    {
                      $("#agreementDetail").html(data);


                      $("#tenant_contract_no").val("");
                      $("#tenant_id").val("");
                      $("#tenant_contracts_id").hide();
                      $("#tenant_contract_no").show();
                    } 
                  });
                  $.ajax({
                    type: "POST",
                    url: "{{route('agreementDetailAgainstBulUnit')}}",
                    data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
                    cache: false,
                    success: function(data)
                    {
                      var result = $.parseJSON(data);
                      $("#building").val(result[0].building_name);
                     // $('#unit_id').empty();
                      //if(result[1]!= null){selected = "selected";}
                     // $('#unit_id').append('<option value="'+ result[1].id +'" '+ selected +'>'+ result[1].unit_code +'</option>')



                    } 
                  });
                }
              }
              else{
                $('#unit_id').html('<option value="">No Data</option>');
                $("#tenant_id").val("");
                $("#tenant_contract_no").val("");
              }
            } 
          });

        } 

      }

    }
  });
  /***********************************UNit Change*******************************/
  $("#unit_id").on('change keyup input',function(e){
    var building_id = $('#building_id').val();
    var unit_id = $("#unit_id").val();
    if(building_id != "" && unit_id != ""){
      $.ajax
      ({
        type: "POST",
        url: "{{route('agreementDetail')}}",
        data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
        cache: false,
        success: function(data)
        {
          $("#agreementDetail").html(data);
          $("#tenant_contract_no").val("");
          $("#tenant_id").val("");
          $("#tenant_contracts_id").hide();
          $("#tenant_contract_no").show();

        } 
      });
      $.ajax({
        type: "POST",
        url: "{{route('agreementDetailAgainstBulUnit')}}",
        data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
        cache: false,
        success: function(data)
        {
          var result = $.parseJSON(data);
          $("#building").val(result[0].building_name);
         // $('#unit_id').empty();
         // if(result[1]!= null){selected = "selected";}
         // $('#unit_id').append('<option value="'+ result[1].id +'" '+ selected +'>'+ result[1].unit_code +'</option>')
        } 
      });
    }

  }); 
  /***************************UNit**********************************/
  $('#unit').autocomplete({
    source : '{!!URL::route('unitAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    select:function(e,ui){
      if(ui.item.ids != null){
        $('#units_id').val(ui.item.ids);
        var unit_id = ui.item.ids;

        if(unit_id){
          $.ajax({
            type: "POST",
            url: "{{route('agreementDetail')}}",
            data: {"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              $("#agreementDetail").html(data);
              $("#tenant_contract_no").val("");
              $("#tenant_id").val("");
              $("#building").val("");
              $("#tenant_contracts_id").hide();
              $("#tenant_contract_no").show();

            } 
          });
          $.ajax({
            type: "POST",
            url: "{{route('agreementDetailAgainstBulUnit')}}",
            data: {"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              var result = $.parseJSON(data);
              $("#unit").val(result[1].unit_code);
              $("#tenant_id").val("");
              $("#building").val("");
              $("#tenant_contract_no").val("");
            } 
          });

        }
      }

    }
  });

  /***************************Tenant**********************************/
  $('#tenant_id').autocomplete({
    source : '{!!URL::route('tenantAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      if(ui.item.ids != null){
        $('#tenants_id').val(ui.item.ids);
        var tenant_id = ui.item.ids;
        if(tenant_id){
          $.ajax
          ({
            type: "POST",
            url: "{{route('getTenantContractByTenantId')}}",
            data: {"tenant_id":tenant_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              var result = $.parseJSON(data);
              selected = "";
              if(result[0].length > 0){
                if(result[0].length ==1){selected = "selected";}
                $('#tenant_contract_no').hide();
                $('#tenant_contracts_id').show();
                $('#tenant_contracts_id').empty();
                $('#tenant_contracts_id').append('<option value="">'+ 'Select Agreement No' +'</option>')
                $.each(result[0], function(key, value) {
                  $('#tenant_contracts_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_contract_no'] +'</option>');
                });

                var tenant_id = $('#tenant_id').val();
                var contractNo = $("#tenant_contracts_id").val();
                if(contractNo != "" && tenant_id != ""){
                  $.ajax
                  ({
                    type: "POST",
                    url: "{{route('agreementDetail')}}",
                    data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
                    cache: false,
                    success: function(data)
                    {
                      $("#agreementDetail").html(data);

                      $("#unit").val("");
                      $("#building").val("");
                      $("#unit_id").hide();
                      $("#unit").show();
                    } 
                  });
                }


              }
              else{
                $('#tenant_contracts_id').html('<option value="">No Data</option>');
                $("#building").val("");
                $("#unit_id").hide();
                $("#unit").show();
              }
            } 
          });

        } 




      }

    }
  });
  /********************************Tenant contract id change*********************/
  $("#tenant_contracts_id").on('change keyup input',function(e){
    var tenant_id = $('#tenant_id').val();
    var contractNo = $("#tenant_contracts_id").val();
    if(contractNo != "" && tenant_id != ""){
      $.ajax
      ({
        type: "POST",
        url: "{{route('agreementDetail')}}",
        data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
        cache: false,
        success: function(data)
        {
          $("#agreementDetail").html(data);
          $("#building").val("");
          $("#unit_id").hide();
          $("#unit").show();
        } 
      });
    }
  }); 
  /****************************************************************************/

</script>