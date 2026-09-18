<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;

class ComplaintServiceReportImage extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'complaint_service_report_image';

    
}
