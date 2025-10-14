<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $mail;
    protected $title;

    public function __construct($mail)
    {
        $this->mail = $mail;
        $this->title = sprintf('Reset your password - %s!', config('app.name'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.account-forgot-password',
            with: [
                'mail' => $this->mail,
                'subject' => $this->title
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
