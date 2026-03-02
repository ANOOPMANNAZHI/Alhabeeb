<script>
  $( document ).ready(function() {

    /*******************On chANGE******************/
    $('#category').hide();
    $('#building_name').hide();
    $('#Contractor').hide();
    $('#unit_code').hide();
    $('#Technician').hide();
    $('.val2').hide();
    $(document).on('change',"#filter_type", function(){
      var type = $("#filter_type").val();
      if(type == 1){ //open ticket
        $('#category').hide();
        $('#Contractor').hide();
        $('#building_name').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 2){ //closed ticket
        $('#category').hide();
        $('#Contractor').hide();
        $('#building_name').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 3){ //category wise
        $('#category').show();
        $('.val2').show();
        $('#Contractor').hide();
        $('#building_name').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', true);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 4){ //building wise
        $('.val2').show();
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('#building_name').show();
        $('#category').val('');
        $('#Contractor').val('');
        $('#building_name').val('');
        $('#unit_code').val('');
        $('#Technician').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', true);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 5){ //subcontractor wise
        $('#Contractor').show();
        $('#Technician').hide();
        $('#category').hide();
        $('#unit_code').hide();
        $('.val2').show();
        $('#building_name').hide();
        $('#category').val('');
        $('#Contractor').val('');
        $('#building_name').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Technician').prop('required', false);
        $('#Contractor').prop('required', true);
        $('#unit_code').prop('required', false);
      }else if(type == 6){//technician wise
        $('#Contractor').hide();
        $('#Technician').show();
        $('#category').hide();
        $('#unit_code').hide();
        $('.val2').show();
        $('#building_name').hide();
        $('#category').val('');
        $('#Contractor').val('');
        $('#building_name').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Technician').prop('required', true);
        $('#Contractor').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 7){//vip
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#building_name').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 8){//open ticket contractor wise
        $('#Contractor').show();
        $('#Technician').hide();
        $('#category').hide();
        $('#unit_code').hide();
        $('.val2').show();
        $('#building_name').hide();
        $('#category').val('');
        $('#Contractor').val('');
        $('#unit_code').val('');
        $('#building_name').val('');
        $('#Technician').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Technician').prop('required', false);
        $('#Contractor').prop('required', true);
        $('#unit_code').prop('required', false);
      }else if(type == 9){//open ticket technician wise
        $('#Contractor').hide();
        $('#Technician').show();
        $('#category').hide();
        $('#unit_code').hide();
        $('.val2').show();
        $('#building_name').hide();
        $('#category').val('');
        $('#Contractor').val('');
        $('#building_name').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Technician').prop('required', true);
        $('#Contractor').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 10){//maintained by alhabib
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('#building_name').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 11){//maintained by Landlord
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#building_name').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 12){//total complaints
        $('#category').hide();
        $('#building_name').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#unit_code').prop('required', false);
        $('#Technician').prop('required', false);
      }else if(type == 13){
        //24 hours
        $('#category').hide();
        $('#Contractor').hide();
        $('#building_name').hide();
        $('#unit_code').hide();
        $('#Technician').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 14){
        //48 hours
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#building_name').hide();
        $('#unit_code').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else if(type == 15){
        //greater than 48 hours
        $('#category').hide();
        $('#Contractor').hide();
        $('#Technician').hide();
        $('#unit_code').hide();
        $('#building_name').hide();
        $('.val2').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', false);
      }else{
        //unit wise
        $('#unit_code').show();
        $('.val2').show();
        $('#category').hide();
        $('#Contractor').hide();
        $('#building_name').hide();
        $('#Technician').hide();
        $('#category').val('');
        $('#building_name').val('');
        $('#Contractor').val('');
        $('#Technician').val('');
        $('#unit_code').val('');
        $('#category').prop('required', false);
        $('#building_name').prop('required', false);
        $('#Contractor').prop('required', false);
        $('#Technician').prop('required', false);
        $('#unit_code').prop('required', true);
      }
    });


  //AutoComplete For Unit Code
 /*********************************************************************************/ 
 $('#unit_code').autocomplete({
  source : '{!!URL::route('unitReportAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#unit_code").val('');
      $('#unit_code-error').show();
    }
  }
});
  });
</script>