<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesAllocation extends Model
{
    protected $table = 'termination_dues_allocations';
    protected $guarded = [];

    public function line()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDuesLine', 'termination_dues_line_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
