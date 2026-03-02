<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

class Occupant extends Model
{
    protected $guarded = [];
    protected $table = 'occupants';
    /*
    *
    * Tenant 
    */
    public function tenantContract(){

      return $this->hasOne('Modules\Sales\Entities\TenantContract','occupant_id');
    }
}
