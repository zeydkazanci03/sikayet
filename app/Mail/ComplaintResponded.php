<?php

namespace App\Mail;

use App\Models\Complaint;
use App\Models\ComplaintComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Şikayete cevap verildiğinde gönderilen e-posta
 * Email sent when a complaint receives a response
 */
class ComplaintResponded extends Mailable
{
    use Queueable, SerializesModels;

    public Complaint $complaint;
    public ComplaintComment $comment;

    /**
     * Yeni mailable instance oluştur
     * Create a new message instance.
     */
    public function __construct(Complaint $complaint, ComplaintComment $comment)
    {
        $this->complaint = $complaint;
        $this->comment = $comment;
    }

    /**
     * E-posta zarfını al
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Şikayetinize Cevap Verildi - ' . $this->complaint->complaint_number,
        );
    }

    /**
     * E-posta içeriğini al
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.complaint-responded',
            with: [
                'complaint' => $this->complaint,
                'comment' => $this->comment,
                'commenter' => $this->comment->user,
                'complaintUrl' => route('complaints.show', $this->complaint->id),
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
