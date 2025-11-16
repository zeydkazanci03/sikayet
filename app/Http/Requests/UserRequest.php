<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Kullanıcı oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for user creation and updates
 */
class UserRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar kullanıcı oluşturabilir/güncelleyebilir
        // Only admin users can create/update users
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'user_type' => ['required', Rule::in(['customer', 'brand', 'admin'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'is_banned' => ['nullable', 'boolean'],
        ];

        // Şifre sadece oluşturma sırasında zorunlu
        // Password is required only during creation
        if (!$isUpdate) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];
        } else {
            $rules['password'] = ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];
        }

        return $rules;
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
            'password.required' => 'Şifre gereklidir.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
            'user_type.required' => 'Kullanıcı tipi gereklidir.',
            'user_type.in' => 'Geçerli bir kullanıcı tipi seçiniz.',
            'avatar.image' => 'Avatar bir resim dosyası olmalıdır.',
            'avatar.mimes' => 'Avatar formatı jpg, jpeg veya png olmalıdır.',
            'avatar.max' => 'Avatar boyutu en fazla 2MB olabilir.',
            'birth_date.date' => 'Geçerli bir doğum tarihi giriniz.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',
            'gender.in' => 'Geçerli bir cinsiyet seçiniz.',
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
            'password' => 'şifre',
            'user_type' => 'kullanıcı tipi',
            'phone' => 'telefon',
            'avatar' => 'avatar',
            'birth_date' => 'doğum tarihi',
            'gender' => 'cinsiyet',
            'city' => 'şehir',
            'address' => 'adres',
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
}
