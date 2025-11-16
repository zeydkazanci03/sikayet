<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Şikayet oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for complaint creation and updates
 */
class ComplaintRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Şikayet oluşturmak için kullanıcının aktif olması gerekir
        // User must be active to create a complaint
        if ($this->isMethod('POST')) {
            return auth()->check() && auth()->user()->is_active && !auth()->user()->is_banned;
        }

        // Şikayeti güncellemek için kullanıcının sahibi olması gerekir
        // User must own the complaint to update it
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $complaint = $this->route('complaint');
            return $complaint && $complaint->user_id === auth()->id();
        }

        return false;
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'content' => ['required', 'string', 'min:50', 'max:5000'],
            'resolution_expectation' => ['nullable', 'string', 'max:1000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'], // 5MB
        ];

        // Güncelleme için ek kurallar
        // Additional rules for updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'in_progress', 'resolved', 'closed'])];
            $rules['customer_satisfaction_rating'] = ['nullable', 'integer', 'min:1', 'max:5'];
            $rules['customer_satisfaction_comment'] = ['nullable', 'string', 'max:1000'];
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
            'brand_id.required' => 'Lütfen bir marka seçin.',
            'brand_id.exists' => 'Seçilen marka geçerli değil.',
            'category_id.required' => 'Lütfen bir kategori seçin.',
            'category_id.exists' => 'Seçilen kategori geçerli değil.',
            'title.required' => 'Şikayet başlığı gereklidir.',
            'title.min' => 'Şikayet başlığı en az :min karakter olmalıdır.',
            'title.max' => 'Şikayet başlığı en fazla :max karakter olabilir.',
            'content.required' => 'Şikayet içeriği gereklidir.',
            'content.min' => 'Şikayet içeriği en az :min karakter olmalıdır.',
            'content.max' => 'Şikayet içeriği en fazla :max karakter olabilir.',
            'resolution_expectation.max' => 'Çözüm beklentisi en fazla :max karakter olabilir.',
            'attachments.max' => 'En fazla :max dosya yükleyebilirsiniz.',
            'attachments.*.file' => 'Yüklenen dosya geçerli bir dosya olmalıdır.',
            'attachments.*.mimes' => 'Dosya formatı jpg, jpeg, png, pdf, doc veya docx olmalıdır.',
            'attachments.*.max' => 'Dosya boyutu en fazla 5MB olabilir.',
            'customer_satisfaction_rating.integer' => 'Memnuniyet puanı sayı olmalıdır.',
            'customer_satisfaction_rating.min' => 'Memnuniyet puanı en az :min olmalıdır.',
            'customer_satisfaction_rating.max' => 'Memnuniyet puanı en fazla :max olabilir.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'brand_id' => 'marka',
            'category_id' => 'kategori',
            'title' => 'başlık',
            'content' => 'içerik',
            'resolution_expectation' => 'çözüm beklentisi',
            'attachments' => 'ekler',
            'customer_satisfaction_rating' => 'memnuniyet puanı',
            'customer_satisfaction_comment' => 'memnuniyet yorumu',
        ];
    }

    /**
     * Doğrulamadan sonra verileri hazırla
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Başlık ve içeriği temizle
        // Clean title and content
        if ($this->has('title')) {
            $this->merge([
                'title' => strip_tags($this->title),
            ]);
        }

        if ($this->has('content')) {
            $this->merge([
                'content' => strip_tags($this->content, '<p><br><b><i><u><strong><em>'),
            ]);
        }
    }
}
