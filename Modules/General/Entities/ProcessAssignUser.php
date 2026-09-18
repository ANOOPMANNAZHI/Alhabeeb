<?php

namespace Modules\General\Entities;

use Illuminate\Database\Eloquent\Model;

class ProcessAssignUser extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded 	= [];
    protected $table 	= 'process_assign_users';
    public $timestamps 	= false;

    
}
