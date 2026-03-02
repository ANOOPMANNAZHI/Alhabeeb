<script>
$(document).ready(function() {
  // ---- Print Function
  $('.btnprn').printPage();
  $(document).on('click','.add_search',function(){ 		

     var filter =   $(this).closest('.row').clone();  
   
    $(filter).find(".add_search")
             .removeClass("add_search").addClass('remove_search').end()
             .find(".remove_search").html('Remove').end()
             .appendTo('#search_form')              
  });

  $(document).on('click','.remove_search',function(){
    $(this).closest('div .row').remove();
  });

  /*$(document).on('change','.fieldName',function(){  
  var fieldValue = $(this).val() ;

  if( fieldValue == 'sales_move_in_date' ||  fieldValue  == 'created_at'){  
  $(this).closest('.row').find('.fieldValue').attr('type','date');
  }else{
  $(this).closest('.row').find('.fieldValue').attr('type','text');
  }

  });*/

  

  $(".adSearch").on('click',function(){
    $(".advanceSearch").toggle();
  }); 

  $("#receipts_generation_eff_from, #receipts_generation_eff_to, #payment_type, #status, #receipt_date").change(function(){
                
                $('.search_fields').trigger('keyup');
    });


    $('.search_fields').on('keyup',function(){ 
    
     var showResultsTimer = 0;      
     searchnow = this.value;
     var receipts_generation_receipt_no     = $("#receipt_no").val();
     var receipts_generation_receipt_date       = $("#receipt_date").val();
     var receipts_generation_eff_from       = $("#receipts_generation_eff_from").val();
     var receipts_generation_eff_to         = $("#receipts_generation_eff_to").val();
     var unit_no                      = $("#unit_no").val();
     var building_name                      = $("#bldg_name").val();
     var building_code                      = $("#bldg_code").val();
     var tenant_name                        = $("#tenant_name").val();
     var tenant_code                        = $("#tenant_code").val();
     var contract_no                        = $("#contract_no").val();
     var receipts_generation_payment_method = $("#payment_type").val();
     var receipts_generation_amt            = $("#amount").val();
     var receipts_generation_approval_status= $("#status").val();
     var route_href                         = $("#action").val(); 
     var tab                                = $("#tab").val(); 
     var are_id = document.getElementById("are").getAttribute("are");
     var date = document.getElementById("date").getAttribute("date");

     console.log(are_id)
     console.log(date)





    
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
    //  if(this.value != '')
            operation.push(this.value);
      });      
      
      $(".fieldValue").each(function(){
    //  if(this.value != '')
            fieldValue.push(this.value);
      });
      
      $(".logic").each(function(){
    //  if(this.value != '')
            logic.push(this.value);
      });
      
          
     window.clearTimeout(showResultsTimer);
     showResultsTimer = window.setTimeout(function(){   
         $.ajax({
                  method: "GET",
                  url: '{{$route}}',
                  data: {are_id:are_id,date:date,'receipts_generation_receipt_no': receipts_generation_receipt_no,'receipts_generation_receipt_date': receipts_generation_receipt_date,'receipts_generation_eff_from':receipts_generation_eff_from,'receipts_generation_eff_to':receipts_generation_eff_to,'unit_no':unit_no,'building_name':building_name,'building_code':building_code,
                         'tenant_name': tenant_name, 'tenant_code':tenant_code,'tenant_contract_no':contract_no ,'receipts_generation_payment_method':receipts_generation_payment_method,'receipts_generation_amt':receipts_generation_amt, 'receipts_generation_approval_status':receipts_generation_approval_status ,'route':'{{$route}}',
                           'tab':tab,'ajax' : true ,'fieldName' : fieldName , 
                           'operation' : operation , 'fieldValue' : fieldValue , 
                           'logic' : logic},           
                  beforeSend: function(){
                    // Show image container
                    $("#pagination" ).hide();
                    $('#receipt-search').html("<tr><td colspan='12' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
                    
                   },
                  success: function(data){ 
    
                    if(data != 0){
                        if(data.length >0){
                            $("#pagination").show();
                            var temp = $(data);
                             var paginate_info = temp.find('.pagination_info').clone();
                    temp.find('.pagination_info').remove();
                            var paginate = temp.find('#pagination_ajax').clone();
                            temp.find('#pagination_ajax').remove();
                            $('#receipt-search').html(temp);
                            $("#pagination" ).html(paginate); 
                              $( "#pagination_info" ).html(paginate_info);       
                            // $('.contract_search_field').trigger('blur'); 
                        }
                    }

                    var txt = '' ;

                      txt = 'receipts_generation_receipt_no='+ receipts_generation_receipt_no+'receipts_generation_receipt_date='+ receipts_generation_receipt_date+'&receipts_generation_eff_from='+receipts_generation_eff_from+'&receipts_generation_eff_to='+receipts_generation_eff_to+'&unit_no='+unit_no+'&building_name='+building_name+'&building_code='+building_code+
                         '&tenant_name='+ tenant_name+ '&tenant_code'+tenant_code+'&tenant_contract_no='+contract_no+'&receipts_generation_payment_method='+receipts_generation_payment_method+'&receipts_generation_amt='+receipts_generation_amt+'&receipts_generation_approval_status='+receipts_generation_approval_status+
                           '&tab='+tab;  

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
            },1000);

    });
  
});
</script>
 
 
