<?php
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\Sales\Entities\TenantContract;
use Modules\Maintenance\Entities\ComplaintServiceReport;
use Modules\Masters\Entities\Unit;

// Laravel's config loader `require`s this file (not `require_once`) on every
// application boot. Tests\TestCase boots a fresh application per test method,
// so a test class with more than one test method re-requires this file in
// the same PHP process. Guard the function declarations so re-requiring is a
// no-op instead of a "Cannot redeclare" fatal error.
if (!defined('PLMS_FUNCTIONS_LOADED')) {
define("PLMS_FUNCTIONS_LOADED", true);
function prefixData($configuration_settings){

    return App\Setting::where('configuration_settings',$configuration_settings)->first();


}
   /*
    * Status
    *
    */
    function terminationTypeStatusClass($code){       
       
        switch($code){

             case 0:
              		 $class = 'label-primary';
               		   break;
             case 1: $class = 'label-info';
                       break;
             case 2: $class = 'label-general';
                       break; 
             case 3: $class = 'label-success';
                       break; 
             case 4: $class = 'label-danger';
                       break;                
             
             default : $class = 'label-danger';
                        break;             

        }

        return $class;
    } 



   function IsResubmitName($is_resubmit)
    {     
        switch($is_resubmit){
          
          case '1' : return 'No';  
          case '2' : return 'Yes';
    
        }
    }  




  /*
  *   Clear Notifications 
  *
  */ 
 function clearNotification($type,$textSearch = null,$delete_all = false){

     
 // if($delete_all){

	$notifications = \DB::table('notifications')
	 	                  ->whereNull('read_at')
	 	                  ->where('type',$type)
	 	                  ->when( (!empty($textSearch)), function($query)use($textSearch){
	                         $query->where('data','ilike', '%"id":'.$textSearch.',%');
	 	                  })
                          ->when( ($delete_all == false), function($query){
                             $query->where('notifiable_id',\Auth::user()->id);
                          })
	 	                  ->update(['read_at' => now()]);	 	                
 /* }else{
//notifiable_id
   $user_notification =  \Auth::user()->unreadNotifications; 
   $user_notification = $user_notification->where('type',$type) ;
      
      if($textSearch != null){
		  
		   $user_notification = $user_notification->filter(function($data, $key) use($textSearch){
					if ($data->data['id'] == $textSearch) {
						 return true;
					} 
			});	 
	        $user_notification->all();  
	  
	  }

     foreach ($user_notification as $notification) {
             $notification->markAsRead();
       }

     }  */     

  } 
  /*
  *   Read Notifications 
  *
  */ 
 function readNotification($type,$textSearch = null){

 	$notifications = \DB::table('notifications')
 	                  ->whereNull('read_at')
 	                  ->where('type',$type)->get();
 	
    //dd($notifications);
      if($textSearch != null){
		  	
		   $notifications = $notifications->filter(function($value, $key) use($textSearch){
           
          $data =  json_decode($value->data);

					if ($data->id == $textSearch) {
						 return true;
					} 
			});	 
	        $notifications->all();  
	  
	  }
   
       foreach ($notifications as $notification) {
       	
       		$notification = \DB::table('notifications')
 	                  ->where('id',$notification->id)->update(['read_at' => now()]);
       }


  }
  

 
  
  
  
  /*
   * Menu Selection Check 
   * 
   */
 function menuSelectionCheck($val,$key){
	  
	 
	 if($val->menutype == 1){		 
		 
		  $res =   $val->allChildMenu()->where('url_key', $key)->first();	
		  
		  if(empty($res)) {
			  			  
			  foreach($val->allChildMenu as $menuSub_val){
				  
				    if($menuSub_val->menutype == 2){					 
						  if($menuSub_val->url_key == $key)
						  return true;
					}else{
					$checkRes = menuSelectionCheck($menuSub_val,$key);
						if($checkRes){ 
						return true;	
					 }				
				  }				  
				}
			  }else			  
			  return true;			 
		}else{
			
			if($val->url_key == $key)
			return true;
			
			}
	// echo '<br>';
	 return false;
	 
	 }
	 /*
      *
      *
      * check Outstanding Amount
      *
      */
      function chekOutstanding($building)
      {
      	$flag = 0;
      	$date = '';
      	$tenantContracts = Modules\Sales\Entities\TenantContract::active()->where('building_id',$building)->get();
      	$buildingCheck = Modules\Masters\Entities\Building::where('id',$building)->first();
      	if($buildingCheck->management_id == 1) {
      		foreach($tenantContracts as $tenantContract){
	      		$tenant_contract_last_paid_date = $tenantContract->tenant_contract_last_paid_date;
		      	if(empty($tenant_contract_last_paid_date)){
			        $tenant_contract_last_paid_date = $tenantContract->tenant_contract_effective_date;
			        $paymentTerm = $tenantContract->tenant_contract_payment_type;
			        switch($paymentTerm){
			          case 1:
			            $month = 1;
			            break;
			          case 2:
			            $month = 2;
			            break;
			          case 3:
			            $month = 3;
			            break;
			          case 4:
			            $month = 6;
			            break;
			          case 5:
			            $month = 12;
			            break;
			        }
			        $futurePaymentDate = $tenant_contract_last_paid_date->addMonths($month)->format('Y-m-d');
			        if($futurePaymentDate < date('Y-m-d')){
			          $os = 'Yes' ;
			          //$date .= $futurePaymentDate.$tenantContract->id;
			          $flag += 1;
			        }else{
			          $os = 'No' ;
			        }

		      	}
	      	}
      	}
      	
      	return $flag;
      }
/*
      *
      *
      * Replace Comma With Space
      *
      */

function replaceCommaWithDot($amount){
    return (double)str_replace(',', '', $amount);
}



function replaceCommaWithSpace($amount){
    return str_replace(',', '', $amount);
}
/*
      *
      *
      * Change Number Format from Dot to Comma Separated
      *
      */
function numberFormat($amount){
    return number_format($amount,3);
}
/*

Rent Calculation with From Date and To Date 
Every Partial month to be consider as 30 days.

*/
function contractRentCountCalculation($date1, $date2, $rent){

	$date1 = date("Y-m-d", strtotime($date1));
	$date2 = date("Y-m-d", strtotime($date2));
	if(!is_numeric($rent) || empty($date1) || empty($date2)) return 0;
	// First day of the month.
	$firstDate  = strtotime(date('Y-m-01', strtotime($date1)));

	// Last day of the month.
	$lastDate   = strtotime(date('Y-m-t', strtotime($date2)));

	$startDate  = new \DateTime($date1);
	$endDate    = new \DateTime($date2);

	$noOfDaysStartMonth = date('t', strtotime($date1)); 
	$noOfDaysEnd    = date('t', strtotime($date2)); 

	$explodeStartDtValue  = explode("-",$date1);
	$explodeEndDtValue    = explode("-",$date2);

	//echo "First Date".$firstDate ."--".strtotime($date1); 
	//echo "Last Date".$lastDate ."--".strtotime($date2); 

	// exit;
	$sumOfStartDays = 0;
	$sumOfEndDays = 0;
	$sumOfSameStartEnd = 0;
	$monthIsOne = 1;
	$sumOfMonthRent = 0;
	$startDays = 0;
	$startDate->setTimestamp(strtotime($date1));
	$endDate->setTimestamp(strtotime($date2));

	if($firstDate === strtotime($date1) && $lastDate === strtotime($date2)){
		//echo $rent."<br>";
		$sumOfMonthRent = $monthIsOne * $rent;

	}
	else{

		if ($startDate->format('Y-m') === $endDate->format('Y-m')) {

		$startDays = $explodeEndDtValue[2] - $explodeStartDtValue[2] + 1;
		$sumOfStartDays  = ($rent/30) * $startDays;

		}
		else{

			//echo $explodeStartDtValue[2]; exit;
			if($explodeStartDtValue[2] < $noOfDaysStartMonth){

				$startDays = $noOfDaysStartMonth - $explodeStartDtValue[2] + 1;

				$sumOfStartDays  = ($rent/30) * $startDays;

			}
			if($explodeEndDtValue[2] < $noOfDaysEnd){

				$endDays = $explodeEndDtValue[2];
				$sumOfEndDays  = ($rent/30) * $endDays;

			}
		}
	}

	//$next_month_ts = strtotime($date1.' +1 month');
	//$prev_month_ts = strtotime($date2.' -1 month');

	$nextStartDt  = date('Y-m-d', strtotime(date('m', strtotime($date1.' +1 month')).'/01/'.date('Y')));
	$prevEndDt    = date('Y-m-d', strtotime($date2.' last day of previous month'));

	$startDateNew   =   new \DateTime($nextStartDt);
	$endDateNew     =   new \DateTime($prevEndDt);

	if ((strtotime($prevEndDt)) > (strtotime($nextStartDt))){

		$interval   = $endDateNew->diff($startDateNew);
		$monthIsOne = $interval->format('%m') + 1;

		$sumOfMonthRent = $monthIsOne * $rent;

		$sumOfMonthRent = $sumOfMonthRent + $sumOfStartDays + $sumOfEndDays; 

	}
	else{

		$sumOfMonthRent = $sumOfMonthRent + $sumOfStartDays + $sumOfEndDays;

	}
	return number_format((float)$sumOfMonthRent, 3, '.', '');

}
// Rent loss calculation with dats
function rentLossCalculationWithDays($days, $rent){

	$monthRent = floor($days/30)*$rent;
	$remaindays = floor($days%30);
	$remainRent = 0;
	if($remaindays > 0)
		$remainRent = ($remaindays * $rent)/30; 
	
	$sumRent = $monthRent + $remainRent;
	
	return number_format((float)$sumRent, 3, '.', '');
	
}
function contractDurationCalculation($date1, $date2){

	$datetime3 = new DateTime($date1);

	$datetime4 = new DateTime(date('Y-m-d', strtotime($date2 .' +1 day')));

	$difference = $datetime3->diff($datetime4);

    return array("year"=>$difference->y,"month"=>$difference->m,"day"=>$difference->d);
}
function rentCalcualtionWithDuration($duration, $rent){

	$totalRent = 0;
	if($duration['year'] > 0){

		$totalMonth = $duration['year'] * 12;		
		$totalRent +=  $totalMonth * $rent;
	}
	if($duration['month'] >0){

		$totalRent += $duration['month'] * $rent;
	}
	if($duration['day'] > 0){

		$totalRent += floatval($rent/30) * $duration['day'];
	}

	return $totalRent;
}  
function dateDuration($date1, $date2){

	$date1 = date("Y-m-d", strtotime($date1));
	$date2 = date("Y-m-d", strtotime($date2));

	$date1 = new \DateTime($date1);
	$date2 = $date1->diff(new \DateTime($date2 ));

	return $date2->y.'-'.$date2->m.'-'.$date2->d;
}
//returns 3 month
function dateDifference($date_1 , $date_2 , $differenceFormat = '%m' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    
    $interval = date_diff($datetime1, $datetime2);
    
    return $interval->format($differenceFormat);
    
}
//SMS
function sendSms($mobile,$msg,$params){
	// 1 - Enable, 2 - Disable
	if(SMS_ENABLE_DISABLE==2)
		return false;
	try {
		$http = new GuzzleHttp\Client(['verify'=>false]);
		$response = $http->post(SMS_URL, [
		   'json' => [
			   'UserName' => SMS_USERNAME,
			   'Password' => SMS_PASSWORD,
			   'Message' => $msg,
			   'Priority' => "1",
			   'SourceRef' => 'PLMS',
			   'MSISDNs' => $mobile ,
			   'Sender' => 'PLMS'
		   ],
				'http_errors' => false
		]);

		$res =  json_decode((string) $response->getBody(), true);

		if (!$res || !isset($res['StatusCode'])) {
			\Log::error('sendSms: Unexpected API response', ['mobile' => $mobile, 'body' => (string) $response->getBody()]);
			return false;
		}

		return ($res['StatusCode'] == '00')? true : false ;
	} catch (\Exception $e) {
		\Log::error('sendSms: Exception - ' . $e->getMessage(), ['mobile' => $mobile]);
		return false;
	}
}
//get total receivables


function getReceivables($fromDate , $validToDate, $nextDays,$tenantContractRent,$tenantContractId){
	$todate = date('Y-m-d',strtotime($validToDate));
	$ts1 = strtotime($fromDate);
	

	// if($todate >= $nextDays)
	// 	$ts2 = strtotime($nextDays);
		// $countMonth = dateDifference($fromDate , $nextDays);

	// else
		$ts2 = strtotime($validToDate);
		// $countMonth = dateDifference($fromDate , $validToDate);

	
	

	$year1 = date('Y', $ts1);
	$year2 = date('Y', $ts2);

	$month1 = date('m', $ts1);
	$month2 = date('m', $ts2);

	$diff = (($year2 - $year1) * 12) + ($month2 - $month1);


	$totalRent = $diff * $tenantContractRent;

	$sumOfReceipt = 0;

	$sumOfReceipt   = ReceiptsGeneration::where('receipts_generation_type',0)->where('tenant_contract_id',$tenantContractId)->where('receipts_generation_status',3)->sum('receipts_generation_amt');

	$totalRentAmount = $totalRent-$sumOfReceipt;


	return numberFormat($totalRentAmount); 
}

	//Days Difference
function getDaysDifference($date1,$date2){
	$date1 = date_create($date1);
	$date2 = date_create($date2);
	$diff = date_diff($date1,$date2);

//count days
	return $diff->format("%a");
}
	//get Penalty Days
function getPenaltyDays($tenantContractId,$tenantContractValidToDate){
	$today = date('Y-m-d');
	$penaltyDay = Null;
	if($tenantContractValidToDate < $today){
		$daysDiff =getDaysDifference($today,$tenantContractValidToDate);
		$penaltyDayStart = $tenantContractValidToDate->addDays(31);
		if($today < $penaltyDayStart){
			$penaltyDay = getDaysDifference($today,$penaltyDayStart);
		}else{
			$penaltyDay = 'Under Penalty';
		}
	}
	else{
		$penaltyDay = 'NA';
	}
	return $penaltyDay;


}
function getOccupiedUnits($tenantContractId){
   $tenantContract =  TenantContract::where('id',$tenantContractId);
   $getUnits = $tenantContract->pluck('unit_id');
        $occuipied_unit = Unit::whereIn('id',$getUnits)->where('unit_vaccant_status',1)->pluck('id');
        $occuipied_units = count($occuipied_unit);
        return $occuipied_units;
}
function numberToWords($amt){
 	
 	$f = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
 	return $f->format($amt); 
}
function getLogoPath(){
$domain=  $_SERVER['HTTP_HOST'];

if($domain == '134.0.205.114:9443'){
$logo = 'public/img/logo-1.png'; //path for http://134.0.205.114:9081/
}else{
$logo = asset('public/img/logo-1.png');  //path for http://plms.alhabib.om:8086/
}
return $logo;
}

function getSignaturePath(){
$domain=  $_SERVER['HTTP_HOST'];

if($domain == '134.0.205.114:9443'){
	$logo = 'http://plms.alhabib.om:8086/storage/app/public/TenantSignature/'; //path for http://134.0.205.114:9081/
}else{
	$logo = 'http://plms.alhabib.om:8086/storage/app/public/TenantSignature/';  //path for http://plms.alhabib.om:8086/
}
return $logo;
}

function getAddressPath(){
$domain=  $_SERVER['HTTP_HOST'];

if($domain == '134.0.205.114:9443'){
$address = 'public/img/address.png';;           //change the path for http://134.0.205.114:9081/
}else{
$address = asset('public/img/address.png');                                        //change the path for http://plms.alhabib.om:8086/
}
return $address;
}

function getReportUrl(){
$domain=  $_SERVER['HTTP_HOST'];

if($domain == '134.0.205.114:9443'){
$path = 'http://134.0.205.114:9443/';
}elseif(strpos($domain, 'localhost') !== false || strpos($domain, '127.0.0.1') !== false){
$path = url('/') . '/';
}else{
$path = 'http://plms.alhabib.om:8086/';
}
return $path;
}

function totalContractRentCountCalculation($contractId,$terminationDate){
$tenantContract =  TenantContract::where('id',$contractId)->first();
$effectiveDate = $tenantContract->tenant_contract_effective_date;
$rentPerMonth = $tenantContract->tenant_contract_rent;
$rentPerDay = $rentPerMonth/30;

$diff = date_diff(date_create($effectiveDate),date_create($terminationDate));


     //count Months
$monthsDiff = $diff->format("%m");
//count days remaining
$daysDiff = $diff->format("%d");

$rentMonth = $rentPerMonth*$monthsDiff;

$rentDay = $rentPerDay*$daysDiff;

$sumOfRent = $rentMonth+$rentDay;



return number_format((float)$sumOfRent, 3, '.', '');
}
}



