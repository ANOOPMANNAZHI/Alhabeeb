<script>
	$(document).ready(function() {

    $(document).on('change keyup paste','.search_fields',function(){ 

      var focus 				                   = $(this).attr('id'); 
      var landlord_contract_old_no 			   = $("#landlord_contract_old_no").val();
      var landlord_contract_no 			       = $("#landlord_contract_no").val();
      var landlord_contract_valid_from_date = $("#landlord_contract_valid_from_date").val();
      var landlord_contract_valid_to_date	 = $("#landlord_contract_valid_to_date").val();
      var building_name                    = $("#building_name").val();
      var vendor_name 	                   = $("#vendor_name").val();
      var landlord_contract_amt            = $("#landlord_contract_amt").val();
      // alert(building_id);
      //var landlord_contract_name 	    = $("#landlord_contract_name").val();
      //alert(landlord_contract_name);
      var route 				                   = $("#route").val(); //alert(route);
      var route_href 			                 = $("#url_route").val();
      var fieldName                        = [];
      var operation                        = [];
      var fieldValue                       = [];
      var logic                            = [];


      // Initializing array  
      $(".fieldName").each(function(){
        if(this.value != '')
          fieldName.push(this.value);
      });

      $(".operation").each(function(){
          operation.push(this.value);
      });      

      $(".fieldValue").each(function(){
          fieldValue.push(this.value);
      });

      $(".logic").each(function(){
          logic.push(this.value);
      });
      
      
      $.ajax({
      	method: "GET",
      	url: '{{$quick_url}}',
      	data: { 'new_contract': landlord_contract_no,'old_contract': landlord_contract_old_no,'landlord_contract_valid_from_date': landlord_contract_valid_from_date,'landlord_contract_valid_to_date': landlord_contract_valid_to_date,'building_name': building_name,'vendor_name': vendor_name,'route':'{{$quick_url}}',"_token" : $('meta[name="csrf-token"]').attr('content') , 'ajax' : true ,'fieldName' : fieldName , 'operation' : operation , 'fieldValue' : fieldValue , 'logic' : logic,'landlord_contract_amt':landlord_contract_amt },			  
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
            	 
            		txt = 'old_contract='+landlord_contract_old_no+ '&new_contract='+landlord_contract_no + '&landlord_contract_valid_from_date='+landlord_contract_valid_from_date+ '&landlord_contract_valid_to_date='+landlord_contract_valid_to_date+'&building_name='+building_name+'&vendor_name='+vendor_name+'&landlord_contract_amt='+landlord_contract_amt;


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

	
});
</script>
