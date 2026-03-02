<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class BuildingDocs extends Model
{
    protected $guarded = [] ;

   	/*
    * Doc Category Name
    *
    */

    public function getBuildingDocCategoryNameAttribute()
    {     
        switch($this->building_doc_category){
          case '0' : return 'Mulkia';
          case '1' : return 'Krooki'; 
          case '2' : return 'Waqala (POA)'; 
          case '3' : return 'Landlord ID'; 
          case '4' : return 'CR (if Landlord is a company)'; 
          case '5' : return 'Drawings'; 
          case '6' : return 'Building Permit'; 
          case '7' : return 'Civil Defense Certificate'; 
          case '8' : return 'Others';       
          case '9' : return 'AMC';       
          case '10' : return 'Insurance';       
        }
    }

}
