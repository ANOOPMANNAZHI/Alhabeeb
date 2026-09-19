<?php

namespace Modules\BackOffice\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\BackOffice\Services\TenantOnboarding;

/**
 * Creates the tenant onboarding row for contracts activated without going
 * through the hooked desktop actions (imports, AX sync, older activations).
 */
class OnboardingSync extends Command
{
    protected $signature = 'onboarding:sync {--since= : Only contracts updated since this date/time (default: 2 days ago)}';
    protected $description = 'Create missing tenant onboarding rows for active contracts';

    public function handle(): int
    {
        $since = Carbon::parse($this->option('since') ?: '-2 days');
        $n = TenantOnboarding::sync($since);
        $this->info("Onboarding rows created: $n (contracts updated since {$since->toDateTimeString()})");
        return self::SUCCESS;
    }
}
