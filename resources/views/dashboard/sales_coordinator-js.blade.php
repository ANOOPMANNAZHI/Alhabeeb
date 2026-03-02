<script> 


$('.select_count').change(function (event) {
  
    var id = $(this).attr('id');
    var select_value = $(this).val();

    var  data_details = {};
     
    data_details['count_type'] = id;

    if(select_value != 'all')
    data_details['count_val'] = select_value;

    data_details['_token'] =  "{{ csrf_token() }}";
    
     $.ajax({
           type: "POST",
           url: "{{route('sales-cordinator-dashboard')}}",
        //   data: {count_type :id ,  count_val : select_value},
           data: data_details,
           success: function(data){

            
            $('.'+id+'_count').html(data.count)
            $("."+id+"_href").attr('href', data.href);      
           
          }
        });


    

   
});

</script>