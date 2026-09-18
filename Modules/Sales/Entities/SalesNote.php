<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class SalesNote extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];
    /*
    *
    * Sales
    */
    public function sales(){

      return $this->belongsTo('Modules\Sales\Entities\Sales');
  	}
  	/*
    * 
    *  CreatedBy
    *
    */
    public function createdBy() {
        
           return $this->belongsTo('App\User','created_by');
    }
    
}
