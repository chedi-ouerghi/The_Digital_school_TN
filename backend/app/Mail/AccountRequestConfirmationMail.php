<?php

namespace App\Mail;

use App\Models\AccountRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRequestConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $accountRequest;

    public function __construct(AccountRequest $accountRequest)
    {
        $this->accountRequest = $accountRequest;
    }

    public function build()
    {
        $confirmationUrl = config('app.frontend_url') . '/verify-email?token=' . $this->accountRequest->token;

        return $this->subject('Confirmez votre adresse email')
                    ->view('emails.account-request-confirmation')
                    ->with([
                        'name' => $this->accountRequest->name,
                        'email' => $this->accountRequest->email,
                        'token' => $this->accountRequest->token,
                        'confirmationUrl' => $confirmationUrl
                    ]);
    }
}
