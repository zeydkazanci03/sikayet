<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kategori oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for category creation and updates
 */
class CategoryRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar kategori oluşturabilir
        // Only admin users can create categories
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'parent_id' => ['nullable', 'exists:categories,id', 'different:id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Özel doğrulama kurallarını uygula
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Üst kategori döngüsünü kontrol et
            // Check for parent category loop
            if ($this->has('parent_id') && $this->parent_id) {
                $categoryId = $this->route('category') ? $this->route('category')->id : null;

                if ($categoryId && $this->parent_id == $categoryId) {
                    $validator->errors()->add('parent_id', 'Kategori kendi üst kategorisi olamaz.');
                }

                // Üst kategorinin alt kategori olup olmadığını kontrol et
                // Check if parent category is a subcategory
                if ($categoryId) {
                    $parent = \App\Models\Category::find($this->parent_id);
                    if ($parent && $this->isDescendant($categoryId, $parent)) {
                        $validator->errors()->add('parent_id', 'Döngüsel kategori ilişkisi oluşturulamaz.');
                    }
                }
            }
        });
    }

    /**
     * Kategori alt kategori mi kontrol et
     * Check if category is a descendant
     */
    private function isDescendant($categoryId, $parent)
    {
        if (!$parent->parent_id) {
            return false;
        }

        if ($parent->parent_id == $categoryId) {
            return true;
        }

        $grandParent = \App\Models\Category::find($parent->parent_id);
        if ($grandParent) {
            return $this->isDescendant($categoryId, $grandParent);
        }

        return false;
    }

    /**
     * Özel doğrulama mesajlarını al
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adı gereklidir.',
            'name.min' => 'Kategori adı en az :min karakter olmalıdır.',
            'name.max' => 'Kategori adı en fazla :max karakter olabilir.',
            'name.unique' => 'Bu kategori adı zaten kullanılıyor.',
            'slug.unique' => 'Bu URL zaten kullanılıyor.',
            'description.max' => 'Açıklama en fazla :max karakter olabilir.',
            'icon.max' => 'İkon adı en fazla :max karakter olabilir.',
            'color.regex' => 'Renk geçerli bir HEX kodu olmalıdır (örn: #FF5733).',
            'parent_id.exists' => 'Seçilen üst kategori geçerli değil.',
            'parent_id.different' => 'Kategori kendi üst kategorisi olamaz.',
            'order.integer' => 'Sıra sayı olmalıdır.',
            'order.min' => 'Sıra en az :min olmalıdır.',
            'meta_title.max' => 'Meta başlığı en fazla :max karakter olabilir.',
            'meta_description.max' => 'Meta açıklaması en fazla :max karakter olabilir.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'kategori adı',
            'slug' => 'URL',
            'description' => 'açıklama',
            'icon' => 'ikon',
            'color' => 'renk',
            'parent_id' => 'üst kategori',
            'order' => 'sıra',
            'meta_title' => 'meta başlığı',
            'meta_description' => 'meta açıklaması',
        ];
    }

    /**
     * Doğrulamadan sonra verileri hazırla
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Eğer slug yoksa isimden oluştur
        // Create slug from name if not provided
        if (!$this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Parent ID boşsa null yap
        // Set parent_id to null if empty
        if ($this->has('parent_id') && empty($this->parent_id)) {
            $this->merge([
                'parent_id' => null,
            ]);
        }
    }
}
