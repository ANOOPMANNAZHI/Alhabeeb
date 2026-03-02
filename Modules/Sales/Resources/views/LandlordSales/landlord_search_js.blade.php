<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

  var focus 				= $(this).attr('id'); 
	  var sales_enquiry_no 		= $("#sales_enquiry_no").val();
	  var created_at 			= $("#created_at").val();
	  var fc_att 				= $("#fc_att").val();
	  var customer_name			= $("#sales_enquiry_name").val();
	  var phone 				= $("#sales_mobile_no").val();
	  var unit 					= $("#unit").val();
	  var unitTypes 			= $("#unitTypes").val();
	  var location 				= $("#location").val(); 

	  var sales_note 			= $("#sales_note").val();
	  var route 				= $("#route").val(); //alert(route);
      var route_href 			= $("#leade_search").attr('action');  
	  var email					= $(".email").val();	
	  var sales_building_name 	= $("#building_name").val();
	  var assigned_person 		= $(".assigned_person").val();
	  var building_name_select 	= $("#building_name_select").val();	
	  var unit_select 			= $("#unit_select").val();	
	  var duration 				= $("#duration").val();	
	  var start_dt 				= $("#start_dt").val();	
	  var rent 					= $("#rent").val();	
	  
	  
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
			 data: { 'sales_enquiry_no': sales_enquiry_no,'email':email,'created_at': created_at,'customer_name': customer_name,'route':route,'phone': phone,'unitTypes':unitTypes,'unit': unit,'sales_note': sales_note,'location': location,'route':route,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,'sales_building_name':sales_building_name,'assigned_person':assigned_person,'building_name_select':building_name_select,
				      'unit_select':unit_select,'duration':duration,'start_dt':start_dt, 'rent':rent },			  
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
					//$('.search_fields').trigger('blur');	
				
				}  
				
				var txt = '' ;
				if(sales_enquiry_no != '')
				txt = txt + '&sales_enquiry_no='+sales_enquiry_no;
				
				if(created_at != '')
				txt = txt + '&created_at='+created_at;
				
				if(sales_enquiry_name != '')
				txt = txt + '&sales_enquiry_name='+phone;
				
				if(duration != '')
				txt = txt + '&duration='+duration;
				
				
				
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
