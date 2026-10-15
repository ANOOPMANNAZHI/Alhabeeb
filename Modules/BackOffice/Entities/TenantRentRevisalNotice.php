<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** The 120-day "start rent revisal" email sent to the ARE for a contract (at most one per contract). */
class TenantRentRevisalNotice extends Model
{
    protected $table = 'tenant_rent_revisal_notices';
    protected $fillable = ['tenant_contract_id', 'are_user_id', 'email_status', 'sent_at'];
    protected $dates = ['sent_at'];
}
