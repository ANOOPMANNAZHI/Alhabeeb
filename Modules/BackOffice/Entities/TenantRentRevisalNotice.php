<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/** The "start rent revisal" email (sent when the contract enters the window) sent to the ARE for a contract (at most one per contract). */
class TenantRentRevisalNotice extends Model
{
    protected $table = 'tenant_rent_revisal_notices';
    protected $fillable = ['tenant_contract_id', 'are_user_id', 'email_status', 'sent_at'];
    protected $dates = ['sent_at'];
}
