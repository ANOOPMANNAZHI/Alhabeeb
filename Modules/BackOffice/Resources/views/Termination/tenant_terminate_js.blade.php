<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

	  var focus 							= $(this).attr('id'); 
	  var contract_no 						= $("#contract_no").val();
	  var tenant_contract_old_no 			= $("#tenant_contract_old_no").val();
	  var tenant_contract_rent 			    = $("#tenant_contract_rent").val();
	  var building_id 			            = $("#building_id").val();
	  var building_no 			            = $("#building_no").val();
	  var unit_id		        			= $("#unit_id").val();
	  var unit_types_name		        	= $("#unit_types_name").val();
	  var tenant_id		        			= $("#tenant_id").val();
	  var tenant_contract_start_date 	    = $("#tenant_contract_start_date").val();
	  var tenant_contract_valid_to_date 	= $("#tenant_contract_valid_to_date").val();
	  var tenant_contract_muncipality_agr_no = $("#tenant_contract_muncipality_agr_no").val();

	  var locationId		        		  = $("#location_id").val();
	  var tenant_contact_no		        	  = $("#tenant_contact_no").val();
	  var way_no		        			  = $("#way_no").val();

	  var route 				= $("#route").val(); 
	  var lastPart              = route.split("/").pop();
      var route_href 			= $("#url_route").val();
      var tenant_contract_last_paid_date 			= $("#tenant_contract_last_paid_date").val();
      var termination_date 			= $("#termination_date").val();
      
	  
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
      if((lastPart == 'tenantRenewal') || (lastPart == 'tenantTermination'))route = route_href;
     
	  $.ajax({
			  method: "GET",
			  url: '{{$quick_url}}',
			  data: { 'tenant_contract_no': contract_no,'tenant_contract_rent': tenant_contract_rent, 'tenant_contract_old_no': tenant_contract_old_no,'building_name': building_id,'building_no': building_no,'unit_code': unit_id,'unitType__unit_types_name': unit_types_name,'tenant_contract_start_date': tenant_contract_start_date,'tenant_contract_valid_to_date': tenant_contract_valid_to_date,'tenant_name': tenant_id,'tenant_contract_muncipality_agr_no': tenant_contract_muncipality_agr_no,'route':'{{$quick_url}}','location__locations_name':locationId,'tenant_contact_no':tenant_contact_no,'building_pc':way_no,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic,
			      'tenant_contract_last_paid_date' : tenant_contract_last_paid_date, 
			      'termination_date': termination_date
			},			  
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
					 
				
				}  
				
				var txt = '' ;
				txt = 'tenant_contract_no='+contract_no+'&tenant_contract_rent='+tenant_contract_rent+'&tenant_contract_old_no='+tenant_contract_old_no+'&building_name='+ building_id+'&building_no='+ building_no+'&unit_code='+unit_id+'&unitType__unit_types_name='+unit_types_name+'&tenant_contract_start_date='+ tenant_contract_start_date+'&tenant_contract_valid_to_date='+ tenant_contract_valid_to_date+'&tenant_name='+ tenant_id+'&tenant_contract_muncipality_agr_no='+ tenant_contract_muncipality_agr_no+'&location__locations_name='+locationId+'&tenant_contact_no='+tenant_contact_no+'&building_pc='+way_no+'&tenant_contract_last_paid_date='+tenant_contract_last_paid_date+ 
			      '&termination_date='+termination_date;


			  var txt_hashes = txt.split('&');
			  var href_txt = '';
			  for(var i = 0; i < txt_hashes.length; i++)
			    {
			        txt_hash = txt_hashes[i].split('=');

			        if(txt_hash[1] !=  '' &&  txt_hash[1] != 'undefined'){                             
			         href_txt =  (href_txt != '')? href_txt + '&': href_txt;

			         href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
			        }                       
			    }


		var queryTxt = ''; 
        
        if(fieldName.length > 0)
        queryTxt =   decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));
		                 
		    href_txt = href_txt + '&'+queryTxt;


			 $('.sort_url').each(function (i, n) {
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