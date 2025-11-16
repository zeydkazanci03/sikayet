<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * Giriş formunu göster
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Kullanıcıyı oturum açar
     */
    public function login(Request $request)
    {
        // Rate limiting kontrol et
        $throttleKey = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Validation
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.exists' => 'Bu e-posta adresine sahip kullanıcı bulunamadı.',
            'password.required' => 'Şifre zorunludur.',
        ]);

        // Kullanıcı banned mı kontrol et
        $user = User::where('email', $validated['email'])->first();
        if ($user && $user->is_banned) {
            Log::warning("Banned user login attempt: {$validated['email']}");
            throw ValidationException::withMessages([
                'email' => 'Bu hesap devre dışı bırakılmıştır. Lütfen sistem yöneticisine başvurunuz.',
            ]);
        }

        // Email verified kontrol et
        if ($user && !$user->email_verified_at) {
            Log::info("Unverified email login attempt: {$validated['email']}");
            return redirect()->route('login')
                ->with('warning', 'Lütfen önce e-posta adresinizi doğrulayınız.')
                ->withInput($request->only('email'));
        }

        // Giriş dene
        if (Auth::attempt($validated, $request->boolean('remember_me'))) {
            RateLimiter::clear($throttleKey);

            // Login aktivitesi kayıt et
            Log::info("User logged in: {$user->email}", [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            // Last login zamanını güncelle
            $user->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            // Role'e göre yönel
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Hoşgeldiniz, Admin!');
            } elseif ($user->hasRole('brand')) {
                return redirect()->route('brand.dashboard')
                    ->with('success', 'Hoşgeldiniz, Marka Yöneticisi!');
            }

            return redirect()->intended('/')
                ->with('success', 'Hoşgeldiniz!');
        }

        // Login başarısız
        RateLimiter::hit($throttleKey, 60 * 60);

        Log::warning("Failed login attempt for email: {$validated['email']}", [
            'ip' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'Girilen kimlik bilgileri bizim kayıtlarımızla eşleşmiyor.',
        ]);
    }

    /**
     * Kullanıcı oturumunu kapat
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Log::info("User logged out: " . Auth::user()->email, [
                'user_id' => Auth::id(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Çıkış başarılı!');
    }

    /**
     * Login attempt throttle key'i oluştur
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Lockout event'ini tetikle
     */
    protected function fireLockoutEvent(Request $request): void
    {
        Log::warning("Too many login attempts", [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Lockout response'unu gönder
     */
    protected function sendLockoutResponse(Request $request)
    {
        return redirect()->route('login')
            ->with('error', 'Çok fazla giriş denemesinde bulundunuz. Lütfen 1 saat sonra tekrar deneyin.');
    }
}
