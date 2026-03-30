<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DetailedStoreSlangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'korean' => trim((string) $this->input('korean', '')),
            'ai_generation_hint' => trim((string) $this->input('ai_generation_hint', '')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'korean' => ['required', 'string', 'max:255'],
            'ai_generation_hint' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'korean.required' => '단어를 입력해주세요.',
            'korean.max' => '단어는 255자 이하여야 합니다.',
            'ai_generation_hint.required' => '단어 설명을 입력해주세요.',
            'ai_generation_hint.max' => '단어 설명은 2000자 이하여야 합니다.',
        ];
    }
}
