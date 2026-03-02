<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TerminationDocument extends Model
{
    
    use Sortable;
    protected $guarded = [];
    protected $table = 'termination_document';

    
    
}
