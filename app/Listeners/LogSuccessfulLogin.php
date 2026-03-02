<?php

namespace App\Listeners;
 
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;

class LogSuccessfulLogin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
         $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  NewEnquiry  $event
     * @return void
     */
    public function handle($event)
    {            
        $user =  $event->user;
      
        $user->user_last_login = $user->user_log_time;
        $user->user_log_time = \Carbon\Carbon::now();
        $user->user_ip = $this->request->getClientIp();
        $user->save();
    }
}
