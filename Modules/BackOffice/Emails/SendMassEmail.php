<?php
namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
//use App\User;

class SendMassEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user_info;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
         $this->user_info    = $user;
    

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       // return $this->view('backoffice::MassMail.composer');
       return $this->subject($this->user_info->subject)->markdown('backoffice::MassMail.composer');
    }
}
