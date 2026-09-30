<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Modules\BackOffice\Services\Bulk\BulkSender;

class BulkMessagesSend extends Command
{
    protected $signature = 'bulk-messages:send';
    protected $description = 'Send the next batch of bulk messages (Mail -> Send); scheduled every minute';

    public function handle()
    {
        $r = BulkSender::run();
        $this->info('Bulk: ' . $r['processed'] . ' processed (' . $r['sent'] . ' sent, ' . $r['failed'] . ' failed, ' . $r['skipped'] . ' skipped).');
        return 0;
    }
}
