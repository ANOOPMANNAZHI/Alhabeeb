<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Modules\BackOffice\Services\RentRevisal;

/** Emails each ARE once when a contract enters the 90-120 day rent revisal window (spec 2026-10-15). */
class RentRevisalNotifyAre extends Command
{
    protected $signature = 'rent-revisal:notify-are';
    protected $description = 'Email AREs about contracts entering the rent revisal window';

    public function handle()
    {
        $this->info('Rent revisal ARE emails: ' . RentRevisal::notifyAres());
    }
}
