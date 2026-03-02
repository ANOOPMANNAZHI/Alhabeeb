<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;


class PreferredTime extends Model
{
	use Sortable;

    protected $guarded = [];

    protected $table = 'preferred_time';
}
