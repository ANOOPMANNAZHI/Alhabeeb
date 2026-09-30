<?php

namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

/** Any system template email: subject + rendered HTML inside the PLMS mail layout. */
class TemplatedMail extends Mailable
{
    use Queueable;

    /** Rendered template body. Not "$html": Mailable::$html would bypass the layout view. */
    public $bodyHtml;
    private $files;

    public function __construct($subject, $html, array $attachments = [])
    {
        $this->subject = $subject;
        $this->bodyHtml = $html;
        $this->files = $attachments;
    }

    public function build()
    {
        $mail = $this->view('backoffice::Email.templated', ['bodyHtml' => $this->bodyHtml]);
        foreach ($this->files as $f) {
            $mail->attachData($f['data'], $f['name'], ['mime' => $f['mime']]);
        }
        return $mail;
    }
}
