<script>
$(document).ready(function() {

	$(document).on('keyup','.search_fields',function(){ 
	 
	  var focus = $(this).attr('id');       
      
      var customer_name = $(".customer-name").val();
      var email = $(".email").val();
      var phone = $(".phone").val();
      var stages = $(".stages").val();
      var sales_building_name = $(".sales_building_name").val();
      var route = $("#route").val(); //alert(route);
      var route_href = $("#leade_search").attr('action');  
      
      
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
			  data: { 'customer_name': customer_name,'email': email,'phone': phone,'route':route,'stages':stages,
				"_token" : $('meta[name="csrf-token"]').attr('content') , 'type' :  'landlord' , 'ajax' : true ,
				'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,
				'sales_building_name' : sales_building_name
				},
			  success: function(data){ 
				if(data != 0){
				
				var temp = $(data);
				var paginate = temp.find('#pagination_ajax').clone();
				
				temp.find('#pagination_ajax').remove();
				$('#enquiry-search').html(temp);
				$( "#pagination" ).html(paginate);			
				
				}  
				
				var txt = '' ;
				if(customer_name != '')
				txt = txt + '&customer_name='+customer_name;
				
				if(email != '')
				txt = txt + '&email='+email;
				
				if(phone != '')
				txt = txt + '&phone='+phone;
				
				
				
				$('.landlord_sort').each(function (i, n) {
				  var href = $(n).attr('href');
				  $(n).attr('href',href+txt);
				});
				
			             
			  }           
			});
      
      
      
       
    });


    
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


    
});
</script>
