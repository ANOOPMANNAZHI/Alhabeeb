<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class DiscussionCategory extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];

    protected $table = 'discussion_categories';
}
