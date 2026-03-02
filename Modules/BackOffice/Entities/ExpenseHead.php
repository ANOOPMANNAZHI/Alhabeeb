<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ExpenseHead extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'expense_head';
	protected $dates = ['created_at'];

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
