<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequenceConfig extends Model
{
    protected $fillable = [];
    protected $guarded 	= [];
    protected $table 	= 'invoice_sequence_config';
    public $timestamps = false;
}
