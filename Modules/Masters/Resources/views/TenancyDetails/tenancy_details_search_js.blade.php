<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

	  var focus 				= $(this).attr('id'); 
	  var amc_schedule_id			= $("#amc_schedule_id").val();
	  var amc_schedule_from_date 			= $("#amc_schedule_from_date").val();
	  var amc_schedule_to_date		= $("#amc_schedule_to_date").val();
	  var amenities_types_id 	    = $("#amenities_types_id").val();
	  var building_id 	    = $("#building_id").val();
	  var payment_method_id 	    = $("#payment_method_id").val();
	  var amc_schedule_task_status 	    = $("#amc_schedule_task_status").val();
	  var vendor_id 	    = $("#vendor_id").val();
	  
	  var route 				= $("#route").val(); //alert(route);
      var route_href 			= $("#url_route").val();
      var fieldName = [];
      var operation = [];
      var fieldValue = [];
      var logic = [];
     
      
      // Initializing array  
      $(".fieldName").each(function(){
		//	if(this.value != '')
            fieldName.push(this.value);
      });
      
      $(".operation").each(function(){
		//	if(this.value != '')
            operation.push(this.value);
      });      
      
      $(".fieldValue").each(function(){
		//	if(this.value != '')
            fieldValue.push(this.value);
      });
      
      $(".logic").each(function(){
		//	if(this.value != '')
            logic.push(this.value);
      });
      
      
	  $.ajax({
			  method: "POST",
			  url: route_href,
			  data:{ 'amc_schedule_id': amc_schedule_id,'amc_schedule_from_date': amc_schedule_from_date,'amc_schedule_to_date': amc_schedule_to_date,'amenities_types_id': amenities_types_id,'vendor_id': vendor_id,'amc_schedule_task_status': amc_schedule_task_status,'building_id': building_id,'payment_method_id': payment_method_id,'route':route_href,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic },			  
			  beforeSend: function(){
                // Show image container
                $('#enquiry-search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
				
               },
			  success: function(data){ 

			  
				if(data != 0){
	
					var temp = $(data);
					var paginate = temp.find('#pagination_ajax').clone();
					
					temp.find('#pagination_ajax').remove();
					$('#enquiry-search').html(temp);
					$("#pagination" ).html(paginate);		
					$('.search_fields').trigger('blur');	
				
				}  
				
				var txt = '' ;
				if(amc_schedule_id!= '')
				txt = txt + '&amc_schedule_id'+amc_schedule_id
				
				if(amc_schedule_from_date != '')
				txt = txt + '&amc_schedule_from_date='+amc_schedule_from_date;
				
				if(amc_schedule_to_date != '')
				txt = txt + '&amc_schedule_to_date='+amc_schedule_to_date;

			   if(amenities_types_id != '')
				txt = txt + '&amenities_types_id='+amenities_types_id;

			if(amc_schedule_task_status != '')
				txt = txt + '&amc_schedule_task_status='+amc_schedule_task_status;
				
				
				
				
				
				$('.landlord_sort').each(function (i, n) {
				  var href = $(n).attr('href');
				  $(n).attr('href',href+txt);
				});
						             
			  }           
			});
      

    });

	/*
    
    $(document).on('change','.searchFields',function(){ 

      
      var customer_name = $("#customer-name").val();
      var email = $("#email").val();
      var phone = $("#phone").val();
      var stages = $("#stages").val();
      var route = "{{ Request::fullUrl() }}";  
     
        $.ajax({
          method: "POST",
          url: "{{route('landlord.landlordSearch')}}",
          data: { 'customer_name': customer_name,'email': email,'phone': phone,'route':route,'stages':stages,
            "_token" : $('meta[name="csrf-token"]').attr('content'), 'type' :   'landlord' , 'ajax' : true },
          success: function(data){ 

            if(data != 0){
				
                var temp = $(data);
				var paginate = temp.find('#pagination_ajax').clone();
				
				temp.find('#pagination_ajax').remove();
				$('#enquiry-search').html(temp);
				$( "#pagination" ).html(paginate);	
            }               
          }           
        });

       
    });

	*/
    
});
</script>
