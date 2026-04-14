<?php

namespace App\Mail;

use App\Models\AuthCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public AuthCode $authCode;
    public string $code; // Le code en clair à envoyer

    public function __construct(AuthCode $authCode, string $code)
    {
        $this->authCode = $authCode;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Code d\'accès SecureAccess')
            ->view('emails.otp')
            ->with([
                'authCode' => $this->authCode,
                'code' => $this->code,
            ]);
    }
}
