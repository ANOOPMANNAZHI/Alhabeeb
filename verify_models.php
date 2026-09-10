<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$inv = new \Modules\BackOffice\Entities\LandlordInvoiceV2();
echo get_class($inv->vendor()->getRelated()) . "\n";
echo get_class($inv->landlordContract()->getRelated()) . "\n";
echo get_class($inv->lines()->getRelated()) . "\n";
