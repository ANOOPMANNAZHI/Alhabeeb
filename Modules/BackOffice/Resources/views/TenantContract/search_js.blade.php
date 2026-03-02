<script>
  $(document).ready(function() {
   $(document).on('click','.add_search',function(){ 
  
  var filter =   $(this).closest('.row').clone();	
   
  $(filter).find(".add_search")
           .removeClass("add_search").addClass('remove_search').end()
           .find(".remove_search").html('Remove').end()
           .appendTo('#search_form')  

            
    });

    $(document).on('click','.remove_search',function(){
        $(this).closest('div .row').remove();
    });
//'<label for="fieldValue"> Value</label>'
    var status_select = '<label for="fieldValue"> Value</label><select class="form-control fieldValue" name="fieldValue[]" required>'+'<option value="1">Active</option>'+'<option value="0">InActive</option>'+'</select>';
    var input_select = '<label for="fieldValue"> Value</label><input autocomplete="off" required type="text" class="form-control '+'fieldValue" name="fieldValue[]" placeholder="Enter Value" value="">';    
    
    
    $(document).on('change','.fieldName',function(){  
		var fieldValue = $(this).val() ;
     var curr_field =  $(this).closest('.row').find('.fieldValue');
        if( fieldValue == 'tenant_contract_status') 
         curr_field.closest('div').html(status_select);
        else{

         if($(curr_field).closest('div').has('input').length == 0)
          curr_field.closest('div').html(input_select);
       
           if( fieldValue == 'sales_move_in_date' ||  fieldValue  == 'created_at') 
              $(this).closest('.row').find('.fieldValue').attr('type','date');
           else
              $(this).closest('.row').find('.fieldValue').attr('type','text');
        }
			 
     });
     
    $(".adSearch").on('click',function(){
      $(".advanceSearch").toggle();
    }); 
  });
</script>
 
 
