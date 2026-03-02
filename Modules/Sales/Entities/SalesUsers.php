<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class SalesUsers extends Model
{
	protected $guarded = [] ;

    /*
    * 
    *  CreatedBy
    *
    */
    public function assignUser() {
        
           return $this->belongsTo('App\User','user_id');
    }
    public $timestamps = false;
}
