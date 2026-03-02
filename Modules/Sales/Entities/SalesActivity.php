<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class SalesActivity extends Model
{
	protected $guarded = [];
    protected $fillable = [];
    protected $dates = ['sales_activities_due_date'];

    /*
    *
    *  SalesActivityType
    */
    public function getSalesActivityTypeAttribute($value)
    {

    	if($value == 1)
    		$name = 'Email';
    	else if($value == 2)
    		$name = 'Phone';
    	else if($value == 3)
    		$name = 'Task';
    	else if($value == 4)
    		$name = 'Appointment';

        return $name;
    }
    /*
    *
    *  SalesActivitiesStatus
    */
    public function getSalesActivitiesStatusAttribute($value)
    {

    	if($value == 1)
    		$name = 'Not Attend';
    	else if($value == 2)
    		$name = 'Attend';
    	else if($value == 3)
    		$name = 'Close';

        return $name;
    }
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
    /*
    * 
    *  Closed By(UpdatedBy)
    *
    */
    public function updatedBy() {
        
           return $this->belongsTo('App\User','updated_by');
    }
}
