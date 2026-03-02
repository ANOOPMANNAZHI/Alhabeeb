<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantContractComment extends Model
{
    use Sortable;
    protected $guarded = [];
    protected $table = 'tenant_contract_comments';
    public $sortable = ['id','tenant_contract_id'];
}
