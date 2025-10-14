<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $mail;
    protected $type;
    protected $title;

    public function __construct($mail, $type)
    {
        $this->mail = $mail;
        $this->type = $type;

        $this->title = $type === 'admin'
            ? sprintf('New user registered at %s!', config('app.name'))
            : sprintf('Welcome to %s!', config('app.name'));
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
        $view = $this->type === 'admin'
        ? 'mail.account-register-admin'
        : 'mail.account-register-user';

        return new Content(
            view: $view,
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
