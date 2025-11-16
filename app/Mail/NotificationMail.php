<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Genel bildirim e-postası
 * General notification email
 */
class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $emailMessage;
    public ?array $data;

    /**
     * Yeni mailable instance oluştur
     * Create a new message instance.
     */
    public function __construct(string $subject, string $message, ?array $data = null)
    {
        $this->emailSubject = $subject;
        $this->emailMessage = $message;
        $this->data = $data;
    }

    /**
     * E-posta zarfını al
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * E-posta içeriğini al
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'subject' => $this->emailSubject,
                'message' => $this->emailMessage,
                'data' => $this->data,
            ],
        );
    }

    /**
     * E-posta eklerini al
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
