<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields,.contract_search_field',function(){ 
	
	  
      //console.log(expiryFlag);
	  var sales_enquiry_no 		= $("#sales_enquiry_no").val();
	  var landlord_contract_no 		= $("#landlord_contract_no").val();
	  var created_at 		= $("#created_at").val();
	  var landlord_contract_valid_to_date 		= $("#landlord_contract_valid_to_date").val();
	  var landlord_contract_status 		= $("#landlord_contract_status").val();
	  var landlord_contract_duration 		= $("#landlord_contract_duration").val();
	  var buildingInfo__building_name 		= $("#buildingInfo__building_name").val();
	  var vendorName__vendor_name		= $("#vendorName__vendor_name").val();
	  var managementTypeInfo__management_types_name		= $("#managementTypeInfo__management_types_name").val();  
	  var expiryFlag = document.getElementById("expiredContracts").getAttribute("expiry");
	  
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
			 data: {
			      'landlord_contract_no': landlord_contract_no,'created_at':created_at,
			      'landlord_contract_valid_to_date': landlord_contract_valid_to_date,
			      'landlord_contract_status': landlord_contract_status,'route':'{{$quick_url}}',
			      'landlord_contract_duration': landlord_contract_duration,
			      'buildingInfo__building_name': buildingInfo__building_name,
			      'vendorName__vendor_name': vendorName__vendor_name,
			      'managementTypeInfo__management_types_name':managementTypeInfo__management_types_name,
			      "_token" : $('meta[name="csrf-token"]').attr('content') ,
			       'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 
			       'fieldValue' : fieldValue , 'logic' : logic,'salesEnquiry__sales_enquiry_no':sales_enquiry_no,expiredContracts:expiryFlag},			  
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
					$('#contract-search').html(temp);
					$("#pagination" ).html(paginate);	
					$( "#pagination_info" ).html(paginate_info); 	
					//$('.search_fields').trigger('blur');	
				
				}  
				
	var txt = '' ;
	txt = 'landlord_contract_no='+landlord_contract_no+'&created_at='+created_at+
			      '&landlord_contract_valid_to_date='+ landlord_contract_valid_to_date+
			      '&landlord_contract_status='+ landlord_contract_status+
			      '&landlord_contract_duration='+ landlord_contract_duration+
			      '&buildingInfo__building_name='+ buildingInfo__building_name+
			      '&vendorName__vendor_name='+ vendorName__vendor_name+
			      '&managementTypeInfo__management_types_name='+managementTypeInfo__management_types_name+'&salesEnquiry__sales_enquiry_no='+sales_enquiry_no_;

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
        queryTxt = decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic }));
     
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
