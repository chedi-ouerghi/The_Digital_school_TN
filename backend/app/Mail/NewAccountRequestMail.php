<?php

namespace App\Mail;

use App\Models\AccountRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAccountRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $accountRequest;

    public function __construct(AccountRequest $accountRequest)
    {
        $this->accountRequest = $accountRequest;
    }

    public function build()
    {
        return $this->view('emails.new-account-request')
                    ->with([
                        'name' => $this->accountRequest->name,
                        'email' => $this->accountRequest->email,
                        'token' => $this->accountRequest->token
                    ]);
    }
}
