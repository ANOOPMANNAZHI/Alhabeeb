<script>
$(document).ready(function() {

	$(document).on('change keyup paste','.search_fields',function(){ 

  	  var focus 				= $(this).attr('id'); 
  	  var tenant_name			= $("#tenant_name").val();	
	  var sales_enquiry_no 		= $("#sales_enquiry_no").val();
	  var created_at 			= $("#created_at").val();
	  var fc_att 				= $("#fc_att").val();
	  var customer_name			= $("#sales_enquiry_name").val();
	  var phone 				= $("#sales_mobile_no").val();
	  var unit 					= $("#unit").val();
	  var unitTypes 			= $("#unitTypes").val();
	  var location 				= $("#location").val(); 

	  var remark 				= $("#remark").val();
	  var sales_note 			= $("#sales_note").val();
	  var route 				= $("#route").val(); //alert(route);
      var route_href 			= $("#leade_search").attr('action');  
	  var email					= $(".email").val();	
	  var sales_building_name 	= $("#building_name").val();
	  var assigned_person 		= $(".assigned_person").val();
	  var building_name_select 	= $("#building_name_select").val();	
	  var unit_no_select 		= $("#unit_no_select").val();	
	  var unit_select 			= $("#unit_select").val();	
	  var duration 				= $("#duration").val();	
	  var start_dt 				= $("#start_dt").val();	
	  var rent 					= $("#rent").val();	
	  var inprogress_days 		= $("#inprogress_day").val();	
	  var unit_usage 			= $("#unit_usage").val();
	  var cust 					= $("#cust").val();
	  var cust_no 				= $("#cust_no").val();
	  
	  
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
			method: "POST",
			url: '{{$quick_url}}',
			data: {'sales_enquiry_no': sales_enquiry_no,'sales_email':email,'created_at': created_at,'sales_enquiry_name': customer_name,'route':route,'sales_mobile_no': phone,'unit_type':unitTypes,'unit': unit,
			'sales_note': remark,
			'loc': location,'route':route,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,'sales_building_name':sales_building_name,'employee_name':assigned_person,'building_name':building_name_select,'unit_code':unit_select,'tenant_contract_duration':duration,'tenant_contract_start_date':start_dt, 'tenant_contract_rent':rent,'tenant_name':tenant_name,'unit_code':unit_no_select ,
				          'sales_notes':sales_note,'inprogress_days':inprogress_days,'unit_usage':unit_usage,'cust':cust,'cust_no':cust_no 
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
					//temp.find('.pagination_ajax').remove();				
					temp.find('#pagination_ajax').remove();
					//alert('fdfdsf')
					
					$('#enquiry-search').html(temp);
					$("#pagination").html(paginate);  
					$( "#pagination_info" ).html(paginate_info);			
			}  
			var txt = '' ;

			txt ='sales_enquiry_no='+sales_enquiry_no+'&sales_email='+email+'&created_at='+created_at+'&sales_enquiry_name='+customer_name+'&route='+route+'&sales_mobile_no='+ phone+'&unit_type='+unitTypes+'&salesNotes__sales_notes='+sales_note+'&loc='+location+'&sales_building_name='+sales_building_name+'&building_name='+building_name_select+'&assignedPerson__employee__employee_name='+assigned_person+'&tenant_contract_start_date='+start_dt+'&tenant_contract_rent='+rent+'&unit_code='+unit_select+'&tenant_contract_duration='+duration+'&tenant_name='+tenant_name+'&unit_code='+unit_no_select+'&sales_note='+ remark+'&inprogress_days='+inprogress_day+'&unit_usage='+unit_usage+'&cust='+cust+'&cust_no'+cust_no;			   

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
