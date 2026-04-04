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
        $model = (string) config('services.gemini.model', 'gemini-3.1-pro-preview');

        $response = $this->geminiService->generate(
            $this->buildDraftPrompt($context),
            $this->draftSchema(),
            'MEDIUM',
            $model
        );

        $data = $response->json();

        if ($data === null) {
            throw new RuntimeException('AI 초안 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $this->normalizeDraftPayload($data) + [
            'translation_model' => $model,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string>
     */
    public function translate(array $context): array
    {
        $model = (string) config('services.gemini.model', 'gemini-3.1-pro-preview');

        $response = $this->geminiService->generate(
            $this->buildTranslationPrompt($context),
            $this->translationSchema(),
            'MEDIUM',
            $model
        );

        $data = $response->json();

        if ($data === null) {
            throw new RuntimeException('AI 번역 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $this->normalizeTranslationPayload($data) + [
            'translation_model' => $model,
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
2. body_ko와 body_en은 마크다운이 아닌 HTML fragment로 작성해주세요.
3. HTML은 <h2>, <h3>, <p>, <ul>, <ol>, <li>, <blockquote>, <table>, <thead>, <tbody>, <tr>, <th>, <td>, <strong>, <em>, <a> 정도만 사용해주세요.
4. body에는 최소 3개의 H2 섹션이 있어야 합니다.
5. 한국어와 영어 모두 정보 구조가 명확해야 하며, 뜻/뉘앙스/사용 상황/주의점 중 필요한 내용을 자연스럽게 포함해주세요.
6. 과장되거나 선정적인 클릭베이트 제목은 금지합니다.
7. seo_title_en은 60자 내외, seo_description_en은 140~160자 내외로 작성해주세요.
8. JSON만 반환해주세요.
9. html, body 태그는 넣지 마세요.

## 영어 공개본 작성 규칙
- 한국어 원문의 톤, 구성, 감정적 뉘앙스를 최대한 살려서 영어로 작성해주세요. 단순 직역이나 요약이 아니라, 원문을 읽었을 때 느끼는 분위기와 에너지를 영어 독자도 느낄 수 있어야 합니다.
- 한국어 본문에 등장하는 한국어 단어, 예문, 슬랭 표현은 영어로 번역하지 말고 한국어 원문 그대로 표기하세요(예: "대박", "ㅋㅋㅋ", "아 씨발"). 필요하면 괄호 안에 짧은 영어 설명을 추가하세요.
- 미국 문화나 영어권 독자에게 어색하거나 맞지 않는 비유/표현은 미국 문화에 맞게 자연스럽게 바꿔도 됩니다. 단, 한국어 슬랭 자체의 뜻이나 뉘앙스를 왜곡하면 안 됩니다.
- 문장 구조와 표현을 다양하게 사용하세요. 같은 패턴의 문장이 반복되지 않도록 하고, 문단마다 다른 리듬과 길이를 써서 사람이 직접 쓴 것처럼 자연스럽게 작성하세요.
- "In conclusion", "Let's dive in", "Without further ado" 같은 상투적인 AI 문구는 사용하지 마세요.
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

## 핵심 번역 원칙
- 한국어 원문의 톤, 구성, 감정적 뉘앙스를 최대한 살려서 영어로 작성하세요. 원문의 분위기와 에너지가 영어 독자에게도 전달되어야 합니다.
- 한국어 본문에 등장하는 한국어 단어, 예문, 슬랭 표현은 영어로 번역하지 말고 한국어 원문 그대로 표기하세요(예: "대박", "ㅋㅋㅋ", "아 씨발"). 필요하면 괄호 안에 짧은 영어 설명을 추가하세요.
- 미국 문화나 영어권 독자에게 어색하거나 맞지 않는 비유/표현은 미국 문화에 맞게 자연스럽게 바꿔도 됩니다. 단, 한국어 슬랭 자체의 뜻이나 뉘앙스를 왜곡하면 안 됩니다.

## 글쓰기 스타일 규칙
- 문장 구조와 표현을 다양하게 사용하세요. 같은 패턴의 문장이 반복되지 않도록 하고, 문단마다 다른 리듬과 길이를 써서 사람이 직접 쓴 것처럼 자연스럽게 작성하세요.
- "In conclusion", "Let's dive in", "Without further ado", "It's important to note" 같은 상투적인 AI 문구는 절대 사용하지 마세요.
- 원문의 정보량과 깊이를 줄이거나 단순화하지 마세요. 원문에 있는 내용은 빠짐없이 영어본에도 반영되어야 합니다.

## 포맷 규칙
1. excerpt_en은 2~3문장 요약으로 작성해주세요.
2. body_en은 body_ko의 HTML 구조를 최대한 유지하면서 영어로 작성해주세요.
3. seo_title_en은 60자 내외, seo_description_en은 140~160자 내외로 작성해주세요.
4. body_en은 HTML fragment만 반환하고 html/body 태그는 넣지 마세요.
5. JSON만 반환해주세요.
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
