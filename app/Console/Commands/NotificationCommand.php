<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use App\Setting;
use Modules\Maintenance\Http\Controllers\AmcTaskController as Task ;
use Modules\BackOffice\Http\Controllers\TenantRenewalController as TenantRenewal ;
use Modules\Sales\Http\Controllers\TenantController as Tenant;


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
        $task =  new Task;
        $task->amcTaskNotification();
        $this->info('Task Overdue Notification');

        $tenantRenewal =  new TenantRenewal;
        $tenantRenewal->cronRenewalContract();
        $this->info('Tenant Renewal Activation');

        $birthday =  new Tenant;
        $birthday->tenantBirthdayNotification();
        $this->info('Tenant Birthday');

        //$reminder =  new Tenant;
        //$reminder->areReminderNotification();
        //$this->info('Reminder');
    }
}
