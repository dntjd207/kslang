<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogPost;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AutoSaveBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var BlogPost|null $blogPost */
        $blogPost = $this->filled('blog_post_id')
            ? BlogPost::query()->find($this->integer('blog_post_id'))
            : null;

        return [
            'blog_post_id' => ['nullable', 'integer', 'exists:blog_posts,id'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->ignore($blogPost?->id),
            ],
            'category_name' => ['nullable', 'string', 'max:255'],
            'tag_names' => ['nullable', 'string', 'max:1000'],
            'search_intent' => ['nullable', 'string', 'in:'.implode(',', BlogPost::SEARCH_INTENTS)],
            'primary_keyword' => ['nullable', 'string', 'max:255'],
            'content_brief_ko' => ['nullable', 'string', 'max:5000'],
            'title_ko' => ['nullable', 'string', 'max:255'],
            'excerpt_ko' => ['nullable', 'string', 'max:1000'],
            'body_ko' => ['nullable', 'string', 'max:2000000'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'excerpt_en' => ['nullable', 'string', 'max:1000'],
            'body_en' => ['nullable', 'string', 'max:2000000'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_description_en' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'translation_model' => ['nullable', 'string', 'max:120'],
            'related_slang_ids' => ['nullable', 'array'],
            'related_slang_ids.*' => ['integer', 'exists:slangs,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'blog_post_id.exists' => '자동 저장 대상 블로그 글을 찾을 수 없습니다.',
            'slug.regex' => '슬러그는 영문 소문자, 숫자, 하이픈만 사용할 수 있습니다.',
            'slug.unique' => '이미 사용 중인 슬러그입니다.',
            'slug.max' => '슬러그는 255자 이하여야 합니다.',
            'category_name.max' => '카테고리 이름은 255자 이하여야 합니다.',
            'tag_names.max' => '태그 입력이 너무 깁니다.',
            'search_intent.in' => '올바른 검색 의도를 선택해주세요.',
            'primary_keyword.max' => '핵심 키워드는 255자 이하여야 합니다.',
            'content_brief_ko.max' => '콘텐츠 브리프는 5000자 이하여야 합니다.',
            'title_ko.max' => '한국어 제목은 255자 이하여야 합니다.',
            'excerpt_ko.max' => '한국어 요약은 1000자 이하여야 합니다.',
            'body_ko.max' => '한국어 본문이 너무 깁니다.',
            'title_en.max' => '영어 제목은 255자 이하여야 합니다.',
            'excerpt_en.max' => '영어 요약은 1000자 이하여야 합니다.',
            'body_en.max' => '영어 본문이 너무 깁니다.',
            'seo_title_en.max' => 'SEO 제목은 255자 이하여야 합니다.',
            'seo_description_en.max' => 'SEO 설명은 500자 이하여야 합니다.',
            'canonical_url.url' => 'Canonical URL 형식이 올바르지 않습니다.',
            'canonical_url.max' => 'Canonical URL은 255자 이하여야 합니다.',
            'translation_model.max' => '번역 모델 이름이 너무 깁니다.',
            'related_slang_ids.*.exists' => '존재하지 않는 슬랭이 포함되어 있습니다.',
        ];
    }
}
