<?php

namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\Tenant;
use \Auth;

class RenewalFormPdfEmail extends Mailable
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
        $this->pdf = $tenant->pdf;
        $this->pdfname = $tenant->pdfname;
        $this->toDate = $tenant->todate;//dd($tenant->todate);
        $this->building = $tenant->building;
        $this->unitno = $tenant->unitno;
        //dd($tenant->pdfname);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.renewal_form_pdf_mail')
                    ->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail,'toDate'=>$this->toDate,'building'=>$this->building,'unitno'=>$this->unitno])->subject('Renewal Form')
                    ->attachData($this->pdf->output(), $this->pdfname, [
                    'mime' => 'application/pdf'])//;
                 //   ->from(Auth::user()->email,Auth::user()->email);
                   ->from(\Config::get('mail.from.address'));
                  //  ->from(Auth::user()->email);
    }
}
