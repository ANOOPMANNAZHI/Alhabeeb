<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Setting;
use Modules\Maintenance\Http\Controllers\AmcTaskController as Task ;
use Modules\BackOffice\Http\Controllers\TenantRenewalController as TenantRenewal ;
use Modules\Sales\Http\Controllers\TenantController as Tenant;
use Modules\BackOffice\Http\Controllers\PdcController as PdcReminder;


class NotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   // protected $signature = 'command:name';
    protected $signature = 'taskOverDuenotify:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task Overdue Today notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $task =  new Task;
            $task->amcTaskNotification();
            $this->info('Task Overdue Notification');
        } catch (\Exception $e) {
            Log::error('NotificationCommand: amcTaskNotification failed - ' . $e->getMessage());
            $this->error('Task Overdue Notification FAILED: ' . $e->getMessage());
        }

        try {
            $tenantRenewal =  new TenantRenewal;
            $tenantRenewal->cronRenewalContract();
            $this->info('Tenant Renewal Activation');
        } catch (\Exception $e) {
            Log::error('NotificationCommand: cronRenewalContract failed - ' . $e->getMessage());
            $this->error('Tenant Renewal Activation FAILED: ' . $e->getMessage());
        }

        try {
            $birthday =  new Tenant;
            $birthday->tenantBirthdayNotification();
            $this->info('Tenant Birthday');
        } catch (\Exception $e) {
            Log::error('NotificationCommand: tenantBirthdayNotification failed - ' . $e->getMessage());
            $this->error('Tenant Birthday FAILED: ' . $e->getMessage());
        }

        try {
            $pdcReminder =  new PdcReminder;
            $pdcReminder->pdcExpiryReminderSms();
            $this->info('PDC Expiry SMS Reminder');
        } catch (\Exception $e) {
            Log::error('NotificationCommand: pdcExpiryReminderSms failed - ' . $e->getMessage());
            $this->error('PDC Expiry SMS Reminder FAILED: ' . $e->getMessage());
        }

        //$reminder =  new Tenant;
        //$reminder->areReminderNotification();
        //$this->info('Reminder');
    }
}
