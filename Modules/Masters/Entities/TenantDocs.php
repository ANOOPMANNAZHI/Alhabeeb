<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantDocs extends Model
{
    protected $guarded = [];
    protected $table = 'tenants_documents';

    /*
    * Status Name
    *
    */

    public function gettenantDocCategoryNameAttribute()
    {     
        switch($this->tenant_doc_category){
          case '0' : return 'Resident Card';
          case '1' : return 'Passport';  
          case '2' : return 'CR';  
          case '3' : return 'Others';        
        }
    }
}
