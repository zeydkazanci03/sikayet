<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends SafeController
{
    /**
     * Şifre sıfırlama formunu göster
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Şifreyi sıfırla
     */
    public function reset(Request $request)
    {
        // Validation
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
                'confirmed'
            ],
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.exists' => 'Bu e-posta adresine sahip kullanıcı bulunamadı.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Şifre onaylaması eşleşmiyor.',
        ]);

        try {
            // Şifreyi sıfırla
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    Log::info("Password reset for user: {$user->email}", [
                        'user_id' => $user->id,
                    ]);
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                Log::info("Password successfully reset for email: {$request->email}");

                return redirect('/login')->with('success', 'Şifreniz başarıyla sıfırlandı. Lütfen yeni şifrenizle giriş yapın.');
            } else {
                Log::warning("Password reset token invalid for email: {$request->email}");

                return back()->with('error', 'Şifre sıfırlama linki geçersiz veya süresi dolmuş.');
            }
        } catch (\Exception $e) {
            Log::error("Password reset error: {$e->getMessage()}", [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->with('error', 'Şifre sıfırlama sırasında bir hata oluştu.');
        }
    }
}
