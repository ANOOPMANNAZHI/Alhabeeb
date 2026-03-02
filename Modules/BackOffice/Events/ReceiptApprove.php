<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use App\User;

class ReceiptApprove
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
        $this->receipt->text = "Receipt - ".$ReceiptsGeneration->receipts_generation_receipt_no." For Approval Request ";   
        $this->receipt->icon = 'fa-envelope-o';
        $this->receipt->icon_color = 'blue-bgcolor';
        
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
