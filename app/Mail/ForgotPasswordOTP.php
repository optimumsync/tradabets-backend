<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordOTP extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $content;
    public $subjectLine;
    public $fromEmail;
    public $fromName;

    public function __construct($content, $subjectLine, $fromEmail, $fromName)
    {
        $this->content = $content;
        $this->subjectLine = $subjectLine;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function build()
    {
        return $this->from($this->fromEmail, $this->fromName)
                    ->subject($this->subjectLine)
                    ->view('emails.forgot_password_otp')
                    ->with(['content' => $this->content]);
    }
}
