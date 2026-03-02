<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class AmcContractAmenities extends Model
{
  protected $guarded = [];
  protected $table = 'amc_contract_amenities';
  public $timestamps = false;

      /*
    *
    *  Amenity Type 
    */
      public function amenityType(){

        return $this->belongsTo('Modules\Masters\Entities\AmentityType','amenities_type_id');
      }
      
      public function AmcContractAmenity(){

        return $this->hasMany('Modules\Maintenance\Entities\AmcContractAmenities','amc_contract_id','id');
      }
    }
