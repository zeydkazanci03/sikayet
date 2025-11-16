<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Blog yazısı oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for blog post creation and updates
 */
class BlogPostRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar blog yazısı oluşturabilir
        // Only admin users can create blog posts
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $postId = $this->route('post') ? $this->route('post')->id : null;

        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($postId)],
            'content' => ['required', 'string', 'min:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'], // 4MB
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
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
            'title.required' => 'Başlık gereklidir.',
            'title.min' => 'Başlık en az :min karakter olmalıdır.',
            'title.max' => 'Başlık en fazla :max karakter olabilir.',
            'slug.unique' => 'Bu URL zaten kullanılıyor.',
            'content.required' => 'İçerik gereklidir.',
            'content.min' => 'İçerik en az :min karakter olmalıdır.',
            'excerpt.max' => 'Özet en fazla :max karakter olabilir.',
            'featured_image.image' => 'Öne çıkan görsel bir resim dosyası olmalıdır.',
            'featured_image.mimes' => 'Öne çıkan görsel formatı jpg, jpeg veya png olmalıdır.',
            'featured_image.max' => 'Öne çıkan görsel boyutu en fazla 4MB olabilir.',
            'category_id.exists' => 'Seçilen kategori geçerli değil.',
            'tags.max' => 'Etiketler en fazla :max karakter olabilir.',
            'meta_title.max' => 'Meta başlığı en fazla :max karakter olabilir.',
            'meta_description.max' => 'Meta açıklaması en fazla :max karakter olabilir.',
            'meta_keywords.max' => 'Meta anahtar kelimeler en fazla :max karakter olabilir.',
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
            'excerpt' => 'özet',
            'featured_image' => 'öne çıkan görsel',
            'category_id' => 'kategori',
            'tags' => 'etiketler',
            'meta_title' => 'meta başlığı',
            'meta_description' => 'meta açıklaması',
            'meta_keywords' => 'meta anahtar kelimeler',
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
    }
}
