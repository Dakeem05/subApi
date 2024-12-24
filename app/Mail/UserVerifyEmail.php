<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\user\Models\User;


class UserVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $name;
    public $otp;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $name, $otp)
    {
        $this->email = $email;
        $this->name = $name;
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.VerifyEmail');
    }
}
