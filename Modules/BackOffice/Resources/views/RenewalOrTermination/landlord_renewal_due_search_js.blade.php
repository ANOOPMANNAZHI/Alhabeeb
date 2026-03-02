<script>
  $(document).ready(function() {
   $(document).on('click','.add_search',function(){ 		 
             $.ajax({
                  method: "GET",
                  url: "{{route('renewalDueFilter')}}",
                  data: {"_token" : $('meta[name="csrf-token"]').attr('content')},
                  success: function(data){                            
                            if(data != 0){
                            $('#search_form').append(data);                           
                            }               
                        }           
              });              
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
 
 
