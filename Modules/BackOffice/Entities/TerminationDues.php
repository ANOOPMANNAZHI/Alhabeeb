<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDues extends Model
{
    protected $table = 'termination_dues';
    protected $guarded = [];
    protected $dates = ['termination_date', 'terminated_at', 'charges_fixed_at', 'next_promise_date', 'last_followup_at'];

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_SETTLED = 'settled';
    const STATUS_WRITTEN_OFF = 'written_off';

    public function lines()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesLine', 'termination_dues_id')->orderBy('line_order')->orderBy('id');
    }

    public function followups()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesFollowup', 'termination_dues_id')->orderBy('followup_date', 'desc')->orderBy('id', 'desc');
    }

    public function tenantContract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id', 'id');
    }

    public function termination()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\Termination', 'termination_id', 'id');
    }
}
