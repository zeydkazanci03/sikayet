<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sayfa oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for page creation and updates
 */
class PageRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar sayfa oluşturabilir
        // Only admin users can create pages
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $pageId = $this->route('page') ? $this->route('page')->id : null;

        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($pageId)],
            'content' => ['required', 'string', 'min:50'],
            'template' => ['nullable', 'string', Rule::in(['default', 'full-width', 'landing', 'contact'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'menu_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Özel doğrulama mesajlarını al
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Sayfa başlığı gereklidir.',
            'title.min' => 'Sayfa başlığı en az :min karakter olmalıdır.',
            'title.max' => 'Sayfa başlığı en fazla :max karakter olabilir.',
            'slug.unique' => 'Bu URL zaten kullanılıyor.',
            'content.required' => 'Sayfa içeriği gereklidir.',
            'content.min' => 'Sayfa içeriği en az :min karakter olmalıdır.',
            'template.in' => 'Geçerli bir şablon seçiniz.',
            'meta_title.max' => 'Meta başlığı en fazla :max karakter olabilir.',
            'meta_description.max' => 'Meta açıklaması en fazla :max karakter olabilir.',
            'meta_keywords.max' => 'Meta anahtar kelimeler en fazla :max karakter olabilir.',
            'menu_order.integer' => 'Menü sırası sayı olmalıdır.',
            'menu_order.min' => 'Menü sırası en az :min olmalıdır.',
            'published_at.date' => 'Geçerli bir yayın tarihi giriniz.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'başlık',
            'slug' => 'URL',
            'content' => 'içerik',
            'template' => 'şablon',
            'meta_title' => 'meta başlığı',
            'meta_description' => 'meta açıklaması',
            'meta_keywords' => 'meta anahtar kelimeler',
            'show_in_menu' => 'menüde göster',
            'menu_order' => 'menü sırası',
            'published_at' => 'yayın tarihi',
        ];
    }

    /**
     * Doğrulamadan sonra verileri hazırla
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Eğer slug yoksa başlıktan oluştur
        // Create slug from title if not provided
        if (!$this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title),
            ]);
        }

        // Yayınlanma durumunu kontrol et
        // Check publication status
        if ($this->has('is_published') && $this->is_published && !$this->has('published_at')) {
            $this->merge([
                'published_at' => now(),
            ]);
        }

        // Varsayılan şablon
        // Default template
        if (!$this->has('template') || empty($this->template)) {
            $this->merge([
                'template' => 'default',
            ]);
        }
    }
}
