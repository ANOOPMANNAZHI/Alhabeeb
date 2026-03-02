<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantContractRevokeNote extends Model
{
    protected $fillable = [];
    protected $guarded = [];
    
    protected $table = 'tenant_contract_revoke_note';
    
    public function CreatedUser() {
        
           return $this->belongsTo('App\User','created_by');
    }
}
