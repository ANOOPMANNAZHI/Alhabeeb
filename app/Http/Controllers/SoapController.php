<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\GetConversionAmount;
use App\Soap\Response\GetConversionRateResponse;
use App\Soap\Request\GetConversionRate;
//use App\Soap\Response\GetConversionAmountResponse;
//use SoapClient;


class SoapController extends Controller
{
    /**
   * @var SoapWrapper
   */
  protected $soapWrapper;

  /**
   * SoapController constructor.
   *
   * @param SoapWrapper $soapWrapper
   */
  public function __construct(SoapWrapper $soapWrapper)
  {
    $this->soapWrapper = $soapWrapper;
  }

  public function soapApiAction1(){
    	dd(1111);
    }


    /**
   * Use the SoapWrapper
   */
  public function soapApiAction() 
  {
  	 //dd(2233);
  
  	$opts = array(
        'http' => array(
            'user_agent' => 'PHPSoapClient'
        )
    );
    $context = stream_context_create($opts);
    //dd($context);
	$this->soapWrapper->add('AXVendor', function ($service) {
      $service
        ->wsdl('http://alh-axtest:8018/Wrapper.asmx?wsdl')
        ->cache(WSDL_CACHE_NONE)
        ->trace(true)
		->options([
             'user_agent' => 'PHPSoapClient',      // Add this as options
             'encoding' => 'ISO-8859-1',
             'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
			
         ]);
    }); 
    
    $params_simple = array (
			'VendAccount' =>'LAN1003',
			'CustName' =>'Rocky1qwqwqwqw',
			'CountryRegionCode'=> 'OMN',
			'ContactDescription' => 'INR',
			'City' => 'ERN',
			'LocationName' => 'India',
			'Street' => 'custaccountaddress',
			'ZipCode' => 'ALM',
			'Phone' => '0096871234567',
			'Gsm' =>'147258369',
			'DataAreaId'=> 'HAB',
			'VendGroup'=>'Custgroup',
			'DirpartyType'=> 'Organization',
			'company'=>'HAB',
						
		);

    $response = $this->soapWrapper->call('AXVendor.CreateVendor', [$params_simple]);
  
  	 dd($response->CreateVendorResult);
	

	/*
  	$client = new SoapClient ( 'http://currencyconverter.kowabunga.net/converter.asmx?WSDL' , array ('user_agent' => 'PHPSoapClient','encoding' => 'ISO-8859-1', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP ) );         
    $params_simple = array (
            'CurrencyFrom' =>'USD',
			'CurrencyTo' => 'INR',
			'RateDate' => '2019-10-30'
            
        );

    $result = $client ->GetConversionRate ($params_simple);

    dd($result);
	*/

  	 /*
  	  $response = $this->soapWrapper->call('checkVatResponse.checkVatResponse', [
      'countryCode' => 'USD', 
      'vatNumber'   => '22.00', 
      'requestDate'     => '2014-06-05', 
      
    ]);

	$request = $this->soapWrapper->add('checkVatResponse', function ($service){
            $service
                ->wsdl('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl')
                ->trace(true)
                ->classmap([
		          GetConversionAmount::class,
		          GetConversionAmountResponse::class,
		          CheckVatResponse::class,
		        ]);
        });

    var_dump($response);

    // With classmap
    $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
      new GetConversionAmount('USD', 'EUR', '2014-06-05', '1000')
    ]);

    var_dump($response);
    exit;
  	 /*
  	 $data = [
            'Region_ID'=> 92,
            'Match_ID' => 0,
            'Department_IDs' => '',
            'RegionCoefficient_X' => 1,
            'RegionCoefficient_Y' => 1,
        ];


        $response = $this->soapWrapper->call('CreateSession.GetEntityLocationList', $data);
        dd($response);

        var_dump($response);
	*/
    exit;
    
  }
}
