<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class AmcScheduleAmenity extends Model
{
	protected $guarded = [];
	protected $fillable = [];
	protected $table = 'amc_schedule_amenities';
	public $timestamps = false;
	 /*
    *
    *  Amenity type
    */
    public function amenityType(){

      return $this->belongsTo('Modules\Masters\Entities\AmentityType','amenities_type_id','id');
    }
}
