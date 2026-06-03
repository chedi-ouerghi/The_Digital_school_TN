<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class TempPasswordMail extends Mailable
{
	use Queueable, SerializesModels;

	public $user;
	public $tempPassword;

	public function __construct(User $user, string $tempPassword)
	{
		$this->user = $user;
		$this->tempPassword = $tempPassword;
	}

	public function build()
	{
		// Simple plain-text mail (adapter pour vos vues si besoin)
		return $this->subject('Votre compte a été créé - mot de passe temporaire')
		            ->view('emails.temp_password') // si vous avez une view, sinon utiliser ->text(...)
		            ->with([
						'userName' => $this->user->name,
						'email' => $this->user->email,
						'tempPassword' => $this->tempPassword,
						'note' => "Veuillez vous connecter et mettre à jour votre mot de passe via votre profil."
					]);
	}
}
