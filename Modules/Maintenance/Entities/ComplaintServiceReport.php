<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ComplaintServiceReport extends Model
{
  use Sortable;
  
    protected $guarded = [];
    protected $table = 'complaint_service_report';
	///public $sortable = ['id','service_report_no'];

    /*
    *
    * Complaint Service Report Checklist
    */
    public function complaintServiceReportChecklist(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintServiceReportChecklist','complaint_service_report_id','id');
    }
    /*
    *
    * Complaint  Report Checklist
    */
    public function checklistServiceReport(){

      return $this->hasOne('Modules\Maintenance\Entities\ComplaintServiceReportChecklist','complaint_service_report_id','id');
    }
    /*
    * Service Report Status Name
    *
    */
    public function getServiceReportStatusNameAttribute()
    {     
        switch($this->complaint_assign_status){
          case '0' : return 'Open';  
          case '1' : return 'In Progress';
          case '2' : return 'Attended';  
          case '3' : return 'Completed';
          case '4' : return 'Closed';
          case '5' : return 'Send To Landlord'; 
          case '6' : return 'Rejected';    
        }
    }
    /*
    *  Enquiry Status Class
    */
    public function getServiceReportStatusClassAttribute()
    {

        $code =  $this->complaint_assign_status; 

        switch($code){

          case 2:
           $class = 'label-danger';
            break;

          case 5:           
            $class = 'label-info';
            break;
          case 6:           
            $class = 'label-warning';
            break; 
         default : 
            $class = 'label-warning';
            break;
              

        }


        return $class;

        
    }
}
