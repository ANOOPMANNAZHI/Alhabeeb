<script>
$(document).on('click','.assignLead',function(){       
        var allVals = []; 
        allVals.push($(this).attr('datas-id'));
        action =$(this).attr('data_ac_key');

        var workflow = $(this).attr('data-id');
        /*$('input:hidden[name=enquiryIds]').val(allVals);*/
        $.ajax({
          method: 'POST', // Type of response and matches what we said in the route
          url: "{{route('terminationGroupAssignModal')}}", // This is the url we gave in the route
          data: {'action':action,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
          success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
          },
        });
        return true; 
         
});
/***********************************************************/
$(document).on('click','.landlordRenew', function(e) {        

    var action_key =  $(this).attr('datas-id');
    var landlord_contract_id = $(this).attr('data-contract');
    var status = $(this).attr('data-id');
    var process_flow = $(this).attr('data-flow-id');
    /*if (confirm('Do you want to Approval Accept?')) {*/
    $.ajax({
        method: 'POST', // Type of response and matches what we said in the route
        url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
        data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
        success: function(response){ // What to do if we succeed
            //$("#myModal").html(response);
            //alert(response);
           window.location.href = response;
        },
    });
    return true;
    /*}else {
        return false;
    } */       
     
});
/***********************************************************/
$(document).on('click', '.Penalty',function(e) {        

    var terminationId =$(this).attr('data-id');
    

    /*if (confirm('Do you want to Reject?')) {*/
    $.ajax({
        method: 'POST', // Type of response and matches what we said in the route
        url: "{{route('tenantTerminationPenalty')}}", // This is the url we gave in the route
        data: {'terminationId' : terminationId,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
        success: function(response){ // What to do if we succeed
            
            $("#myModal").html(response);

            var dtToday = new Date();
                
            var month = dtToday.getMonth() + 1;
            var day = dtToday.getDate();
            var year = dtToday.getFullYear();
            if(month < 10)
                  month = '0' + month.toString();
            if(day < 10)
                  day = '0' + day.toString();
              
            var maxDate = year + '-' + month + '-' + day;
              
            //$('#tenant_penalty_start_date').attr('min', maxDate);
            //$('#tenant_penalty_valid_to_date').attr('min', maxDate);

            $('#tenant_penalty_start_date').attr('max', $('#fromdate_max').val());
            $('#tenant_penalty_valid_to_date').attr('max', $('#todate_max').val());
                
        },
    });
    return true;
    /*}else {
        return false;
    } */       
     
});
/*****Contract Value calculation with rent, Start date and Valid date*****/
    $(document).on('change',".tenant_penalty_valid_to_date", function(){

            var startDtOneMonth = 0;
            var remainEndDays = 0;
            var remainDaysInStartMonth = 0 ;
            var noOfDays =0;
            var endDtOneMonth = 0 ;
            var sumOfMonth = 0 ;
            var sumOfStartDays = 0;
            var sumOfRent = 0;
            var sumOfEndDays = 0;

            var tenant_contract_rent = $("#tenant_contract_rents").val();
            //alert(tenant_contract_rent);
            var start       = $("#tenant_penalty_start_date").val();
            var end         = $("#tenant_penalty_valid_to_date").val();
            
            var startObj    = new Date(start);
            var endObj      = new Date(end);

            startdt   = start.split('-');
            enddt     = end.split('-');
            // Entering date - Start date greater than end date return false
            if(Date.parse(startObj) > Date.parse(endObj)){

                return false;
            }

            var startDtMonthNext     = new Date(startObj.getFullYear(), startObj.getMonth()+1, 1);
            var prevEndMonthLastDate = new Date(endObj.getFullYear(), endObj.getMonth(), 0);


            var formatDateComponent = function(dateComponent) {
              return (dateComponent < 10 ? '0' : '') + dateComponent;
            };

            var formatDate = function(date) {
              return formatDateComponent(date.getDate()) + '-' +formatDateComponent(date.getMonth() + 1) + '-' + date.getFullYear();
            };
            // check no of day in this month
            var noOfDaysInStart   = daysInThisMonth(start);
            // Check this date day number is 1. If the condition is true take that full month as One month
            if(startdt[2] == 1 ){
                
                 startDtOneMonth       = 1;
            }
            // If this date day is greater than one take remaining days
            else if(startdt[2] > 1){ 
                // Month
                remainDaysInStartMonth  = getRemanningDays(start)+1;
                
            }

            // If end date is exist
            if(end){
                // Take no of days in this end date
                var noOfDaysInEnd    = daysInThisMonth(end);
                // If no of days is equal to end date day value Ie 30 == 30 Or 31 == 31. Consider as one month
                if(enddt[2] == noOfDays){
                    endDtOneMonth       = 1;
                }
                else{
                // Otherwise take day value Ie- 20-04-2019 - 20 days
                    remainEndDays  = enddt[2];
                }
                
            }
            // Check if it is same month
            if(startObj.getMonth() == endObj.getMonth() && startObj.getFullYear() == endObj.getFullYear() ){
               
                const diffTime = Math.abs(endObj.getTime() - startObj.getTime());
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
               
                sumRent  = (tenant_contract_rent/noOfDaysInStart) * diffDays; 

                $("#invoice_amount").val(sumRent.toFixed(3));

                return true;
            }
            // If start date less than end date
            if(Date.parse(startDtMonthNext) < Date.parse(prevEndMonthLastDate)){
                // start date next month till end date previous month last date(30 or 31)
                var noOfMonths = monthDiff(startDtMonthNext,prevEndMonthLastDate) + 1;
                
                if(noOfMonths > 0){
                        // Add consider as One month in start date and End date
                        noOfMonths = startDtOneMonth + endDtOneMonth + noOfMonths;
                        sumOfMonth = noOfMonths * tenant_contract_rent;

                }
                if(remainDaysInStartMonth > 0){
                        
                        sumOfStartDays  = (tenant_contract_rent/noOfDaysInStart) * remainDaysInStartMonth; 
                }
                if(remainEndDays > 0){
                        sumOfEndDays  = (tenant_contract_rent/noOfDaysInEnd) * remainEndDays; 
                }

                sumOfRent = sumOfMonth + sumOfStartDays + sumOfEndDays;
                 
            }else{

                if(remainDaysInStartMonth > 0){
                        
                        sumOfStartDays  = (tenant_contract_rent/noOfDaysInStart) * remainDaysInStartMonth; 
                }
                if(remainEndDays > 0){
                        sumOfEndDays  = (tenant_contract_rent/noOfDaysInEnd) * remainEndDays; 
                }
                noOfMonths = startDtOneMonth + endDtOneMonth;
                sumOfMonth = noOfMonths * tenant_contract_rent;
                sumOfRent  = sumOfStartDays + sumOfEndDays + sumOfMonth;        
            
            }
             $("#invoice_amount").val(sumOfRent.toFixed(3));
    }); 
    function getRemanningDays(start) {
            var date = new Date(start);
            var time = new Date(date.getTime());
            time.setMonth(date.getMonth() + 1);
            time.setDate(0);
            var days =time.getDate() > date.getDate() ? time.getDate() - date.getDate() : 0;
            return days;
            
    }
    function daysInThisMonth(noOfDaysInDt) {
      var now = new Date(noOfDaysInDt);
      return new Date(now.getFullYear(), now.getMonth()+1, 0).getDate();
    }
    function monthDiff(d1, d2) {
        var months;
        months = (d2.getFullYear() - d1.getFullYear()) * 12;
        months -= d1.getMonth() + 1;
        months += d2.getMonth();
        return months <= 0 ? 0 : months+1;
    }
 
/***************************************************************************************/
$("#myModal").on("hidden.bs.modal", function(){
      $("#myModal").html("");
      $(this).removeData('bs.modal');
  });

/***********************************************************/
</script>