<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class ComplaintServiceReportNote extends Model
{
    protected $guarded = [];
    protected $table = 'complaint_service_report_notes';
    /*
    *
    * serviceReport
    */
    public function serviceReport(){

      return $this->belongsTo('Modules\Maintenance\Entities\ComplaintServiceReport','complaint_service_report_id');
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
    * Service Report Stage Name
    *
    */
    public function getServiceReportStageNameAttribute()
    {     
        switch($this->stage){
          case '0' : return 'Open';  
          case '1' : return 'In Progress';
          case '2' : return 'Attended';  
          case '3' : return 'Completed';
          case '4' : return 'Closed';
          case '5' : return 'Send To Landlord'; 
          case '6' : return 'Rejected';    
        }
    }
}
