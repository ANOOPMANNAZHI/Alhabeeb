<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class BuildingEleWaterReading extends Model
{
    protected $guarded = [];

    /*
    * Doc Category Name
    *
    */

    public function getBuildingMeterCategoryNameAttribute()
    {     
        switch($this->building_meter_category){
          case '0' : return 'Common Area';
          case '1' : return 'Lift';  
          case '2' : return 'Others';       
        }
    }
}
