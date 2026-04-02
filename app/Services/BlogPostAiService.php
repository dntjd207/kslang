<?php

namespace App\Services;

use RuntimeException;

class BlogPostAiService
{
    public function __construct(
        private GeminiService $geminiService
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string>
     */
    public function generateDraft(array $context): array
    {
        $response = $this->geminiService->generate(
            $this->buildDraftPrompt($context),
            $this->draftSchema(),
            'MEDIUM'
        );

        $data = $response->json();

        if ($data === null) {
            throw new RuntimeException('AI 초안 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $this->normalizeDraftPayload($data) + [
            'translation_model' => (string) config('services.gemini.translation_model', 'gemini-3.1-flash-lite-preview'),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string>
     */
    public function translate(array $context): array
    {
        $translationModel = (string) config('services.gemini.translation_model', 'gemini-3.1-flash-lite-preview');

        $response = $this->geminiService->generate(
            $this->buildTranslationPrompt($context),
            $this->translationSchema(),
            'LOW',
            $translationModel
        );

        $data = $response->json();

        if ($data === null) {
            throw new RuntimeException('AI 번역 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $this->normalizeTranslationPayload($data) + [
            'translation_model' => $translationModel,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildDraftPrompt(array $context): string
    {
        $searchIntent = trim((string) ($context['search_intent'] ?? ''));
        $categoryName = trim((string) ($context['category_name'] ?? ''));
        $tagNames = trim((string) ($context['tag_names'] ?? ''));
        $primaryKeyword = trim((string) ($context['primary_keyword'] ?? ''));
        $contentBrief = trim((string) ($context['content_brief_ko'] ?? ''));
        $titleKo = trim((string) ($context['title_ko'] ?? ''));
        $excerptKo = trim((string) ($context['excerpt_ko'] ?? ''));
        $bodyKo = trim((string) ($context['body_ko'] ?? ''));

        return <<<PROMPT
당신은 영어 SEO 유입을 노리는 한국어 슬랭/문화 콘텐츠 편집자입니다.
운영자는 한국인이고, 먼저 한국어 기준 초안을 검수한 뒤 영어 공개본을 다듬습니다.
아래 정보를 바탕으로 블로그 글 초안을 작성해주세요.

## 입력 정보
- search_intent: {$searchIntent}
- category_name: {$categoryName}
- tag_names: {$tagNames}
- primary_keyword: {$primaryKeyword}
- content_brief_ko: {$contentBrief}
- current_title_ko: {$titleKo}
- current_excerpt_ko: {$excerptKo}
- current_body_ko: {$bodyKo}

## 중요한 작성 원칙
1. 한국어 원본과 영어 공개본을 함께 반환해주세요.
2. 영어는 번역투보다 자연스러운 SEO 친화 문장으로 작성해주세요.
3. body_ko와 body_en은 마크다운이 아닌 HTML fragment로 작성해주세요.
4. HTML은 <h2>, <h3>, <p>, <ul>, <ol>, <li>, <blockquote>, <table>, <thead>, <tbody>, <tr>, <th>, <td>, <strong>, <em>, <a> 정도만 사용해주세요.
5. body에는 최소 3개의 H2 섹션이 있어야 합니다.
6. 한국어와 영어 모두 정보 구조가 명확해야 하며, 뜻/뉘앙스/사용 상황/주의점 중 필요한 내용을 자연스럽게 포함해주세요.
7. 과장되거나 선정적인 클릭베이트 제목은 금지합니다.
8. seo_title_en은 60자 내외, seo_description_en은 140~160자 내외로 작성해주세요.
9. JSON만 반환해주세요.
10. html, body 태그는 넣지 마세요.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildTranslationPrompt(array $context): string
    {
        $searchIntent = trim((string) ($context['search_intent'] ?? ''));
        $categoryName = trim((string) ($context['category_name'] ?? ''));
        $tagNames = trim((string) ($context['tag_names'] ?? ''));
        $primaryKeyword = trim((string) ($context['primary_keyword'] ?? ''));
        $titleKo = trim((string) ($context['title_ko'] ?? ''));
        $excerptKo = trim((string) ($context['excerpt_ko'] ?? ''));
        $bodyKo = trim((string) ($context['body_ko'] ?? ''));

        return <<<PROMPT
당신은 한국어 원본을 영어 SEO 콘텐츠로 현지화하는 전문 편집자입니다.
아래 한국어 원본을 바탕으로 영어 공개본만 작성해주세요.

## 입력 정보
- search_intent: {$searchIntent}
- category_name: {$categoryName}
- tag_names: {$tagNames}
- primary_keyword: {$primaryKeyword}
- title_ko: {$titleKo}
- excerpt_ko: {$excerptKo}
- body_ko:
{$bodyKo}

## 번역 및 현지화 규칙
1. 영어 제목은 검색 사용자에게 자연스럽고 명확해야 합니다.
2. excerpt_en은 2~3문장 요약으로 작성해주세요.
3. body_en은 body_ko의 HTML 구조를 최대한 유지하면서 자연스러운 영어로 바꿔주세요.
4. 직역보다 의미 전달과 검색 친화성을 우선해주세요.
5. 한국 문화/인터넷 맥락이 필요한 부분은 영어 독자가 이해할 수 있게 자연스럽게 풀어주세요.
6. seo_title_en은 60자 내외, seo_description_en은 140~160자 내외로 작성해주세요.
7. body_en은 HTML fragment만 반환하고 html/body 태그는 넣지 마세요.
8. JSON만 반환해주세요.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function draftSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'title_ko' => ['type' => 'STRING'],
                'excerpt_ko' => ['type' => 'STRING'],
                'body_ko' => ['type' => 'STRING'],
                'title_en' => ['type' => 'STRING'],
                'excerpt_en' => ['type' => 'STRING'],
                'body_en' => ['type' => 'STRING'],
                'seo_title_en' => ['type' => 'STRING'],
                'seo_description_en' => ['type' => 'STRING'],
            ],
            'required' => [
                'title_ko',
                'excerpt_ko',
                'body_ko',
                'title_en',
                'excerpt_en',
                'body_en',
                'seo_title_en',
                'seo_description_en',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function translationSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'title_en' => ['type' => 'STRING'],
                'excerpt_en' => ['type' => 'STRING'],
                'body_en' => ['type' => 'STRING'],
                'seo_title_en' => ['type' => 'STRING'],
                'seo_description_en' => ['type' => 'STRING'],
            ],
            'required' => [
                'title_en',
                'excerpt_en',
                'body_en',
                'seo_title_en',
                'seo_description_en',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function normalizeDraftPayload(array $data): array
    {
        return [
            'title_ko' => trim((string) ($data['title_ko'] ?? '')),
            'excerpt_ko' => trim((string) ($data['excerpt_ko'] ?? '')),
            'body_ko' => trim((string) ($data['body_ko'] ?? '')),
            'title_en' => trim((string) ($data['title_en'] ?? '')),
            'excerpt_en' => trim((string) ($data['excerpt_en'] ?? '')),
            'body_en' => trim((string) ($data['body_en'] ?? '')),
            'seo_title_en' => trim((string) ($data['seo_title_en'] ?? '')),
            'seo_description_en' => trim((string) ($data['seo_description_en'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function normalizeTranslationPayload(array $data): array
    {
        return [
            'title_en' => trim((string) ($data['title_en'] ?? '')),
            'excerpt_en' => trim((string) ($data['excerpt_en'] ?? '')),
            'body_en' => trim((string) ($data['body_en'] ?? '')),
            'seo_title_en' => trim((string) ($data['seo_title_en'] ?? '')),
            'seo_description_en' => trim((string) ($data['seo_description_en'] ?? '')),
        ];
    }
}
