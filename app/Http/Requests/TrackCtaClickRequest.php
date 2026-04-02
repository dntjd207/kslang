<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackCtaClickRequest extends FormRequest
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
            'target' => ['nullable', 'string', 'max:50'],
            'source_type' => ['required', 'string', 'max:50'],
            'placement' => ['required', 'string', 'max:50'],
            'blog_post_id' => ['nullable', 'integer', 'exists:blog_posts,id'],
            'slang_id' => ['nullable', 'integer', 'exists:slangs,id'],
            'page_url' => ['nullable', 'url', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source_type.required' => 'CTA 출처 타입이 필요합니다.',
            'source_type.max' => 'CTA 출처 타입이 너무 깁니다.',
            'placement.required' => 'CTA 위치 정보가 필요합니다.',
            'placement.max' => 'CTA 위치 정보가 너무 깁니다.',
            'target.max' => 'CTA 타겟 값이 너무 깁니다.',
            'blog_post_id.exists' => '관련 블로그 글을 찾을 수 없습니다.',
            'slang_id.exists' => '관련 슬랭을 찾을 수 없습니다.',
            'page_url.url' => '페이지 URL 형식이 올바르지 않습니다.',
            'page_url.max' => '페이지 URL이 너무 깁니다.',
        ];
    }
}
