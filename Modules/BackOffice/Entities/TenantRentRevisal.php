<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** A rent revisal before renewal (one per contract): a draft can be edited; once sent it is locked. */
class TenantRentRevisal extends Model
{
    const DRAFT = 'draft';
    const SENT = 'sent';

    protected $table = 'tenant_rent_revisals';
    protected $fillable = ['tenant_contract_id', 'current_rent', 'revised_rent', 'note', 'status', 'email_status', 'emailed_at', 'created_by'];
    protected $attributes = ['status' => self::SENT];

    public function isDraft()
    {
        return $this->status === self::DRAFT;
    }
    protected $casts = ['emailed_at' => 'datetime'];

    public function contract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
