<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantLandlordTerminationRenewalComment extends Model
{
    
    use Sortable;
    protected $guarded = [];
    protected $table = 'tenant_landlord_termination_renewal_comment';
    protected $dates = ['created_at'];

    public function createdBy() {
        
           return $this->belongsTo('App\User','created_by');
    }
    
}
