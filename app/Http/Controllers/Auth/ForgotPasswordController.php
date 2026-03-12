<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\SafeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends SafeController
{
    /**
     * Şifremi unuttum formunu göster
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Password reset linki gönder
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validation
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.exists' => 'Bu e-posta adresine sahip kullanıcı bulunamadı.',
        ]);

        try {
            // Password reset token oluştur
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                Log::info("Password reset link sent to email: {$request->email}");

                return back()->with('success', 'Şifre sıfırlama linki e-posta adresinize gönderildi.');
            } else {
                Log::warning("Password reset link failed for email: {$request->email}");

                return back()->with('error', 'Şifre sıfırlama linki gönderilemedi. Lütfen daha sonra tekrar deneyin.');
            }
        } catch (\Exception $e) {
            Log::error("Password reset error: {$e->getMessage()}", [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->with('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }
}
