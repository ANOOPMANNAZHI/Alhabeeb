<?php

namespace Modules\Maintenance\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Maintenance\Entities\ComplaintEnquiry;

class ComplaintStageEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user_name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ComplaintEnquiry $complaintEnquiry,$user_name)
    {
        $this->userName=$user_name;
        $this->compNo   = $complaintEnquiry->complaint_no;
        $this->subject  = $complaintEnquiry->subject;
        $this->textContent 	= $complaintEnquiry->textContent;
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('maintenance::Email.complaint_stage_mail')->with(['userName'=>$this->userName,'compNo'=>$this->compNo,'textContent'=>$this->textContent])->subject($this->subject);
    }
}
