<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantContractRevokeTemp extends Model
{
    protected $fillable = [];
    protected $guarded = [];
    
    protected $table = 'tenant_contract_revoke_temp';
}
