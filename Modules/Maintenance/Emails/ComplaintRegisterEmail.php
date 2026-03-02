<?php

namespace Modules\Maintenance\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Tenant;
use Modules\Maintenance\Entities\ComplaintEnquiry;

class ComplaintRegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ComplaintEnquiry $complaintEnquiry)
    {
         $this->tenantName  = $complaintEnquiry->tenant->tenant_name;
        $this->tenantemail  = $complaintEnquiry->tenant->tenant_contact_email;
        $this->compNo   = $complaintEnquiry->complaint_no;
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

         return $this->markdown('maintenance::Email.complaint_mail')->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail,'compNo'=>$this->compNo])->subject('Complaint Registration');
    }
}
