<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesFollowup extends Model
{
    protected $table = 'termination_dues_followups';
    protected $guarded = [];
    protected $dates = ['followup_date', 'promise_date'];

    public function dues()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDues', 'termination_dues_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
