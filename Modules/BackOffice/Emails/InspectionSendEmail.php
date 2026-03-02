<?php

namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\Termination;

class InspectionSendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Termination $termination,TenantContract $tenantContract,$terminationChecklistOther,$groupedWork)
    {
        $this->tenantName   = $tenantContract->tenant->tenant_name;
        $this->tenantemail  = $tenantContract->tenant->tenant_contact_email ? $tenantContract->tenant->tenant_contact_email:$tenantContract->tenant->tenant_personal_email;
        $this->groupedWork  = $groupedWork;
        $this->tenantContract  = $tenantContract;
        $this->termination = $termination;
        $this->terminationChecklistOther = $terminationChecklistOther;
        //dd($this->terminationChecklistOther);
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.inspection_send_mail')->with(['tenantName'=>$this->tenantName,'tenantContract'=>$this->tenantContract,'terminationChecklistOther'=>$this->terminationChecklistOther,'groupedWork'=>$this->groupedWork,'termination'=>$this->termination])->subject('Inspection Details');
    }
}
