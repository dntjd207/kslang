<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('examples')) {
            $filtered = collect($this->input('examples'))
                ->filter(function ($example) {
                    $korean = trim($example['korean_example'] ?? '');
                    $english = trim($example['english_example'] ?? '');

                    return $korean !== '' || $english !== '';
                })
                ->values()
                ->toArray();

            $this->merge(['examples' => $filtered]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'korean' => ['required', 'string', 'max:255'],
            'pronunciation' => ['required', 'string', 'max:255'],
            'english_description' => ['required', 'string'],
            'korean_description' => ['required', 'string'],
            'level' => ['required', 'integer', 'between:1,4'],
            'usage_frequency' => ['required', 'string', 'in:Common,Occasional,Rare'],
            'usage_context' => ['required', 'string'],
            'english_usage_context' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'mimetypes:audio/mpeg', 'max:5120'],
            'examples' => ['nullable', 'array'],
            'examples.*.id' => ['nullable', 'integer', 'exists:slang_examples,id'],
            'examples.*.korean_example' => ['required_with:examples.*.english_example', 'nullable', 'string', 'max:500'],
            'examples.*.english_example' => ['required_with:examples.*.korean_example', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'korean.required' => '한국어 욕을 입력해주세요.',
            'korean.max' => '한국어 욕은 255자 이하여야 합니다.',
            'pronunciation.required' => '영어 발음을 입력해주세요.',
            'pronunciation.max' => '영어 발음은 255자 이하여야 합니다.',
            'english_description.required' => '영어 설명을 입력해주세요.',
            'korean_description.required' => '한글 설명을 입력해주세요.',
            'level.required' => '레벨을 선택해주세요.',
            'level.between' => '레벨은 1~4 사이여야 합니다.',
            'usage_frequency.required' => '사용 빈도를 선택해주세요.',
            'usage_frequency.in' => '올바른 사용 빈도를 선택해주세요.',
            'usage_context.required' => '사용 상황을 입력해주세요.',
            'english_usage_context.required' => '사용 상황 영어 번역을 입력해주세요.',
            'category_ids.*.exists' => '존재하지 않는 카테고리입니다.',
            'audio_file.mimes' => 'mp3 파일만 업로드할 수 있습니다.',
            'audio_file.mimetypes' => 'mp3 파일만 업로드할 수 있습니다.',
            'audio_file.max' => '음성 파일은 5MB 이하여야 합니다.',
            'examples.*.korean_example.required_with' => '한국어 예문을 입력해주세요.',
            'examples.*.korean_example.max' => '한국어 예문은 500자 이하여야 합니다.',
            'examples.*.english_example.required_with' => '영어 번역을 입력해주세요.',
            'examples.*.english_example.max' => '영어 번역은 500자 이하여야 합니다.',
        ];
    }
}
