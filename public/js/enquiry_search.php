 <script>
 $(document).on('click','.add_search',function(){ 		 
             $.ajax({
                  method: "GET",
                  url: "{{route('enquiryFilter')}}",
                  data: { type: '{{$type}}', 
                    "_token" : $('meta[name="csrf-token"]').attr('content')},
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
</script>
 
 
 
