<?php

namespace Modules\Masters\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class EmployeeLoginEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->username = $user->username;
        $this->new_password = $user->new_password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('masters::Email.employee_mail')->with(['username'=>$this->username, 'password'=>$this->new_password])->subject('Employee login');
    }
}
