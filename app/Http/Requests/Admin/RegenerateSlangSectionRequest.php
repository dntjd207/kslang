<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RegenerateSlangSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'section' => ['required', 'string', 'in:descriptions,usage_context,examples'],
            'korean' => ['nullable', 'string', 'max:255'],
            'ai_generation_hint' => ['nullable', 'string', 'max:2000'],
            'pronunciation' => ['nullable', 'string', 'max:255'],
            'english_description' => ['nullable', 'string'],
            'korean_description' => ['nullable', 'string'],
            'level' => ['nullable', 'integer', 'between:1,4'],
            'usage_frequency' => ['nullable', 'string', 'in:Common,Occasional,Rare'],
            'usage_context' => ['nullable', 'string'],
            'english_usage_context' => ['nullable', 'string'],
            'examples' => ['nullable', 'array'],
            'examples.*.id' => ['nullable', 'integer'],
            'examples.*.korean_example' => ['nullable', 'string', 'max:500'],
            'examples.*.english_example' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'section.required' => '재생성할 섹션을 선택해주세요.',
            'section.in' => '올바른 재생성 섹션이 아닙니다.',
            'korean.max' => '한국어 욕은 255자 이하여야 합니다.',
            'ai_generation_hint.max' => 'AI 참고 설명은 2000자 이하여야 합니다.',
            'pronunciation.max' => '영어 발음은 255자 이하여야 합니다.',
            'level.between' => '레벨은 1~4 사이여야 합니다.',
            'usage_frequency.in' => '올바른 사용 빈도를 선택해주세요.',
            'examples.*.korean_example.max' => '한국어 예문은 500자 이하여야 합니다.',
            'examples.*.english_example.max' => '영어 예문은 500자 이하여야 합니다.',
        ];
    }
}
