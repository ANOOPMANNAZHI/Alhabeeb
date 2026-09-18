<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesFollowup extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $table = 'termination_dues_followups';
    protected $guarded = [];
    protected $casts = ['followup_date' => 'datetime', 'promise_date' => 'datetime'];

    public function dues()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDues', 'termination_dues_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
