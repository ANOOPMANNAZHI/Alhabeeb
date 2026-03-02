<?php

namespace Modules\Sales\Listeners;

use Modules\Sales\Events\NewEnquiry;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyEnquiry implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewEnquiry  $event
     * @return void
     */
    public function handle(NewEnquiry $event)
    {
        //
    }
}
