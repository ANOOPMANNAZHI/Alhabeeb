<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class LegalDocuments extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'legal_documents';
	public $sortable = ['id','legal_documents_file_name','legal_id','legal_documents_name'];
}
