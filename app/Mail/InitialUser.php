<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InitialUser extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $initpass;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $initpass)
    {
        $this->user = $user;
        $this->initpass = $initpass;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Initial User')
                    ->markdown('emails.initial-user');
    }
}
