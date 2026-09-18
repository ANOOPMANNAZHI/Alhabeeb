<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class DimDetail extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'dim_details';

    /*
    public function dim1()
    {
        return $this->morphOne('Modules\Maintenance\Entities\MaintenanceInvoiceDetails', 'dim1able');
    } */
}
