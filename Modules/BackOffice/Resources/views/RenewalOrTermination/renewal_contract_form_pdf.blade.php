<!DOCTYPE html>
<html>
<head>
	<title>Renewal-Form-Pdf</title>
	<style>
		table, th, td {
			border: 1px solid black;
		}
	</style>
	<style>
		footer .pagenum:before {
			content: counter(page);
		}
		@page { margin: 50px 50px 50px 50px; }
		header{ position:relative; margin:auto; float: left; top: 0; bottom: 60%; width: 100%;}
		footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; }

		table, th, td {
			border: thin solid #d0d0d0;
			padding: 2px;
			background:#fff;
		}
		table{
			width: 100%;
			background: #e1e9ef;
		}
	</style>
</head>
<body>
	<input type="hidden" value="{{$tenantContract->tenantRenewalFormLatest->tenant_contract_start_date}}" id="d1">
	<input type="hidden" value="{{$tenantContract->tenantRenewalFormLatest->tenant_contract_valid_to_date}}" id="d2">
	<div style="page-break-inside: avoid;">
		<div class="page-logo">
		<span class="logo-default" >
		<img alt="" src="../public/img/logo-1.png"></span>
	</div>
	<span class="pull-right" style="float: right;"><?php echo date('d/m/Y');?></span>
	<h4 align="center"><u>ONLINE LEASE CONTRACT RENEWAL FORM</u></h4>
	<b>Building Details</b>
	<table class="table" >
		<tbody>
			<tr>
				<td>Name</td>
				<td  colspan='5'>{{$tenantContract->building->building_name}}</td>
			</tr>
			<tr>
				<td >Landlord</td>
				<td  colspan='5'>{{$tenantContract->building->vendor->vendor_name}}</td>
			</tr>
			<tr>
				<td >Building No.</td>
				<td  colspan='5'>{{$tenantContract->building->building_no}}</td>
			</tr>
			<tr>
				<td >Unit No</td>
				<td  colspan='5'>{{$tenantContract->unit->unit_no}}</td>
			</tr> 
			<tr>
				<td>Way No</td>
				<td>{{$tenantContract->building->building_pc??''}}</td>
				<td>Plot No.</td>
				<td>{{$tenantContract->building->plot_no}}</td>
				<td>Building Code</td>
				<td> {{$tenantContract->building->building_code}}</td>
			</tr> 
			<tr>
				<td>Area</td>
				<td colspan='5'>{{$tenantContract->building->google_location}}</td>
			</tr>                                                       
		</tbody>
	</table>
</div>
<br/>
<div style="page-break-inside: avoid;">
<b>Tenant Details</b>
	<table class="table" >
		<tbody>
			<tr>
				<td style="width:50px">Name</td>
				<td colspan='5' style="width:50px">{{$tenantContract->tenant->tenant_name}}</td>
			</tr>
<!-- 			<tr>
				<td style="width:50px" >Contact </td>
				<td colspan='5' style="width:50px" >{{$tenantContract->tenant->tenant_contact_person}}</td>
			</tr> -->
			<tr>
				<td style="width:50px" >Company</td>
				<td colspan='5' style="width:50px">{{$tenantContract->tenant->tenant_employer_name}}</td>
			</tr>
			<tr>

				<td style="width:50px">Address</td>
				<td style="width:75px">{{$tenantContract->tenant->tenant_contact_address}}</td>
				<td style="width:35px">PO Box</td>
				<td style="width:50px">{{$tenantContract->tenant->tenant_post_box}}</td>
				<td style="width:35px">PC No</td>
				<td style="width:50px">{{$tenantContract->tenant->tenant_pc}}</td>
			</tr> 
			<tr>
				<td style="width:50px">Tel</td>
				<td style="width:75px">{{$tenantContract->tenant->tenant_residence_tel}}</td>
				<td style="width:50px">Office</td>
				<td style="width:50px">{{trim($tenantContract->tenant->gsm_no)}}</td>
				<td style="width:35px">Mobile</td>
				<td style="width:50px">{{trim($tenantContract->tenant->tenant_contact_no)}}</td>
			</tr>
			<tr>
				<td style="width:50px">Email</td>
				<td  colspan='5'>{{$tenantContract->tenant->tenant_contact_email?? $tenantContract->tenant->tenant_personal_email }}
				</td>
			</tr>                                                         
		</tbody>
	</table>
</div>
<div style="page-break-inside: avoid;">
	<br/><b>Renewal Schedule Details</b>
	<table class="table" >
		<tbody>
			<tr>
				<td>Rent(P.M.)</td>
				<td>{{$tenantContract->tenantRenewalFormLatest->tenant_contract_rent}} OMR</td>
			</tr>
			<tr>
				<td>From Contract Period</td>
				<td>{{$tenantContract->tenantRenewalFormLatest->tenant_contract_start_date->format('d/m/Y')}}</td>
			</tr>
			<tr>
				<td>To Contract Period</td>
				<td>{{$tenantContract->tenantRenewalFormLatest->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
			</tr>
			<tr>
				<td >Lease Period</td>
								
				<td id="duration">
					@php

					$d1=date('Y-m-d', strtotime($tenantContract->tenantRenewalFormLatest->tenant_contract_start_date));
                    $d2=date('Y-m-d', strtotime($tenantContract->tenantRenewalFormLatest->tenant_contract_valid_to_date));

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
					
					{{$years}} Years {{$months}} Months and {{$days}} Day
				 </td>							
			</tr>
			<tr>
				<td >Payment Mode.</td>
				<td>{{$tenantContract->tenantRenewalFormLatest->TenantContractPaymentName}}</td>
			</tr>
			<tr>
				<td >Chqs Favoring</td>
				<td>Al Habib & Co. LLC</td>
			</tr>                                                       
		</tbody>
	</table>
</div>
<div style="page-break-inside: avoid;">
	<h4 class="m-b-0"></h4>
	<h4 class="m-b-0" ></h4>
	<table class="table" >
		<tbody>
			<tr>
				<td>Contact Person</td>
				<td>Tel.</td>
				<td>Email</td>
			</tr>
			<tr>
				<td >{{(Auth::user()->user_type == 'admin')? ucwords(Auth::user()->username) : ucwords(Auth::user()->employee->employee_name) }} </td>
				<td>{{(Auth::user()->user_type == 'admin')? '': ucwords(Auth::user()->employee->employee_contact_no) }}</td>
				<td>{{Auth::user()->email}}</td>
			</tr>
			                                                      
		</tbody>
	</table>
</div>
<div style="page-break-inside: avoid;">
	<div style="float: left;width:60%">
	<br/>
	<br/>
	<br/>
	<br/>
		<h4 class="m-b-0">---------------------------------------------------- 
		</h4>
		<h4 class="m-b-0" >Signature Of Tenant/Authorized</h4>
	</div>

	<div style="float: left;width:40%">
	<br/>
	<br/>
	<br/>
	<br/>
		<h4 class="m-b-0">---------------------------------------------------- 
		</h4>
		<h4 class="m-b-0" >For Al Habib &amp; Co LLC
	(Property Leasing &amp; Management)</h4>
		
	</div>
</div>
<script type="text/javascript">
	$( document ).ready(function() {

		var d1 = $("#d1").val();
		var d2 = $("#d2").val();
	    console.log(convertdate(d1,d2));
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
</script>

</body>
</html>