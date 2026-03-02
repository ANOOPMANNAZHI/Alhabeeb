<?php
namespace App\Helpers;
use Artisaninweb\SoapWrapper\SoapWrapper;

class DynamicsFunctions
{
	/*
	 * VENDOR INSERTATION
	 */
	public static function VendorAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
				
		$params_simple = array(
				'VendAccount' =>$params->vendor_code,
				'CustName' =>$params->vendor_name,
				'CountryRegionCode'=> 'OMN',
				'ContactDescription' =>isset($params->vendor_contact_address)? $params->vendor_contact_address:'',
				'City' => '',
				'LocationName' =>isset($params->vendorLocation->locations_name)?$params->vendorLocation->locations_name:'',
				'Street' => '',
				'ZipCode' => $params->vendor_pc,
				'Phone' => $params->vendor_contact_no,
				'VendGroup' => VENDOR_GROUP_CONST,
				'DirpartyType'=> DIRTY_PARTY_TYPE,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call('AXVendor.CreateVendor', [$params_simple]);
		return ($response->CreateVendorResult==$params->vendor_code)?'Success':'Error';
	}
	/*
	 * Vendor is Exist or not
	 */
	public static function VendorIsExitAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'VendAccount' =>$params->vendor_code,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.IsVendorExist', [$params_simple]);
		return $response->IsVendorExistResult;
	}
	/*
	 * VENDOR EDIT
	 */
	public static function VendorAxUpdatePushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
				
		$params_simple = array(
				'VendAccount' =>$params->vendor_code,
				'CustName' =>$params->vendor_name,
				'CountryRegionCode'=> 'OMN',
				'ContactDescription' =>isset($params->vendor_contact_address)? $params->vendor_contact_address:'',
				'City' => '',
				'LocationName' =>isset($params->vendorLocation->locations_name)?$params->vendorLocation->locations_name:'',
				'Street' => '',
				'ZipCode' =>isset($params->vendor_pc)? $params->vendor_pc:'',
				'Phone' => isset($params->vendor_contact_no)?$params->vendor_contact_no:'',
				'VendGroup' => VENDOR_GROUP_CONST,
				'DirpartyType'=> DIRTY_PARTY_TYPE,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call('AXVendor.UpdateVendor', [$params_simple]);
		return ($response->CreateVendorResult==$params->vendor_code)?'Success':'Error';
	}
	/*
	 * TENANT INSERTATION
	 */
	public static function TenantAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						 
					 ]);
				}); 
				
		$params_simple = array(
				'CustAccount' =>$params->tenant_code,
				'CustName' =>$params->tenant_name,
				'CountryRegionCode'=>isset($params->nationality->countries_code)?$params->nationality->countries_code:'',
				'ContactDescription' => $params->vendor_contact_address,
				'City' =>'',
				'LocationName' =>isset($params->vendorLocation->locations_name)?$params->vendorLocation->locations_name:'',
				'Street' => '',
				'ZipCode' => $params->tenant_pc,
				'Phone' => $params->tenant_contact_no,
				'VendGroup' => TENANT_GROUP_RENTAL_CONST,
				'DirpartyType'=> DIRTY_PARTY_TYPE,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.CreateCustomer', [$params_simple]);
		return ($response->CreateCustomerResult==$params->vendor_code)?'Success':'Error';
	}
	/*
	 * TENANT is Exist or not
	 */
	public static function TenantIsExitAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'CustAccount' =>$params->tenant_code,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);
		
		$response = $soapWrapper->call($serviceName.'.IsCustomerExist', [$params_simple]);
		return $response->IsCustomerExistResult;
	}
	/*
	 * TENANT is Exist or not
	 */
	public static function TenantAxUpdatePushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'CustAccount' =>$params->tenant_code,
				'CustName' =>$params->tenant_name,
				'CountryRegionCode'=>$params->nationality->countries_code,
				'ContactDescription' => $params->vendor_contact_address,
				'City' =>'',
				'LocationName' =>isset($params->location->locations_name)?$params->location->locations_name:'',
				'Street' => '',
				'ZipCode' => $params->tenant_pc,
				'Phone' => $params->tenant_contact_no,
				'VendGroup' => TENANT_GROUP_RENTAL_CONST,
				'DirpartyType'=> DIRTY_PARTY_TYPE,
				'DataAreaId'=>DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.UpdateCustomer', [$params_simple]);
		return $response->UpdateCustomerResult;
	}
	/*
	 * EMPLOYEE INSERTATION
	 */
	public static function EmployeeAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'EmpCode' =>$params->employee_code,
				'EmpName'=>$params->employee_name,
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.CreateEmployee', [$params_simple]);
		return ($response->CreateEmployeeResult==$params->employee_code)?'Success':'Error'; 
	}
	/*
	 * BUILDING INSERTATION
	 */
	public static function BuildingAxPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'BuildingCode' =>$params->building_code,
				'BuildingDescription'=>$params->building_name,
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.CreateBuilding', [$params_simple]);
		return ($response->CreateBuildingResult==$params->building_code)?'Success':'Error'; 
	}
	/*
	 * Maintenance Invoice Booking Header
	 */
	public static function MaintenanceInvoiceAxHeaderPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'JournalName' =>$params['JournalName'],
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.APInvoiceJournal', [$params_simple]);
		return ($response->APInvoiceJournalResult)?$response->APInvoiceJournalResult:'Error'; 
	}
	/*
	 * Maintenance Invoice Booking Line Item
	 */
	public static function MaintenanceInvoiceAxLineItemPushData($serviceName, $params=array()){
		
		foreach($params as $lineItem){
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			
			$params_simple =array(
						'JournalNum'=> $lineItem['JournalNum'],
						'JournalName'=>$lineItem['JournalName'],
						'PaymentDate'=>$lineItem['PaymentDate'],
						'Description'=>$lineItem['Description'],
						'vendAccount'=>$lineItem['vendAccount'],
						'currency'=>$lineItem['currency'],
						'accountType'=> $lineItem['accountType'],
						'paymentMethod'=>$lineItem['paymentMethod'],
						'checkBookid'=>$lineItem['checkBookid'],
						'documentNo'=> $lineItem['documentNo'],
						'AmountCredit'=>$lineItem['AmountCredit'],
						'voucher'=>$lineItem['voucher'],
						'AmountDebit'=> $lineItem['AmountDebit'],
						'Remarks'=>$lineItem['Remarks'],
						'Invoice'=>$lineItem['Invoice'],
						'dimension1'=>$lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=>$lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY
			);
			
			$response = $soapWrapper->call($serviceName.'.APInvoiceJournal_Line', [$params_simple]);
			
			if(empty($response->APInvoiceJournal_LineResult))
				return ($response->APInvoiceJournal_LineResult)?$response->APInvoiceJournal_LineResult:'Error';
			
		}
		return ($response->APInvoiceJournal_LineResult)?$response->APInvoiceJournal_LineResult:'Error'; 
	}
	/*
	 * Header - General Ledger, Bank payment, Landlord Invoice, Bank receipt
	 */
	public static function LedgerAxHeaderPushData($serviceName, $params=array()){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'JournalName' =>$params['JournalName'],
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.GLGeneralJournal', [$params_simple]);
		return ($response->GLGeneralJournalResult)?$response->GLGeneralJournalResult:'Error'; 
	}
	/*
	 * Line Item - General Ledger, Bank payment, Landlord Invoice, Bank receipt.
	 */
	public static function LedgerAxLineItemPushData($serviceName, $params=array()){
		
		foreach($params as $lineItem){
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			
			$params_simple =array(
						'JournalNum'=> $lineItem['JournalNum'],
						'JournalName'=>$lineItem['JournalName'],
						'PaymentDate'=>$lineItem['PaymentDate'],
						'Description'=>$lineItem['Description'],
						'Account'=>trim($lineItem['Account']),
						'currency'=>$lineItem['currency'],
						'accountType'=> trim($lineItem['accountType']),
						'paymentMethod'=>$lineItem['paymentMethod'],
						'checkBookid'=>$lineItem['checkBookid'],
						'documentNo'=> $lineItem['documentNo'],
						'DebitCredit'=>$lineItem['DebitCredit'],
						'voucher'=>$lineItem['voucher'],
						'AmountCredit'=>$lineItem['AmountCredit'],
						'AmountDebit'=> $lineItem['AmountDebit'],
						'Remarks'=>$lineItem['Remarks'],
						'Invoice'=>$lineItem['Invoice'],
						'dimension1'=>$lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=>$lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'DataAreaId'=>$lineItem['DataAreaId'],
						'company'=>$lineItem['company'],
			);			
			
			$response = $soapWrapper->call($serviceName.'.GLGeneralJournal_Line', [$params_simple]);



			if(empty($response->GLGeneralJournal_LineResult)){
				
				return ($response->GLGeneralJournal_LineResult)?$response->GLGeneralJournal_LineResult:'Error'; 
				
			}
		}
		
		//print_r(json_encode($response));exit();
		return ($response->GLGeneralJournal_LineResult)?$response->GLGeneralJournal_LineResult:'Error';
	}
	/*
	 * Header - Landlord Payment Journal
	 */
	public static function PaymentJournalAxHeaderPushData($serviceName){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'JournalName' =>PAYMENT_JOURNAL_NAME,
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.APPaymentJournal', [$params_simple]);
		return ($response->APPaymentJournalResult)?$response->APPaymentJournalResult:'Error'; 
	}
	/*
	 * Line Item - Landlord Payment Journal.
	 */
	public static function PaymentJournalAxLineItemPushData($serviceName, $params=array()){
		
		foreach($params as $lineItem){
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			
			$params_simple =array(
						'JournalNum'=> $lineItem['JournalNum'],
						'JournalName'=>$lineItem['JournalName'],
						'PaymentDate'=>$lineItem['PaymentDate'],
						'Description'=>$lineItem['Description'],
						'vendAccount'=>$lineItem['vendAccount'],
						'currency'=>$lineItem['currency'],
						'accountType'=> $lineItem['accountType'],
						'paymentMethod'=>$lineItem['paymentMethod'],
						'checkBookid'=>$lineItem['checkBookid'],
						'documentNo'=> $lineItem['documentNo'],
						'DebitCredit'=>$lineItem['DebitCredit'],
						'voucher'=>$lineItem['voucher'],
						'Amount'=>$lineItem['Amount'],
						'Remarks'=>$lineItem['Remarks'],
						'Invoice'=>$lineItem['Invoice'],
						'dimension1'=>$lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=>$lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'PostingProfile'=>'',
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY
			);
			//dd($params_simple);

			$response = $soapWrapper->call($serviceName.'.APPaymentJournal_Line', [$params_simple]);
			if(empty($response->APPaymentJournal_LineResult))
				return ($response->APPaymentJournal_LineResult)?$response->APPaymentJournal_LineResult:'Error'; 
		}
		return ($response->APPaymentJournal_LineResult)?$response->APPaymentJournal_LineResult:'Error'; 
	}
	/*
	 * Header - Tenant Rent Receipt- Comprehensive - ARPaymentJournal, Maintenace Payment
	 */
	public static function ReceiptPaymentJournalAxHeaderPushData($serviceName){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'JournalName' => AR_PAYMENT_JOURNAL_NAME,
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
		);

		$response = $soapWrapper->call($serviceName.'.ARPaymentJournal', [$params_simple]);
	
		return ($response->ARPaymentJournalResult)?$response->ARPaymentJournalResult:'Error'; 
	}
	/*
	 * Line Item - Tenant Rent Receipt- Comprehensive - ARPaymentJournal, Maintenace Payment ReceiptPaymentJournalAxLineItemPushData
	 */
	public static function ReceiptPaymentJournalAxLineItemPushData($serviceName, $params=array()){
		
		
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			$lineItem = $params;

			$params_simple =array(
						'JournalNum'=> $lineItem['JournalNum'],
						'JournalName'=>$lineItem['JournalName'],
						'PaymentDate'=>$lineItem['PaymentDate'],
						'Description'=>$lineItem['Description'],
						'CustAccount'=>$lineItem['CustAccount'],
						'currency'=>$lineItem['currency'],
						'accountType'=> $lineItem['accountType'],
						'paymentMethod'=>$lineItem['paymentMethod'],
						'checkBookid'=>$lineItem['checkBookid'],
						'documentNo'=> $lineItem['documentNo'],
						'DebitCredit'=>$lineItem['DebitCredit'],
						'voucher'=>$lineItem['voucher'],
						'Amount' =>$lineItem['Amount'],
						'Remarks'=>$lineItem['Remarks'],
						'Invoice'=>$lineItem['Invoice'],
						'dimension1'=>$lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=>$lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY
			);
			

			//print_r(json_encode($params_simple));exit();

			$response = $soapWrapper->call($serviceName.'.ARPaymentJournal_Line', [$params_simple]);

			//print_r(json_encode($response));exit();

			return ($response->ARPaymentJournal_LineResult)?$response->ARPaymentJournal_LineResult:'Error'; 
	
	}
	/*
	 * Header - ARFreeTextInvoice - Tenant Invoice
	 */
	public static function TenantInvoiceAxHeaderPushData($serviceName,$params=null){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'InvoiceId'=> $params['InvoiceId'],
				'custAccount'=>$params['custAccount'],
				'InvoiceAccount' =>$params['InvoiceAccount'],
				'InvoiceDate'=>$params['InvoiceDate'],
				'currency'=>$params['currency'],
				'DataAreaId' =>$params['DataAreaId'],
				'company' =>$params['company'],
		);
	
		$response = $soapWrapper->call($serviceName.'.ARFreeTextInvoice', [$params_simple]);
		
		return ($response->ARFreeTextInvoiceResult)?$response->ARFreeTextInvoiceResult:'Error'; 
	}
	/*
	 * Line Item - ARFreeTextInvoice - Tenant Invoice
	 */
	public static function TenantInvoiceAxLineItemPushData($serviceName, $params=array()){
	
		foreach($params as $lineItem){
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			
			$params_simple =array(
						'recid'=>$lineItem['recid'],
						'Amount'=>$lineItem['Amount'],
						'unitprice'=>$lineItem['unitprice'],
						'quantity'=> $lineItem['quantity'],
						'account'=>$lineItem['account'],
						'InvoiceDate' =>$lineItem['InvoiceDate'],
						'Description' =>$lineItem['Description'],
						'currency'=> $lineItem['currency'],
						'dimension1'=> $lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=> $lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'DataAreaId'=>$lineItem['DataAreaId'],
						'company'=>$lineItem['company'],
			);
			
			$response = $soapWrapper->call($serviceName.'.ARFreeTextInvoice_Line', [$params_simple]);
			
			$result = ($response->ARFreeTextInvoice_LineResult)?$response->ARFreeTextInvoice_LineResult:'Error';
			
			if($result=='Error')
				return 'Error';
		}
		return $response->ARFreeTextInvoice_LineResult; 
	}
	/*
	 * Header - APInvoiceRegister - Landlord Invoice & cost Recognition
	 */
	public static function LandlordInvoiceRegisterAxHeaderPushData($serviceName,$params=null){
		
		$soapWrapper = new SoapWrapper ;
		$soapWrapper->add($serviceName, function ($service) {
				$service
					->wsdl(AX_URL)
					->cache(WSDL_CACHE_NONE)
					->trace(true)
					->options([
						 'user_agent' => 'PHPSoapClient',      // Add this as options
						 'encoding' => 'ISO-8859-1',
						 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
						
					 ]);
				}); 
		
		$params_simple = array(
				'JournalName'=> LANDLORD_INV_JOURNAL_NAME,
				'DataAreaId'=> DATA_AREA_ID,
				'company'=> COMPANY,
				
		);
		
		$response = $soapWrapper->call($serviceName.'.APInvoiceJournal', [$params_simple]);
		return ($response->APInvoiceJournalResult)?$response->APInvoiceJournalResult:'Error';
	}
	/*
	 * Line Item - APInvoiceRegister - Landlord Invoice & cost Recognition
	 */
	public static function LandlordInvoiceRegisterAxLineItemPushData($serviceName, $params=array()){
	
		foreach($params as $lineItem){
			$soapWrapper = new SoapWrapper ;
			$soapWrapper->add($serviceName, function ($service) {
					$service
						->wsdl(AX_URL)
						->cache(WSDL_CACHE_NONE)
						->trace(true)
						->options([
							 'user_agent' => 'PHPSoapClient',      // Add this as options
							 'encoding' => 'ISO-8859-1',
							 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
							
						 ]);
					}); 
			
			$params_simple =array(
						'JournalNum'=> $lineItem['JournalNum'],
						'JournalName'=>$lineItem['JournalName'],
						'PaymentDate'=>$lineItem['PaymentDate'],
						'Description'=>$lineItem['Description'],
						'vendAccount'=>$lineItem['vendAccount'],
						'currency'=>$lineItem['currency'],
						'accountType'=> $lineItem['accountType'],
						'paymentMethod'=>$lineItem['paymentMethod'],
						'checkBookid'=>$lineItem['checkBookid'],
						'documentNo'=> $lineItem['documentNo'],
						'AmountCredit'=>$lineItem['AmountCredit'],
						'voucher'=>$lineItem['voucher'],
						'AmountDebit'=> $lineItem['AmountDebit'],
						'Remarks'=>$lineItem['Remarks'],
						'Invoice'=>$lineItem['Invoice'],
						'dimension1'=>$lineItem['dimension1'],
						'dimension1value'=>$lineItem['dimension1value'],
						'dimension2'=>$lineItem['dimension2'],
						'dimension2value'=>$lineItem['dimension2value'],
						'dimension3'=>$lineItem['dimension3'],
						'dimension3value'=>$lineItem['dimension3value'],
						'dimension4'=> $lineItem['dimension4'],
						'dimension4value'=>$lineItem['dimension4value'],
						'dimension5'=>$lineItem['dimension5'],
						'dimension5value'=>$lineItem['dimension5value'],
						'DataAreaId'=>DATA_AREA_ID,
						'company'=>COMPANY
			);
			
			
			$response = $soapWrapper->call($serviceName.'.APInvoiceJournal_Line', [$params_simple]);
			
			if($response->APInvoiceJournal_LineResult =='Error')
				return 'Error';
			
		}
		return ($response->APInvoiceJournal_LineResult)?$response->APInvoiceJournal_LineResult:'Error'; 
		
	}
} 