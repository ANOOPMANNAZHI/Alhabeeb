<?php

namespace Modules\Masters\Console;

use Illuminate\Console\Command;
use Modules\Masters\Services\LegalSettlement;

/** Daily: settlement statuses (completed / broken), overdue installment alerts, payments lost after closing. */
class LegalSettlementCheck extends Command
{
    protected $signature = 'legal:settlement-check';
    protected $description = 'Refresh legal settlements, alert overdue installments and settled cases that lost a payment';

    public function handle()
    {
        $c = LegalSettlement::checkAll();
        $this->info('Refreshed ' . $c['refreshed'] . ' settlement(s), ' . $c['overdue_alerts'] . ' overdue alert(s), '
            . $c['shortfalls'] . ' closed case(s) short.');
        return 0;
    }
}
