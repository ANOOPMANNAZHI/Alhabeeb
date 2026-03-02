<?php

namespace App\Soap\Response;

class GetConversionRateResponse
{
  /**
   * @var string
   */
  protected $GetConversionRate;

  /**
   * GetConversionAmountResponse constructor.
   *
   * @param string
   */
  public function __construct($GetConversionRateResult)
  {
    $this->GetConversionRateResult = $GetConversionRateResult;
  }

  /**
   * @return string
   */
  public function GetConversionRateResult()
  {
    return $this->GetConversionRateResult;
  }
}
