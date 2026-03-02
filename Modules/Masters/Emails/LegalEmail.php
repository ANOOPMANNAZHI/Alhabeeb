<?php
namespace Modules\Masters\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Masters\Entities\Legal;
use Modules\Sales\Entities\User;

class LegalEmail extends Mailable
{
    use Queueable, SerializesModels;
     public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Legal $legal,$user)
    {
       $this->user = $user;
        $this->tenantContract   = $legal->tenantContract->tenant_contract_no;
        $this->textContent     = $legal->textContent;
        $this->subject     = $legal->subject;
        $this->userName     = $user->employee->employee_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('masters::Email.legal_mail')->with(['userName'=>$this->userName,'textContent'=>$this->textContent,'tenantContract'=>$this->tenantContract])->subject($this->subject);
    }
}
