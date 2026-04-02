<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateBlogPostDraftRequest extends FormRequest
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
            'category_name' => ['nullable', 'string', 'max:255'],
            'tag_names' => ['nullable', 'string', 'max:1000'],
            'search_intent' => ['nullable', 'string', 'in:'.implode(',', BlogPost::SEARCH_INTENTS)],
            'primary_keyword' => ['nullable', 'string', 'max:255'],
            'content_brief_ko' => ['nullable', 'string', 'max:5000'],
            'title_ko' => ['nullable', 'string', 'max:255'],
            'excerpt_ko' => ['nullable', 'string', 'max:1000'],
            'body_ko' => ['nullable', 'string', 'max:2000000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasSeedContent = collect([
                $this->input('primary_keyword'),
                $this->input('content_brief_ko'),
                $this->input('title_ko'),
                $this->input('body_ko'),
            ])->contains(fn ($value) => trim((string) $value) !== '');

            if (! $hasSeedContent) {
                $validator->errors()->add('primary_keyword', 'AI 초안을 만들려면 핵심 키워드나 브리프를 입력해주세요.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search_intent.in' => '올바른 검색 의도를 선택해주세요.',
            'category_name.max' => '카테고리 이름은 255자 이하여야 합니다.',
            'tag_names.max' => '태그 입력이 너무 깁니다.',
            'primary_keyword.max' => '핵심 키워드는 255자 이하여야 합니다.',
            'content_brief_ko.max' => '콘텐츠 브리프는 5000자 이하여야 합니다.',
            'title_ko.max' => '한국어 제목은 255자 이하여야 합니다.',
            'excerpt_ko.max' => '한국어 요약은 1000자 이하여야 합니다.',
            'body_ko.max' => '한국어 본문이 너무 깁니다.',
        ];
    }
}
