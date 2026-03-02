<?php

namespace Modules\Masters\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class EmployeeResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected  $username;
    protected  $new_password;

    public function __construct($user)
    {

        $this->username 	= $user->username;
        $this->new_password = $user->password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('masters::Email.employee_resetpassword_mail')->with(['username'=>$this->username, 'password'=>$this->new_password])->subject('Reset Password');
    }
}
