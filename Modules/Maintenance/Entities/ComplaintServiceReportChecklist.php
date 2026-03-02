<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class ComplaintServiceReportChecklist extends Model
{
   	protected $guarded = [];
    protected $table = 'complaint_service_report_checklist';
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
    * Complaint Checklist
    */
    public function complaintChecklist(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintChecklist','checklist_id');
    }
    /*
    *
    * Complaint Checklist service report Inv
    */
    public function checklistServiceReportInv(){
       
      return $this->hasMany('Modules\Maintenance\Entities\ComplaintServiceReportInv','complaint_service_report_id','complaint_service_report_id');
    }
}
