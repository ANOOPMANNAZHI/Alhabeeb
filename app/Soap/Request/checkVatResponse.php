<?php

namespace App\Soap\Request;

class CheckVatResponse
{
  /**
   * @var string
   */
  protected $countryCode;

  /**
   * @var string
   */
  protected $vatNumber;

  /**
   * @var string
   */
  protected $requestDate;

  /**
   * @var string
   */
  //protected $Amount;

  /**
   * GetConversionAmount constructor.
   *
   * @param string $CurrencyFrom
   * @param string $CurrencyTo
   * @param string $RateDate
   * @param string $Amount
   */
  public function __construct($CurrencyFrom, $CurrencyTo, $RateDate, $Amount)
  {
    $this->countryCode = $CurrencyFrom;
    $this->vatNumber   = $CurrencyTo;
    $this->requestDate     = $RateDate;
    echo $this->requestDate; exit;
    //$this->Amount       = $Amount;
  }

  /**
   * @return string
   */
  public function getcountryCode()
  {
    return $this->countryCode;
  }

  /**
   * @return string
   */
  public function getvatNumber()
  {
    return $this->vatNumber;
  }

  /**
   * @return string
   */
  public function getrequestDate()
  {
    return $this->requestDate;
  }

  /**
   * @return string
   */
  public function getAmount()
  {
    return $this->Amount;
  }
}
