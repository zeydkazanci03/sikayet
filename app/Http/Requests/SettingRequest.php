<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ayar oluşturma ve güncelleme için doğrulama sınıfı
 * Validation class for setting creation and updates
 */
class SettingRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi var mı?
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sadece admin kullanıcılar ayarları değiştirebilir
        // Only admin users can modify settings
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * İstek için doğrulama kurallarını al
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $settingId = $this->route('setting') ? $this->route('setting')->id : null;

        $baseRules = [
            'key' => ['required', 'string', 'max:255', Rule::unique('settings', 'key')->ignore($settingId)],
            'value' => ['nullable'],
            'type' => ['required', Rule::in(['string', 'text', 'number', 'boolean', 'json', 'file'])],
            'group' => ['nullable', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_public' => ['nullable', 'boolean'],
        ];

        // Tipe göre özel doğrulama kuralları
        // Type-specific validation rules
        if ($this->has('type')) {
            switch ($this->type) {
                case 'number':
                    $baseRules['value'] = ['nullable', 'numeric'];
                    break;
                case 'boolean':
                    $baseRules['value'] = ['nullable', 'boolean'];
                    break;
                case 'json':
                    $baseRules['value'] = ['nullable', 'json'];
                    break;
                case 'file':
                    if ($this->hasFile('value')) {
                        $baseRules['value'] = ['nullable', 'file', 'max:10240']; // 10MB
                    }
                    break;
                case 'text':
                case 'string':
                default:
                    $baseRules['value'] = ['nullable', 'string'];
                    break;
            }
        }

        return $baseRules;
    }

    /**
     * Özel doğrulama kurallarını uygula
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // JSON değerini doğrula
            // Validate JSON value
            if ($this->type === 'json' && $this->has('value') && $this->value) {
                if (is_string($this->value)) {
                    json_decode($this->value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validator->errors()->add('value', 'Değer geçerli bir JSON formatında olmalıdır.');
                    }
                }
            }

            // Boolean değerini doğrula
            // Validate boolean value
            if ($this->type === 'boolean' && $this->has('value')) {
                if (!in_array($this->value, [0, 1, '0', '1', true, false, 'true', 'false'], true)) {
                    $validator->errors()->add('value', 'Değer true veya false olmalıdır.');
                }
            }
        });
    }

    /**
     * Özel doğrulama mesajlarını al
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Ayar anahtarı gereklidir.',
            'key.unique' => 'Bu ayar anahtarı zaten kullanılıyor.',
            'type.required' => 'Ayar tipi gereklidir.',
            'type.in' => 'Geçerli bir ayar tipi seçiniz.',
            'value.numeric' => 'Değer sayı olmalıdır.',
            'value.boolean' => 'Değer true veya false olmalıdır.',
            'value.json' => 'Değer geçerli bir JSON formatında olmalıdır.',
            'value.file' => 'Değer geçerli bir dosya olmalıdır.',
            'value.max' => 'Dosya boyutu en fazla 10MB olabilir.',
            'group.max' => 'Grup adı en fazla :max karakter olabilir.',
            'label.max' => 'Etiket en fazla :max karakter olabilir.',
            'description.max' => 'Açıklama en fazla :max karakter olabilir.',
        ];
    }

    /**
     * Alan isimlerini özelleştir
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'anahtar',
            'value' => 'değer',
            'type' => 'tip',
            'group' => 'grup',
            'label' => 'etiket',
            'description' => 'açıklama',
        ];
    }

    /**
     * Doğrulamadan sonra verileri hazırla
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Anahtarı küçük harfe çevir ve boşlukları alt çizgi ile değiştir
        // Convert key to lowercase and replace spaces with underscores
        if ($this->has('key')) {
            $this->merge([
                'key' => strtolower(str_replace(' ', '_', $this->key)),
            ]);
        }

        // Boolean değerini dönüştür
        // Convert boolean value
        if ($this->has('type') && $this->type === 'boolean' && $this->has('value')) {
            $value = $this->value;
            if (in_array($value, ['true', '1', 1], true)) {
                $this->merge(['value' => true]);
            } elseif (in_array($value, ['false', '0', 0], true)) {
                $this->merge(['value' => false]);
            }
        }
    }
}
