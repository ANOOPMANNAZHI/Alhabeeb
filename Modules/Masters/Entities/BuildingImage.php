<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class BuildingImage extends Model
{
    protected $guarded = [] ;

    /*
    * Image Category Name
    *
    */

    public function getBuildingImgCategoryNameAttribute()
    {     
        switch($this->building_img_category){
          case '0' : return 'Frontage';
          case '1' : return 'Balcony';  
          case '2' : return 'Others';       
        }
    }
}
