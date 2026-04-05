<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;

class TranslateBlogPostRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255'],
            'title_ko' => ['required', 'string', 'max:255'],
            'excerpt_ko' => ['nullable', 'string', 'max:1000'],
            'body_ko' => ['required', 'string', 'max:2000000'],
        ];
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
            'title_ko.required' => '영문 번역을 생성하려면 한국어 제목이 필요합니다.',
            'title_ko.max' => '한국어 제목은 255자 이하여야 합니다.',
            'excerpt_ko.max' => '한국어 요약은 1000자 이하여야 합니다.',
            'body_ko.required' => '영문 번역을 생성하려면 한국어 본문이 필요합니다.',
            'body_ko.max' => '한국어 본문이 너무 깁니다.',
        ];
    }
}
