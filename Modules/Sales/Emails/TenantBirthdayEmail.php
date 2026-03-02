<?php
namespace Modules\Sales\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Tenant;

class TenantBirthdayEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenantName   = $tenant->tenant_name;
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

        return $this->markdown('sales::Email.tenant_birthday_mail')->with(['tenantName'=>$this->tenantName,'textContent'=>$this->textContent])->subject($this->tenantSubject);
    }
}
