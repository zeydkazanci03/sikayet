<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Şikayet oluşturulduğunda gönderilen e-posta
 * Email sent when a complaint is created
 */
class ComplaintCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Complaint $complaint;

    /**
     * Yeni mailable instance oluştur
     * Create a new message instance.
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * E-posta zarfını al
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yeni Şikayet Bildirimi - ' . $this->complaint->complaint_number,
        );
    }

    /**
     * E-posta içeriğini al
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.complaint-created',
            with: [
                'complaint' => $this->complaint,
                'brand' => $this->complaint->brand,
                'user' => $this->complaint->user,
                'complaintUrl' => route('admin.complaints.show', $this->complaint->id),
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
