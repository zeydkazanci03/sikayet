<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Marka oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for brand creation and updates
 */
class BrandRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin kullanıcılar veya marka kullanıcıları marka oluşturabilir
        // Admin users or brand users can create brands
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isBrand());
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $brandId = $this->route('brand') ? $this->route('brand')->id : null;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brandId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'], // 4MB
            'category_id' => ['required', 'exists:categories,id'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('brands', 'email')->ignore($brandId)],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:20'],
            'trade_registry_number' => ['nullable', 'string', 'max:50'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'subscription_type' => ['nullable', Rule::in(['free', 'basic', 'premium', 'enterprise'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Özel doğrulama mesajlarını al
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Marka adı gereklidir.',
            'name.min' => 'Marka adı en az :min karakter olmalıdır.',
            'name.max' => 'Marka adı en fazla :max karakter olabilir.',
            'name.unique' => 'Bu marka adı zaten kullanılıyor.',
            'slug.unique' => 'Bu URL zaten kullanılıyor.',
            'description.max' => 'Açıklama en fazla :max karakter olabilir.',
            'logo.image' => 'Logo bir resim dosyası olmalıdır.',
            'logo.mimes' => 'Logo formatı jpg, jpeg veya png olmalıdır.',
            'logo.max' => 'Logo boyutu en fazla 2MB olabilir.',
            'banner.image' => 'Banner bir resim dosyası olmalıdır.',
            'banner.mimes' => 'Banner formatı jpg, jpeg veya png olmalıdır.',
            'banner.max' => 'Banner boyutu en fazla 4MB olabilir.',
            'category_id.required' => 'Kategori gereklidir.',
            'category_id.exists' => 'Seçilen kategori geçerli değil.',
            'website.url' => 'Geçerli bir website adresi giriniz.',
            'phone.required' => 'Telefon numarası gereklidir.',
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'founded_year.integer' => 'Kuruluş yılı sayı olmalıdır.',
            'founded_year.min' => 'Kuruluş yılı en az :min olmalıdır.',
            'founded_year.max' => 'Kuruluş yılı en fazla ' . date('Y') . ' olabilir.',
            'subscription_type.in' => 'Geçerli bir abonelik tipi seçiniz.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'marka adı',
            'slug' => 'URL',
            'description' => 'açıklama',
            'logo' => 'logo',
            'banner' => 'banner',
            'category_id' => 'kategori',
            'website' => 'website',
            'phone' => 'telefon',
            'email' => 'e-posta',
            'address' => 'adres',
            'city' => 'şehir',
            'tax_number' => 'vergi numarası',
            'trade_registry_number' => 'ticaret sicil numarası',
            'founded_year' => 'kuruluş yılı',
            'subscription_type' => 'abonelik tipi',
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
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $this->phone),
            ]);
        }
    }
}
