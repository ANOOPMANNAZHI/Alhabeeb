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
    
    
    
    $(document).on('change','.fieldName',function(){  
		var fieldValue = $(this).val() ;
        
         if( fieldValue == 'sales_move_in_date' ||  fieldValue  == 'created_at'){  
		  	  $(this).closest('.row').find('.fieldValue').attr('type','date');
		 }else{
			 $(this).closest('.row').find('.fieldValue').attr('type','text');
			 }
			 
     });
     
    $(".adSearch").on('click',function(){
      $(".advanceSearch").toggle();
    }); 
  });
</script>
 
 
