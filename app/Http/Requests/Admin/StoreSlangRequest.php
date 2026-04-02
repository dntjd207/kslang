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
            'public_slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:slangs,public_slug'],
            'public_title_en' => ['nullable', 'string', 'max:255'],
            'public_summary_en' => ['nullable', 'string', 'max:1000'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_description_en' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'mimetypes:audio/mpeg', 'max:5120'],
            'examples' => ['nullable', 'array'],
            'examples.*.id' => ['nullable', 'integer', 'exists:slang_examples,id'],
            'examples.*.korean_example' => ['required_with:examples.*.english_example', 'nullable', 'string', 'max:500'],
            'examples.*.english_example' => ['required_with:examples.*.korean_example', 'nullable', 'string', 'max:500'],
            'examples.*.audio_file' => ['nullable', 'string', 'max:500'],
            'examples.*.audio_disk' => ['nullable', 'string', 'max:50'],
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
            'public_slug.regex' => '공개 슬러그는 영문 소문자, 숫자, 하이픈만 사용할 수 있습니다.',
            'public_slug.unique' => '이미 사용 중인 공개 슬러그입니다.',
            'public_slug.max' => '공개 슬러그는 255자 이하여야 합니다.',
            'public_title_en.max' => '공개 영어 제목은 255자 이하여야 합니다.',
            'public_summary_en.max' => '공개 영어 요약은 1000자 이하여야 합니다.',
            'seo_title_en.max' => 'SEO 제목은 255자 이하여야 합니다.',
            'seo_description_en.max' => 'SEO 설명은 500자 이하여야 합니다.',
            'category_ids.*.exists' => '존재하지 않는 카테고리입니다.',
            'audio_file.mimes' => 'mp3 파일만 업로드할 수 있습니다.',
            'audio_file.mimetypes' => 'mp3 파일만 업로드할 수 있습니다.',
            'audio_file.max' => '음성 파일은 5MB 이하여야 합니다.',
            'examples.*.korean_example.required_with' => '한국어 예문을 입력해주세요.',
            'examples.*.korean_example.max' => '한국어 예문은 500자 이하여야 합니다.',
            'examples.*.english_example.required_with' => '영어 번역을 입력해주세요.',
            'examples.*.english_example.max' => '영어 번역은 500자 이하여야 합니다.',
            'examples.*.audio_file.max' => '예문 음성 경로가 너무 깁니다.',
            'examples.*.audio_disk.max' => '예문 음성 디스크 값이 너무 깁니다.',
        ];
    }
}
