<script>
$(document).ready(function() {
	var timer;
  	var delay = 300;
	$(document).on('change keyup paste','.search_fields',function(){ 

	  var focus 				= $(this).attr('id'); 
	  var complaint_no 			= $("#complaint_no").val();
	  var created_at 			= $("#created_at").val();
	  var complainer_name		= $("#complainer_name").val();
	  var complaint_mob_no 	    = $("#complaint_mob_no").val();
	  var unit 					= $("#unit_id").val();
	  var location 				= $("#location_id").val();
	  var ticket_no 		    = $("#ticket_no").val();
	  var category 		        = $("#category").val();

	  var status 			    = $("#status").val();
	  var tenant_status 		= $("#tenant_status").val();
	  
	  var route 				= $("#route").val(); 
	  var lastPart              = route.split("/").pop();
      var route_href 			= $("#url_route").val();
	  var email					= $(".email").val();	
	  var building_id 	        = $("#building_id").val();
	  var assigned_person 		= $(".assigned_person").val();
	  var building_name_select 	= $("#building_name_select").val();	
	  var unit_select 			= $("#unit_select").val();	
	  var duration 				= $("#duration").val();	
	  var start_dt 				= $("#start_dt").val();	
	  var rent 					= $("#rent").val();	
	  var service_report_no     = $("#service_report_no").val();

	  var priority_status     = $("#priority_status").val();	
	  var assigned_to     = $("#assigned_to").val();	
	  var assignedPerson    = $("#assignedPerson").val();	
	  var subAssignedPerson    = $("#subAssignedPerson").val();	
	  
	  
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
      if(lastPart == 'complaint'|| lastPart == 'complaintStage')route = route_href;
      
     window.clearTimeout(timer);
    	timer = window.setTimeout(function(){
	  $.ajax({
			  method: "GET",
			  url: '{{url()->current()}}',
			  data: { 'complaint_no': complaint_no,'complaint_date': created_at,'complainer_name': complainer_name,'complaint_mob_no': complaint_mob_no,'unit__unit_code': unit,'location__locations_name': location,'route':route,"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic ,'building__building_name':building_id,'complaint_status':status,'complaintTicketsAll__complaint_ticket_no':ticket_no ,'complaintTicketsAll__work__works_code':category,'tenant_status':tenant_status,	'service_report_no':service_report_no ,'priority_status':priority_status,'assigned_to':assigned_to,'assignedPerson__employee__employee_name':assignedPerson,
			      'subAssignedPerson__employee__employee_name':subAssignedPerson,},		  
			  beforeSend: function(){
                // Show image container
                $('#enquiry-search').html("<tr><td colspan='10' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
				
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
			    txt =  'complaint_no='+complaint_no+'&complaint_date='+created_at+'&complainer_name='+complainer_name+'&complaint_mob_no='+ complaint_mob_no+'&unit__unit_code='+unit+'&location__locations_name='+location+'&building__building_name='+building_id+'&complaint_status='+status+'&tenant_status='+tenant_status+'&complaintTicketsAll__complaint_ticket_no='+ticket_no+'&complaintTicketsAll__work__works_code='+category+'&service_report_no='+service_report_no+'&priority_status='+priority_status+'&assigned_to='+assigned_to+'&assignedPerson__employee__employee_name='+assignedPerson+'&subAssignedPerson__employee__employee_name='+subAssignedPerson;

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
      }, delay);

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
