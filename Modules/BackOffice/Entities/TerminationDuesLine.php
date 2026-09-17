<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TerminationDuesLine extends Model
{
    protected $table = 'termination_dues_lines';
    protected $guarded = [];

    public function dues()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\TerminationDues', 'termination_dues_id', 'id');
    }

    public function allocations()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesAllocation', 'termination_dues_line_id');
    }
}
