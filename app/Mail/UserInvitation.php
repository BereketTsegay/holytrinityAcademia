<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;
    public $loginUrl;

    public function __construct(User $user, $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        $this->loginUrl = config('app.frontend_url') . '/login';
    }

    public function build()
    {
        return $this->subject('Welcome to School Management System - Account Invitation')
                    ->markdown('emails.user-invitation')
                    ->with([
                        'user' => $this->user,
                        'tempPassword' => $this->tempPassword,
                        'loginUrl' => $this->loginUrl
                    ]);
    }
}