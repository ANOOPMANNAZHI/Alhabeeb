<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class ReceiptGenerationDim extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'receipts_generation_dim';
}
