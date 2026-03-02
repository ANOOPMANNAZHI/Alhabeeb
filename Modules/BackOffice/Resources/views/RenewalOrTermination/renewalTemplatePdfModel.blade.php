<div class="modal-dialog  modal-lg  assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Renewal Form</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
  <div class="modal-body">
    
   <div class="row">
    <div class="col">
      <div class="alert alert-danger">
        <div class="alert-dailog">

           @if (isset($tenantContract->email_count))
             Mail sent  {{$tenantContract->email_count}} Times 
           @else 
             Mail sent 0 Times
           @endif

           

        </div>
      </div>
        <div class="card card-box salesSearchBox">
        <form action="{{  route('tenantRenewalTemplateStore',$tenantContract->id)}}" autocomplete="off" method="POST" id="tenant_contract_form" class="form-horizontal"  data-toggle="validator">
        {{csrf_field()}}      
      

        @if(!empty($tenantContract->tenant->tenant_contact_email) || !empty($tenantContract->tenant->tenant_personal_email) ) 
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox data-bckclr">
            
           <div class="row">
            <div class="col-sm-6">
              <div class="form-group form-group-popup">
                 <label for="pdc_details_check_no">From</label>
                    <div class="p-relative">
                    <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                        <input  type="email" class="form-control" id="from_email"  name="from_email" readonly required  value="{{Auth::user()->email}}" >
                    </div>
              </div>
			  </div>

             <div class="col-sm-6">
              <div class="form-group form-group-popup">
                 <label for="pdc_details_check_no">To</label>
                    <div class="p-relative">
                    <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                        <input  type="email" class="form-control" id="to_email"  name="to_email" required  value="{{($tenantContract->tenant->tenant_contact_email)? $tenantContract->tenant->tenant_contact_email : $tenantContract->tenant->tenant_personal_email}}" >
                    </div>
              </div>
			  </div>
			  
			<div class="col-sm-12">  
            <div class="form-group form-group-popup">
                 <label for="pdc_details_check_no">Cc</label>
                    <div class="p-relative">
                    <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                        <input  type="text" class="form-control" id="cc_email"  name="cc_email" readonly value="{{Auth::user()->email.','.$areEmail}} " >
                    </div>
              </div>
            </div> 
               <div class="w-100"></div>
              </div>
        </div>


        <div class="dataSearchBox ">
           <div class="row">

            <div class="col-sm-12">
              <div class="form-group">
                 <label for="pdc_details_check_no"></label>
                  <textarea class="form-control mail_body ckeditor" row="10" height="10">
                  
                  Dear {{$tenantContract->tenant->tenant_name}},
                  
                  This is an official notice to inform you that your current lease agreement for
                  {{$tenantContract->building->building_name}} / {{$tenantContract->Unit->unit_no}} is due for renewal on {{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}. Accordingly, we enclosed
                  herewith Online Tenancy Contract Renewal Form for your signature.
               
                  You are requested to submit the online form duly signed along with rental
                  cheques and copies of ID card/company documents (as applicable) at our office,
                  which will enable us to proceed with Online registration at Muscat
                  Municipality.

                  We request you to finish the renewal process within a week’s time from the
                  receipt this mail. As you are aware that late registration will attract penalty and hence you are requested to complete the formalities well in time. 
                  You may contact the undersigned if you need any clarification on this regard.

                  Thank you for cooperation.

                  
                  ARE Signature
                 </textarea>    
                 
                    
              </div>
            </div>





            
           </div>
        </div>

       @endif

       @php

        $d1 = old('tenant_contract_start_date',isset($tenantContract->tenant_contract_valid_to_date)?  (  (isset($renewal_data))? date('Y-m-d', strtotime($renewal_data->tenant_contract_start_date)) : $start_date ): '');

        $d2 = old('tenant_contract_valid_to_date',isset($tenantContract->tenant_contract_valid_to_date)? ( (isset($renewal_data))? date('Y-m-d', strtotime($renewal_data->tenant_contract_valid_to_date)) : date('Y-m-d', strtotime($tenantContract->tenant_contract_valid_to_date->addYear())) ): '');

        $diff = abs(strtotime($d2)-strtotime($d1));


        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

        if($days <= 5){
          $days = 0;
        }

        if($months == 12){
          $months = 0;
          $years+=1;
        }

       

        @endphp
	   
	   
	   <div class="sub-head">Renewal Schedule Details</div>
    <div class="dataSearchBox data-bckclr">    
        <div class="card-body">
            
              <table >
                <tbody>
                  <tr>
                    <td >Rent(P.M.)</td>
                    <td> 
                       <div class="p-relative">
                       <i class="fa fa-money icn-add" aria-hidden="true"></i>
                       <input type="text" onkeyup="FormatCurrency(this)" class="form-control rent" id="rent" placeholder="Enter Rent (P.M)" name="tenant_contract_rent" value="{{ isset($tenantContract)?  old('tenant_contract_rent', (isset($renewal_data)? numberFormat($renewal_data->tenant_contract_rent): numberFormat($tenantContract->tenant_contract_rent)) ): old('tenant_contract_rent','')}}" required data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
                       </div>
                    </td>
					<td class="lft-align"> From Contract Period</td>
                     <td>
                      <div class="p-relative">
                      <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                      <input type="date" class="form-control" id="start_date" placeholder="Enter start date" name="tenant_contract_start_date" value="{{old('tenant_contract_start_date',isset($tenantContract->tenant_contract_valid_to_date)?  (  (isset($renewal_data))? date('Y-m-d', strtotime($renewal_data->tenant_contract_start_date)) : $start_date ): '')}}">
                    </div>
                     </td>
                  </tr>
                  
                   <tr> 
                     <td >To Contract Period</td>
                     <td>
                      <div class="p-relative">
                      <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                      <input type="date" class="form-control" id="valid_to_date" placeholder="Enter Valid To" name="tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date',isset($tenantContract->tenant_contract_valid_to_date)? ( (isset($renewal_data))? date('Y-m-d', strtotime($renewal_data->tenant_contract_valid_to_date)) : date('Y-m-d', strtotime($tenantContract->tenant_contract_valid_to_date->addYear())) ): '')}}" required>
                    </div>
                     </td>

                    
                      
					 <td class="lft-align">Payment Mode </td>
                    <td>
                      <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="tenant_contract_payment_type" required>
                    <option value="">Select Payment Term </option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',( (isset($renewal_data))? $renewal_data->tenant_contract_payment_type : $tenantContract->tenant_contract_payment_type) ) == '1')? 'selected' : '') : ''}} value="1" >Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type', ( (isset($renewal_data))? $renewal_data->tenant_contract_payment_type : $tenantContract->tenant_contract_payment_type ) ) == '2')? 'selected' : '') : ''}} value="2" >Bi-Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',( (isset($renewal_data))? $renewal_data->tenant_contract_payment_type :  $tenantContract->tenant_contract_payment_type) ) == '3')? 'selected' : '') : ''}} value="3" >Quarterly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',((isset($renewal_data))? $renewal_data->tenant_contract_payment_type :  $tenantContract->tenant_contract_payment_type) ) == '4')? 'selected' : '') : ''}} value="4" >Half Yearly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type', ((isset($renewal_data))? $renewal_data->tenant_contract_payment_type :  $tenantContract->tenant_contract_payment_type) ) == '5')? 'selected' : '') : ''}} value="5" >Yearly</option>
                </select>
            </div>
            <!-- {{$tenantContract->TenantContractPaymentName}} -->
          </td>
                  </tr>
                  <tr>
                     <td >To Contract Duration </td>
                     <td>
                      <div class="p-relative" >
                       
                       <span id="duration_contract">
                         {{$years}} Years {{$months}} Months {{$days}} Day
                       </span>
                       <a class="pull-right" href="#refresh" onclick="calculate_duration()"><i class="fa fa-refresh"></i></a>

                      </div>
                     </td>
                  </tr>

                 
                  <!-- <tr>
                    <td >Chqs Favoring</td>
                    <td>Al Habib & Co.</td>
                  </tr>  -->                                                      
                </tbody>
              </table>
         
          </div>
        </div>
	   
	   
	   
	   
	   

    <div class="sub-head">Building Details</div>
    <div class="dataSearchBox">
        <div class="card-body">
              <table class="table tbl-bg" >

                <tbody>
                  <tr>
                    <td class="lft-align"><b>Name</b></td>
                    <td class="lft-align">{{$tenantContract->building->building_name}}</td>
					<td class="lft-align"><b>Landlord</b></td>
                    <td class="lft-align">{{$tenantContract->building->vendor->vendor_name}}</td>
                  </tr>
                  <tr>
                    <td class="lft-align"><b>Building No.</b></td>
                    <td class="lft-align">{{$tenantContract->building->building_no}}</td>
                    <td class="lft-align"><b>Unit No</b></td>
                    <td class="lft-align">{{$tenantContract->unit->unit_no}}</td>
                  </tr>
                  <tr>
                    <td class="lft-align"><b>Way No</b></td>
                    <td class="lft-align">    {{$tenantContract->building->building_pc??''}}</td>
                    <td class="lft-align"><b>Plot No.</b></td>
                    <td class="lft-align">{{$tenantContract->building->plot_no}}</td>
                    </tr>
                    <tr>
                    <td class="lft-align"><b>Building Code</b></td>
                    <td class="lft-align"> {{$tenantContract->building->building_code}}</td>
                    <td class="lft-align"><b>Area</b></td>
                    <td class="lft-align">{{$tenantContract->building->google_location}}</td>
                  </tr> 
                                                                       
                </tbody>
              </table>
                    </div>   
        </div>
    




    <div class="sub-head">Tenant Details</div>
    <div class="dataSearchBox">
        <div class="card-body">
            
    <table class="table tbl-bg" >
    <tbody>
      <tr>
          <td class="lft-align"><b>Name</b></td>
          <td class="lft-align">{{$tenantContract->tenant->tenant_name}}</td>
          <td class="lft-align"><b>Contact Person</b></td>
          <td class="lft-align">{{$tenantContract->tenant->tenant_contact_person}}</td>
      </tr>
      <tr>
          <td class="lft-align"><b>Company</b></td>
          <td class="lft-align">{{$tenantContract->tenant->tenant_employer_name}}</td>
          <td class="lft-align"><b>Address</b></td>
          <td class="lft-align">{{$tenantContract->tenant->tenant_contact_address}}</td>
      </tr>
      <tr>
        <td class="lft-align"><b>PO Box No</b></td>
        <td class="lft-align">{{$tenantContract->tenant->tenant_post_box}}</td>
        <td class="lft-align"><b>PC No</b></td>
        <td class="lft-align">{{$tenantContract->tenant->tenant_pc}}</td>
      </tr> 
      <tr>
        <td class="lft-align"><b>Tel.</b></td>
        <td class="lft-align">   
        {{$tenantContract->tenant->tenant_residence_tel}} </td>
        <td class="lft-align"><b>Mobile No.</b></td>
        <td class="lft-align">{{$tenantContract->tenant->gsm_no}}</td>
      </tr>
      <tr>
	     <td class="lft-align"><b>GSM</b></td>
        <td >{{$tenantContract->tenant->tenant_contact_no}}</td>
        <td class="lft-align"><b>Email</b></td>
        <td class="lft-align">{{$tenantContract->tenant->tenant_contact_email?? $tenantContract->tenant->tenant_personal_email }}
        </td>
      </tr>                                                       
    </tbody>
  </table>
  </div>
</div>  
        

<div class="card-body">
  <table class="table" >
    <tbody>
      <tr>
        <td class="lft-align">Contact Person</td>
        <td class="lft-align">Tel.</td>
        <td class="lft-align">Email</td>
      </tr>
      <tr>
        <td class="lft-align" > {{(Auth::user()->user_type == 'admin')? ucwords(Auth::user()->username) : ucwords(Auth::user()->employee->employee_name) }}                                  </td>
        <td class="lft-align">{{(Auth::user()->user_type == 'admin')? '': ucwords(Auth::user()->employee->employee_contact_no) }}      </td>
        <td class="lft-align">{{Auth::user()->email}}</td>
      </tr>
                          
    </tbody>
  </table>
  
   <div class="row">
		   
                   <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">
                       @if(!empty($tenantContract->tenant->tenant_contact_email) || !empty($tenantContrcat->tenant->tenant_personal_email) ) Send Mail 
                       @else 
                        Send Mail 
                       @endif
                       <!-- (Download PDF) -->
                     </button>
                       
                   </div>
           </div>
  
  
</div>

        <div class="col-sm-12 text-right">
				
		</div>
        <div class="clearfix"></div>
        </form>
            
        </div>
    </div>
</div> 

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>

<script src="{{asset('vendor/ckeditor4/ckeditor.js')}} "></script> 
<script>
  
 $(document).ready(function(){ 

  var stdate = $("#start_date").val();
  var edate  = $("#valid_to_date").val();
 
  console.log(convertdate(new Date(stdate),new Date(edate)));

  //CKEDITOR.replace('mail_body');
  $(document).on("change","#start_date",function(){
    

            var start  = mindate  =  $('#start_date').val();   
            start.replace(/-/g, ",");      
            var end         = $("#valid_to_date").val();  

          var start = new Date(start);
          var oneYear = new Date(start.getFullYear() + 1, start.getMonth(), start.getDate());

          var year = oneYear.getFullYear();
          var month = ''+(oneYear.getMonth() + 1);
          var day = ''+oneYear.getDate();

     if (month.length < 2) month = '0' + month;
     if (day.length < 2) day = '0' + day;

   var  todate =  [year, month, day].join('-');

//-------  Landlord contract date compare with todate    ---------------
   
    @if(!empty($landlord_contract_valid_to_date) )    

       var end = new Date(maxDate.replace(/-/g, ","));
       var diff = new Date(end.getTime() - oneYear.getTime());
       var days = parseInt(Date.parse(diff))

         if(days > 0)         
           $('#valid_to_date').val(todate);    
       //else
       //   $('#valid_to_date').val('{{$landlord_contract_valid_to_date}}');

    @endif 
    
   $('#valid_to_date').attr('min', mindate);   

  });
});

//@if(!empty($landlord_contract_valid_to_date) )
//$('#valid_to_date').attr('max', '{{$landlord_contract_valid_to_date}}');
//@endif

$('#valid_to_date').attr('min', '{{$start_date}}');
$('#start_date').attr('min', '{{$start_date}}');

   function oneYearExpire(startDt){

    var str = startDt;

    var parts = str.split("-");

    var year = parts[0] && parseInt( parts[0], 10 );
    var month = parts[1] && parseInt( parts[1], 10 );
    var day = parts[2] && parseInt( parts[2], 10 );
    var duration = 1;

    if( day <= 31 && day >= 1 && month <= 12 && month >= 1 ) {

        var expiryDate = new Date( year, month - 1, day );
        expiryDate.setFullYear( expiryDate.getFullYear() + duration );

        var day = ( '0' + expiryDate.getDate() ).slice( -2 );
        var month = ( '0' + ( expiryDate.getMonth() + 1 ) ).slice( -2 );
        var year = expiryDate.getFullYear();
        var expDt = year+"-"+ month +"-"+day; 
        var date = new Date(expDt);
        date.setDate(date.getDate()-1);
        
        day = ( '0' + date.getDate()).slice( -2 );
        month = ( '0' + (date.getMonth()+1)).slice( -2 );
        year = date.getFullYear();

        dateYesterday = year + '-' + month+ '-' +day ;    
        return dateYesterday;

    } else {
        return str;
    }
  

}

function calculate_duration(){
  var stdate = $("#start_date").val();
  var edate  = $("#valid_to_date").val();

  var result = convertdate(new Date(stdate),new Date(edate));

  //console.log(result);

  $("#duration_contract").html(result);
}

$(document).on("focusout","#valid_to_datess",function(){

  var stdate = $("#start_date").val();
  var edate  = $("#valid_to_date").val();

  var result = convertdate(new Date(stdate),new Date(edate));

  console.log(result);

  // $("#duration_contract").html(result);

});

function convertdate(startingDate, endingDate) {



   var startDate = new Date(new Date(startingDate).toISOString().substr(0, 10));
  if (!endingDate) {
    endingDate = new Date().toISOString().substr(0, 10); // need date in YYYY-MM-DD format
  }
  var endDate = new Date(endingDate);
  if (startDate > endDate) {
    var swap = startDate;
    startDate = endDate;
    endDate = swap;
  }

  var startYear = startDate.getFullYear();
  var february = (startYear % 4 === 0 && startYear % 100 !== 0) || startYear % 400 === 0 ? 29 : 28;
  var daysInMonth = [30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30];

  var yearDiff = endDate.getFullYear() - startYear;
  var monthDiff = endDate.getMonth() - startDate.getMonth();
  if (monthDiff < 0) {
    yearDiff--;
    monthDiff += 12;
  }
  var dayDiff = endDate.getDate() - startDate.getDate();
  if (dayDiff < 0) {
    if (monthDiff > 0) {
      monthDiff--;
    } else {
      yearDiff--;
      monthDiff = 11;
    }
    dayDiff += daysInMonth[startDate.getMonth()];
  }


  
    // var months;

    // var final_ans;


    //   var diffYear =(dt2.getTime() - dt1.getTime()) / 1000;
    //   diffYear /= (60 * 60 * 24);
    //   var f_year = Math.abs(Math.round(diffYear/365.25));

    //   var diffMonth =(dt2.getTime() - dt1.getTime()) / 1000;
    //    diffMonth /= (60 * 60 * 24 * 7 * 4);
    //   var f_month = Math.abs(Math.round(diffMonth));

    //   // calculate the time difference of two dates JavaScript
    //   var diffTime =(dt2.getTime() - dt1.getTime());
  
    //   // calculate the number of days between two dates javascript
    //   var daysDiff = diffTime / (1000 * 3600 * 24); 

    //   console.log(daysDiff);

  

    //   var bb = calculateTimimg(daysDiff);

    //  console.log( yearDiff + 'Y ' + monthDiff + 'M ' + dayDiff + 'D');

      // if(parseInt(bb['days']) > 6){
      //    bb['days'] = 0;
      // }
      

      // if(parseInt(bb['months']) == 12){
      //     bb['months'] = 0;
      //     bb['years']+=1;
      // }
      // else{
      //    var bb = calculateTimimg(daysDiff);
      // }
      
      if(dayDiff <= 5){ 

        if(dayDiff == 30){
          return  yearDiff + 'Year ' + (monthDiff+1) + 'Months ' + 0 + 'Days';
        }else{
          return  yearDiff + 'Year ' + monthDiff + 'Months ' + 0 + 'Days';
        }

        

      }
      else{

        if(dayDiff == 30){
           return  yearDiff + 'Year ' + (monthDiff+1) + 'Months ' + 0 + 'Days';
        }else{
          return  yearDiff + 'Year ' + monthDiff + 'Months ' + dayDiff + 'Days';
        }

      }


      // final_ans = bb['years']+" Year "+bb['months']+" Months "+bb['days']+" Days";
      

}

const calculateTimimg = d => {
   let months = 0, years = 0, days = 0, weeks = 0;
   while(d){
      if(d >= 365){
         years++;
         d -= 365;
      }else if(d >= 30){
         months++;
         d -= 30;
      }else if(d >= 7){
         weeks++;
         d -= 7;
      }else{
         days++;
         d--;
         console.log('dayssss');
         console.log(days);
      }
   };
   return {
      years, months, weeks, days
   };
};

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
    months -= d1.getMonth()+1;
    months += d2.getMonth()+1;

    var months = (d2.getFullYear()-d1.getFullYear())*12+(d2.getMonth()-d1.getMonth());
    
    return months <= 0 ? 0 : months;
}



   

  /**********************************************************/
     jQuery.validator.addMethod("greaterThan", 
            function(value, element, params) {

                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }

                return isNaN(value) && isNaN($(params).val()) 
                    || (Number(value) > Number($(params).val())); 
            },'Must be greater than Start Date.');
    $("#tenant_contract_form").validate({
        rules: {
                tenant_contract_valid_to_date: { greaterThan: "#start_date"}
               
        },
        submitHandler: function(form) {
                  $('.save-contract').prop('disabled', true);
                  form.submit();
        }, 
    });
    
    jQuery.validator.addMethod("greaterThanEqual", 
        function(value, element, params) {

            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) >= new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) > Number($(params).val())); 
        },'Must be greater than or equal to Start Date.');
    // $("#tenant_contract_form").validate();





 // });

</script>
 
