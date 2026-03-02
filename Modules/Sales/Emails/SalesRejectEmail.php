<?php
namespace Modules\Sales\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\SalesEnquiry;

class SalesRejectEmail extends Mailable
{
    use Queueable, SerializesModels;
      public $userName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SalesEnquiry $salesEnquiry,$userName)
    {
        $this->salesName = $userName;
        $this->subject  = $salesEnquiry->subject;
        $this->textContent   = $salesEnquiry->textContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('sales::Email.sales_reject_mail')->with(['salesName'=>$this->salesName,'textContent'=>$this->textContent])->subject($this->subject);
    }
}
