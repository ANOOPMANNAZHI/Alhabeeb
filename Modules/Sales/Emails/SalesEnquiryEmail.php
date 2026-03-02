<?php
namespace Modules\Sales\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Sales\Entities\SalesEnquiry;

class SalesEnquiryEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SalesEnquiry $salesEnquiry)
    {
        $this->salesName 	= $salesEnquiry->sales_enquiry_name;
        $this->salesEmail   = $salesEnquiry->sales_email;
        $this->salesTypeName    = $salesEnquiry->sales_type_name;
        $this->salesEnqNo 	= $salesEnquiry->sales_enquiry_no;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('sales::Email.sales_enquiry_mail')->with(['salesName'=>$this->salesName,'salesEmail'=>$this->salesEmail,'salesTypeName'=>$this->salesTypeName,'salesEnqNo'=>$this->salesEnqNo])->subject('New Enquiry Added');
    }
}
