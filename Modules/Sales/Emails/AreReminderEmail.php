<?php
namespace Modules\Sales\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Tenant;

class AreReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenantName   = $tenant->employee_name; //are name
        $this->tenantSubject    = $tenant->tenant_subject;
        $this->textContent 	= $tenant->tenant_content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('sales::Email.are_reminder_mail')->with(['tenantName'=>$this->tenantName,'textContent'=>$this->textContent])->subject($this->tenantSubject);
    }
}
