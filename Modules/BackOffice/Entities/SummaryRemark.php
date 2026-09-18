<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class SummaryRemark extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $fillable = [];

    protected $guarded = [];
    protected $table = 'summary_remark';
}
