<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class ComplaintServiceReportInv extends Model
{
    protected $guarded = [];
    protected $table = 'complaint_service_report_inv';
    public $timestamps = false;
    /*
    *
    * Complaint ServiceReport
    */
    public function complaintServiceReport(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintServiceReport','complaint_service_report_id');
    }
    /*
    *
    * Complaint inventory
    */
    public function inventory(){

      return $this->belongsTo('Modules\Masters\Entities\Inventory','inventory_id');
    }
}
