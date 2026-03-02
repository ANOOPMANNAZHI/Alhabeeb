<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

  	  var focus 				= $(this).attr('id'); 
	  var sales_enquiry_no 		= $("#sales_enquiry_no").val();
	  var created_at 			= $("#created_at").val();
	  var fc_att 				= $("#fc_att").val();
	  var customer_name			= $("#sales_enquiry_name").val();
	  var phone 				= $("#sales_mobile_no").val();	  
	  
	  var location 				= $("#location").val(); 

	  var sales_note 			= $("#sales_note").val();
	  var route 				= $("#route").val(); //alert(route);
      var route_href 			= $("#leade_search").attr('action');  
	  var email					= $(".email").val();	
	  var sales_building_name 	= $("#building_name").val();
	 
	  var building_name_select 	= $("#building_name_select").val();	
	  
	  
      var fieldName = [];
      var operation = [];
      var fieldValue = [];
      var logic = [];
      
      // Initializing array  
      $(".fieldName").each(function(){
			if(this.value != '')
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
			  method: "GET",
			  url: '{{$quick_url}}',
			 data: { 'sales_enquiry_no': sales_enquiry_no,'sales_email':email,'created_at': created_at,'sales_enquiry_name': customer_name,'route':'{{$quick_url}}','sales_mobile_no': phone,'sales_note': sales_note,'locations__locations_name': location,'route':route,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,'sales_building_name':sales_building_name,'landlordContract__buildingInfo__building_name':building_name_select},			  
			  beforeSend: function(){
                // Show image container
                $('#enquiry-search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
				
               },
			  success: function(data){ 

			  
				if(data != 0){
	
					var temp = $(data);
					var paginate_info = temp.find('.pagination_info').clone();
                    temp.find('.pagination_info').remove();
					var paginate = temp.find('#pagination_ajax').clone();
					
					temp.find('#pagination_ajax').remove();
					$('#enquiry-search').html(temp);
					$("#pagination" ).html(paginate);	
					$( "#pagination_info" ).html(paginate_info); 	
					//$('.search_fields').trigger('blur');	
				
				}  
				
				var txt = '' ;
				txt =  'sales_enquiry_no='+sales_enquiry_no+'&sales_email='+email+'&created_at='+ created_at+'&sales_enquiry_name='+customer_name+'&route='+route+'&sales_mobile_no='+ phone+'&sales_note='+ sales_note+'&locations__locations_name='+location+'&sales_building_name='+sales_building_name;

				  var txt_hashes = txt.split('&');
                  var href_txt = '';
                  for(var i = 0; i < txt_hashes.length; i++)
                    {
                        txt_hash = txt_hashes[i].split('=');

                        if(txt_hash[1] !=  '' && txt_hash[1] != 'undefined'){                             
                         href_txt =  (href_txt != '')? href_txt + '&': href_txt;

                         href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
                        }                       
                    }    

				
		var queryTxt = ''; 
        
        if(fieldName.length > 0)
        queryTxt =   decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));
                 
                href_txt = href_txt + '&'+queryTxt;

				
				
				
				$('.landlord_sort').each(function (i, n) {
				  var href = $(n).attr('href'); 
	              var hashes =  href.slice(href.indexOf('sort'));
	              href = href.split('?')[0];
	              $(n).attr('href',href+'?'+href_txt+'&'+hashes); 
				});
						             
			  }           
			});
      

    });
 
    
});
</script>
