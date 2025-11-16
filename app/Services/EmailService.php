<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificationMail;
use Exception;

/**
 * E-posta gönderme işlemleri servis sınıfı
 * Service class for email sending operations
 */
class EmailService
{
    /**
     * E-posta gönder
     * Send email
     */
    public function send(string $to, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->send($mailable);

            Log::info('Email sent successfully', [
                'to' => $to,
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error sending email', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Birden fazla alıcıya e-posta gönder
     * Send email to multiple recipients
     */
    public function sendToMultiple(array $recipients, Mailable $mailable): bool
    {
        try {
            Mail::to($recipients)->send($mailable);

            Log::info('Email sent to multiple recipients', [
                'recipients_count' => count($recipients),
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error sending email to multiple recipients', [
                'recipients_count' => count($recipients),
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * CC ile e-posta gönder
     * Send email with CC
     */
    public function sendWithCC(string $to, array $cc, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->cc($cc)->send($mailable);

            Log::info('Email sent with CC', [
                'to' => $to,
                'cc_count' => count($cc),
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error sending email with CC', [
                'to' => $to,
                'cc_count' => count($cc),
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * BCC ile e-posta gönder
     * Send email with BCC
     */
    public function sendWithBCC(string $to, array $bcc, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->bcc($bcc)->send($mailable);

            Log::info('Email sent with BCC', [
                'to' => $to,
                'bcc_count' => count($bcc),
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error sending email with BCC', [
                'to' => $to,
                'bcc_count' => count($bcc),
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Kuyruğa ekleyerek e-posta gönder
     * Queue email for sending
     */
    public function queue(string $to, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->queue($mailable);

            Log::info('Email queued successfully', [
                'to' => $to,
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error queueing email', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Gecikmeli e-posta gönder
     * Send delayed email
     */
    public function later(\DateTimeInterface|\DateInterval|int $delay, string $to, Mailable $mailable): bool
    {
        try {
            Mail::to($to)->later($delay, $mailable);

            Log::info('Email scheduled successfully', [
                'to' => $to,
                'delay' => $delay,
                'mailable' => get_class($mailable),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error scheduling email', [
                'to' => $to,
                'delay' => $delay,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Basit bildirim e-postası gönder
     * Send simple notification email
     */
    public function sendNotification(string $to, string $subject, string $message): bool
    {
        return $this->send($to, new NotificationMail($subject, $message));
    }

    /**
     * Admin kullanıcılara toplu e-posta gönder
     * Send bulk email to admins
     */
    public function sendToAdmins(Mailable $mailable): bool
    {
        try {
            $admins = \App\Models\User::where('user_type', 'admin')
                ->where('is_active', true)
                ->pluck('email')
                ->toArray();

            if (empty($admins)) {
                Log::warning('No admin emails found');
                return false;
            }

            return $this->sendToMultiple($admins, $mailable);

        } catch (Exception $e) {
            Log::error('Error sending email to admins', [
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * E-posta adresini doğrula
     * Validate email address
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Toplu e-posta gönder (her birine ayrı ayrı)
     * Send bulk emails (individually to each recipient)
     */
    public function sendBulk(array $recipients, Mailable $mailable): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($recipients as $recipient) {
            if ($this->send($recipient, clone $mailable)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $recipient;
            }
        }

        Log::info('Bulk email sending completed', $results);

        return $results;
    }
}
