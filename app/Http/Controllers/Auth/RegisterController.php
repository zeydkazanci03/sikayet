<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;

class RegisterController extends SafeController
{
    /**
     * Kayıt formunu göster
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Yeni kullanıcıyı kayıt et
     */
    public function register(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
                'confirmed'
            ],
        ], [
            'name.required' => 'Ad ve soyad zorunludur.',
            'name.regex' => 'Ad ve soyad yalnızca harfleri içerebilir.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.required' => 'Şifre zorunludur.',
            'password.confirmed' => 'Şifre onaylaması eşleşmiyor.',
        ]);

        // Duplicate registration kontrol et (aynı e-posta, kısa sürede)
        if ($this->isSpamRegistration($request)) {
            Log::warning("Spam registration attempt from IP: {$request->ip()}");
            return back()->with('error', 'Çok fazla kayıt denemesi. Lütfen daha sonra tekrar deneyin.');
        }

        try {
            // Şifreyi hash'le
            $validated['password'] = Hash::make($validated['password']);

            // IP adresini kaydet
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            // User oluştur
            $user = User::create($validated);

            // Default 'user' role'ü ata
            $user->assignRole('user');

            Log::info("New user registered: {$user->email}", [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            // Registered event'ini emit et (email verification vs.)
            event(new Registered($user));

            // Otomatik giriş yap
            auth()->login($user);

            return redirect('/')
                ->with('success', 'Kayıt başarılı! Hoşgeldiniz!');

        } catch (\Exception $e) {
            Log::error("Registration error: {$e->getMessage()}", [
                'email' => $validated['email'],
                'ip' => $request->ip(),
            ]);

            return back()->with('error', 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    /**
     * Spam registration kontrolü yap
     */
    protected function isSpamRegistration(Request $request): bool
    {
        // Aynı IP'den son 5 dakikada registrasyonlar sayılır
        $recentRegistrations = User::where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        return $recentRegistrations > 2;
    }
}
