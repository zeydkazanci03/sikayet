<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Profil güncelleme için doğrulama sınıfı
 * Validation class for profile updates
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Kullanıcı kendi profilini güncelleyebilir
        // User can update their own profile
        return auth()->check();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    /**
     * Özel doğrulama mesajlarını al
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'İsim gereklidir.',
            'name.min' => 'İsim en az :min karakter olmalıdır.',
            'name.max' => 'İsim en fazla :max karakter olabilir.',
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'phone.max' => 'Telefon numarası en fazla :max karakter olabilir.',
            'avatar.image' => 'Avatar bir resim dosyası olmalıdır.',
            'avatar.mimes' => 'Avatar formatı jpg, jpeg veya png olmalıdır.',
            'avatar.max' => 'Avatar boyutu en fazla 2MB olabilir.',
            'birth_date.date' => 'Geçerli bir doğum tarihi giriniz.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',
            'gender.in' => 'Geçerli bir cinsiyet seçiniz.',
            'current_password.required_with' => 'Yeni şifre belirlemek için mevcut şifrenizi girmelisiniz.',
            'current_password.current_password' => 'Mevcut şifre yanlış.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'isim',
            'email' => 'e-posta',
            'phone' => 'telefon',
            'avatar' => 'avatar',
            'birth_date' => 'doğum tarihi',
            'gender' => 'cinsiyet',
            'city' => 'şehir',
            'address' => 'adres',
            'current_password' => 'mevcut şifre',
            'password' => 'yeni şifre',
        ];
    }

    /**
     * Doğrulamadan sonra verileri hazırla
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Telefon numarasını temizle
        // Clean phone number
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $this->phone),
            ]);
        }

        // E-postayı küçük harfe çevir
        // Convert email to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email),
            ]);
        }
    }

    /**
     * Doğrulamadan sonra verileri al
     * Get validated data after validation.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Şifre alanlarını kaldır (eğer boşsa)
        // Remove password fields if empty
        if (isset($validated['current_password']) && empty($validated['current_password'])) {
            unset($validated['current_password']);
        }

        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }

        return $validated;
    }
}
