<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantLandlordTerminationRenewalComment extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    
    use Sortable;
    protected $guarded = [];
    protected $table = 'tenant_landlord_termination_renewal_comment';
    protected $casts = ['created_at' => 'datetime'];

    public function createdBy() {
        
           return $this->belongsTo('App\User','created_by');
    }
    
}
