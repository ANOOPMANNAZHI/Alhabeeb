<script>

// Vacating Units card: all three counts come with the page, just swap count and link
$('#vacating_sp').change(function () {
    var days = $(this).val();
    $('.vacating_sp_count').html($(this).find(':selected').data('count'));
    $('.vacating_sp_href').attr('href', "{{route('unit.index')}}" + '?vaccating_days=' + days + '&sold_buildings=1');
});


  $('.firstCall').change(function (event) {
    $( "#inprogressEnquiry_sp" ).trigger( "change" );
  });


$('.select_count_sp').change(function (event) {
  
    var id = $(this).attr('id');
    var select_value = $(this).val();

    var  data_details = {};
     
    data_details['count_type'] = id;

    if(select_value != 'all')
    data_details['count_val'] = select_value;

    data_details['_token'] =  "{{ csrf_token() }}";

    if(id == 'inprogressEnquiry_sp')
    data_details['firstCall'] = $('#inprogressEnquiryFirstCall_sp').val();  
    
     $.ajax({
           type: "POST",
           url: "{{route('sales_person_dashboard')}}",
        //   data: {count_type :id ,  count_val : select_value},
           data: data_details,
           success: function(data){

            
            $('.'+id+'_count').html(data.count)
            $("."+id+"_href").attr('href', data.href);      
           
          }
        });


    

   
});

</script>