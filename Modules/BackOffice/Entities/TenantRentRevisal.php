<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** A rent revisal sent to the tenant before renewal (one per contract, locked once sent). */
class TenantRentRevisal extends Model
{
    protected $table = 'tenant_rent_revisals';
    protected $fillable = ['tenant_contract_id', 'current_rent', 'revised_rent', 'note', 'email_status', 'emailed_at', 'created_by'];
    protected $dates = ['emailed_at'];

    public function contract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
