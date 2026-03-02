<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class SalesNote extends Model
{
    protected $guarded = [];
    protected $dates = ['created_at'];
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
