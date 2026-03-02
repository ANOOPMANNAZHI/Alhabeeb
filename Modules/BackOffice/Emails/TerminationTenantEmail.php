<?php

namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\Termination;

class TerminationTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Termination $termination,TenantContract $tenantContract,$terminationChecklistOther,$groupedWork)
    {
        $this->tenantName 	= $termination->tenant_name;
        $this->tenantemail 	= $termination->tenant_contact_email;
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

        return $this->markdown('backoffice::Email.termination_tenant_mail')->with(['tenantName'=>$this->tenantName,'tenantContract'=>$this->tenantContract,'terminationChecklistOther'=>$this->terminationChecklistOther,'groupedWork'=>$this->groupedWork,'termination'=>$this->termination])->subject('Termination Inspection Details');
    }
}
