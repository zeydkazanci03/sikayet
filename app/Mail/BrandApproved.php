<?php

namespace App\Mail;

use App\Models\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Marka onaylandığında gönderilen e-posta
 * Email sent when a brand is approved
 */
class BrandApproved extends Mailable
{
    use Queueable, SerializesModels;

    public Brand $brand;

    /**
     * Yeni mailable instance oluştur
     * Create a new message instance.
     */
    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * E-posta zarfını al
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Markanız Onaylandı - ' . $this->brand->name,
        );
    }

    /**
     * E-posta içeriğini al
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.brand-approved',
            with: [
                'brand' => $this->brand,
                'brandUrl' => route('brands.show', $this->brand->slug),
                'dashboardUrl' => route('brand.dashboard'),
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
