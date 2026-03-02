<?php

namespace Modules\Maintenance\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Tenant;

class ComplaintCloseEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenantName 	= $tenant->tenant_name;
        $this->tenantemail 	= $tenant->tenant_contact_email;
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('maintenance::Email.complaint_close_mail')->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail])->subject('Complaint Closed');
    }
}
