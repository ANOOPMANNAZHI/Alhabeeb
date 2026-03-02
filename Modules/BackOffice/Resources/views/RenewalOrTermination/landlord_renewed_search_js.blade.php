<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

	  var focus 				= $(this).attr('id'); 
	  var landlord_contract_no 			= $("#landlord_contract_no").val();
	  var landlord_contract_valid_from_date 			= $("#landlord_contract_valid_from_date").val();
	  var landlord_contract_valid_to_date		= $("#landlord_contract_valid_to_date").val();
	  var building_id 	    = $("#building_id").val();
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
			  data: { 'landlord_contract_no': landlord_contract_no,'landlord_contract_valid_from_date': landlord_contract_valid_from_date,'landlord_contract_valid_to_date': landlord_contract_valid_to_date,'building_id': building_id,'vendor_id': vendor_id,'route':route_href,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic },			  
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
				if(landlord_contract_no != '')
				txt = txt + '&landlord_contract_no='+landlord_contract_no;
				
				if(landlord_contract_valid_from_date != '')
				txt = txt + '&landlord_contract_valid_from_date='+landlord_contract_valid_from_date;
				
				if(landlord_contract_valid_to_date != '')
				txt = txt + '&landlord_contract_valid_to_date='+landlord_contract_valid_to_date;

			   if(building_id != '')
				txt = txt + '&building_id='+building_id;

			if(vendor_id != '')
				txt = txt + '&vendor_id='+vendor_id;
			if(landlord_contract_amt != '')
				txt = txt + '&landlord_contract_amt='+landlord_contract_amt;
				
				
				
				
				
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
