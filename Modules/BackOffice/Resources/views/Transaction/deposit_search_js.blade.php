<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

	  var focus 				= $(this).attr('id'); 
	  var deposit_refund_no 			= $("#deposit_refund_no").val();
	  var deposit_refund_date 			= $("#deposit_refund_date").val();
	  var receipts_generation_id 	    = $("#receipts_generation_id").val();
	  var building_id 	    = $("#building_id").val();
	  var building_code 	    = $("#building_code").val();
	  var unit_id 					= $("#unit_id").val();
	  var tenant_name 					= $("#tenant_name").val();
	  var tenant_code 					= $("#tenant_code").val();
	  var tenant_contract_id 					= $("#tenant_contract_id").val();
	  var deposit_refund_payment_method 					= $("#deposit_refund_payment_method").val();
	  var deposit_refund_amt 					= $("#deposit_refund_amt").val();
	  var deposit_refund_approval_status 					= $("#deposit_refund_approval_status").val();
	  
	  var route 				= $("#route").val(); //alert(route);
      var route_href 			= $("#url_route").val();
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
			  url: '{{$route}}',
			  data: { 'deposit_refund_no': deposit_refund_no,'deposit_refund_date': deposit_refund_date,'receiptGeneration__receipts_generation_receipt_no': receipts_generation_id,'tenantContract__building__building_name': building_id,'tenantContract__building__building_code': building_code,'tenantContract__Unit__unit_code': unit_id,'tenantContract__tenant__tenant_name': tenant_name,'tenantContract__tenant__tenant_code': tenant_code,'tenantContract__tenant_contract_no': tenant_contract_id,'deposit_refund_payment_method': deposit_refund_payment_method,'deposit_refund_amt': deposit_refund_amt,'deposit_refund_approval_status': deposit_refund_approval_status,'route':'{{$route}}',"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic },			  
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
				
				var txt = 'deposit_refund_no='+deposit_refund_no+'&deposit_refund_date='+ deposit_refund_date+'&receiptGeneration__receipts_generation_receipt_no='+ receipts_generation_id+'&tenantContract__building__building_name='+ building_id+'&tenantContract__building__building_code='+ building_code+'&tenantContract__Unit__unit_code='+ unit_id+'&tenantContract__tenant__tenant_name='+ tenant_name+'&tenantContract__tenant__tenant_code='+ tenant_code+'&tenantContract__tenant_contract_no='+ tenant_contract_id+'&deposit_refund_payment_method='+ deposit_refund_payment_method+'&deposit_refund_amt='+ deposit_refund_am+'&deposit_refund_approval_status='+ deposit_refund_approval_status;


		var txt_hashes = txt.split('&');
        var href_txt = '';

      for(var i = 0; i < txt_hashes.length; i++)
        {
            txt_hash = txt_hashes[i].split('=');

            if(txt_hash[1] !=  '' && txt_hash[1] != 'undefined' && txt_hash[1] != undefined){                             
             href_txt =  (href_txt != '')? href_txt + '&': href_txt;
             href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
            }                       
        }    

        
        var queryTxt = ''; 
        
        if(fieldName.length > 0)
        queryTxt =  decodeURIComponent($.param({'fieldName' : fieldName, 'operation':operation,'fieldValue':fieldValue ,'logic':logic })); 

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
