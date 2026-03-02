<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class LegalNotes extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'legal_notes';
	public $sortable = ['id','user_id','legal_id','legal_notes_status','legal_notes_note'];

	public function getLegalNotesStatusNameAttribute()
	{     
		switch($this->legal_notes_status){
			case 0 : return 'End';  
			case 1 : return 'Start';
			case 2 : return 'Middle';     
		}
	}
}
