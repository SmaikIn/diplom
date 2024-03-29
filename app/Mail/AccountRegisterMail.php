<?php

namespace App\Mail;

use App\Services\Mail\Dto\UserDto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly UserDto $userDto
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Регистрация аккаунта',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-register',
            with: [
                'user' => $this->userDto
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
