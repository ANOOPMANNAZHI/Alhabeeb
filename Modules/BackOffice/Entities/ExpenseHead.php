<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ExpenseHead extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	use Sortable;
	protected $guarded = [];
	protected $table = 'expense_head';
	protected $casts = ['created_at' => 'datetime'];

	/*
    *
    *Acc codes
    *
    *
    */
     public function accountCode(){

      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','acc_codes_id','id');
    }
}
