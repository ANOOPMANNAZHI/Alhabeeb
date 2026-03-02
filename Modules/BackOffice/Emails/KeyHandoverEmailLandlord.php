<?php
namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Masters\Entities\Vendor;

class KeyHandoverEmailLandlord extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Vendor $vendor)
    {
        $this->tenantName 	= $vendor->vendor_name;
        $this->tenantemail 	= $vendor->vendor_contact_email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.key_handover_mail')->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail])->subject('Key Accepted');
    }
}
