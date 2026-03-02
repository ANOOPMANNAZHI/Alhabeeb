<?php

namespace Modules\BackOffice\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\SendMassEmail;
use Modules\BackOffice\Entities\PreferredMails;
//use App\User;
use Log;

class sendMassMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user_info;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {

        $this->user_info    = $user;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         
        $recipient = $this->user_info->email; 
        $cc = explode(',',$this->user_info->cc);    
        if(isset($this->user_info->cc) && count($cc)>0)  
            Mail::to($recipient)->cc($cc)->send(new SendMassEmail($this->user_info)); 
        else
             Mail::to($recipient)->send(new SendMassEmail($this->user_info)); 
        // Sucess
        PreferredMails::where('id',$this->user_info->statusId)->update(['status' => 1]);
        Log::info('Update order ' . $this->user_info->statusId);
    }
    public function failed()
    {
        // Fail
        PreferredMails::where('id',$this->user_info->statusId)->update(['status' => 2]);

    }
}
