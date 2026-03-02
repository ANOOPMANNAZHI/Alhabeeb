<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use App\User;

class ReceiptReject   
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(ReceiptsGeneration $ReceiptsGeneration,$user)
    {
         $this->receipt = $ReceiptsGeneration;
         $this->receipt->text = "Receipt Rejected  -  ".$ReceiptsGeneration->receipts_generation_receipt_no."  by ".\Auth::user()->username; 
         $this->receipt->icon = 'fa-warning';
         $this->receipt->icon_color = 'yellow';
         $this->users = $user;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
