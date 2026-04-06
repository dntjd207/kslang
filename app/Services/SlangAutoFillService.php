<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Slang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SlangAutoFillService
{
    public function __construct(
        private GeminiService $geminiService
    ) {}

    /**
     * pending 상태의 슬랭을 찾아 Gemini로 콘텐츠를 자동 생성.
     *
     * @return array{filled: int, failed: int}
     */
    public function fillPendingSlangs(int $limit = 5): array
    {
        $pendingSlangs = Slang::where('content_status', Slang::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $filled = 0;
        $failed = 0;

        foreach ($pendingSlangs as $slang) {
            try {
                $this->fillSlang($slang);
                $filled++;
            } catch (\Throwable $e) {
                Log::error("SlangAutoFill 실패: {$slang->korean} (ID: {$slang->id})", [
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        return compact('filled', 'failed');
    }

    /**
     * 단일 슬랭에 대해 Gemini API를 호출하여 콘텐츠를 채움.
     */
    public function fillSlang(Slang $slang): Slang
    {
        $categories = Category::orderBy('sort_order')->pluck('name')->toArray();
        $prompt = $this->buildPrompt($slang->korean, $categories, $slang->ai_generation_hint);
        $schema = $this->buildResponseSchema();

        $data = $this->generateStructuredData($prompt, $schema);

        return $this->applyGeneratedData($slang, $data);
    }

    /**
     * 단일 슬랭의 SEO 필드를 생성하고 즉시 DB에 저장.
     */
    public function generateAndSaveSeoFields(Slang $slang): Slang
    {
        $data = $this->generateSeoFields($slang);

        $slang->update([
            'public_title_en' => $data['public_title_en'] ?: null,
            'public_summary_en' => $data['public_summary_en'] ?: null,
            'seo_title_en' => $data['seo_title_en'] ?: null,
            'seo_description_en' => $data['seo_description_en'] ?: null,
            'seo_keywords_en' => $data['seo_keywords_en'] ?: null,
        ]);

        return $slang->refresh();
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{english_description: string, korean_description: string}
     */
    public function regenerateDescriptions(Slang $slang, array $context = []): array
    {
        $slangContext = $this->buildSlangContext($slang, $context);

        $prompt = <<<PROMPT
당신은 한국어 욕설/슬랭 사전 편집자입니다.
아래 표현의 설명 섹션만 다시 작성해주세요.

## 대상 단어
{$slangContext['korean']}

{$this->buildAiHintSection($slangContext['ai_generation_hint'])}

## 참고 정보
- pronunciation: {$slangContext['pronunciation']}
- level: {$slangContext['level']}
- usage_frequency: {$slangContext['usage_frequency']}
- current english_description: {$slangContext['english_description']}
- current korean_description: {$slangContext['korean_description']}
- current usage_context: {$slangContext['usage_context']}
- current english_usage_context: {$slangContext['english_usage_context']}

## 작성 규칙
1. english_description: 영어 설명 2~3문장
2. korean_description: 한국어 설명 2~3문장
3. 두 설명은 같은 의미와 뉘앙스를 담아야 합니다.
4. 의미, 감정 톤, 문화적 맥락을 자연스럽게 반영해주세요.
5. JSON만 반환해주세요.
PROMPT;

        $data = $this->generateStructuredData($prompt, [
            'type' => 'OBJECT',
            'properties' => [
                'english_description' => [
                    'type' => 'STRING',
                    'description' => 'Detailed English description (2-3 sentences)',
                ],
                'korean_description' => [
                    'type' => 'STRING',
                    'description' => 'Detailed Korean description (2-3 sentences)',
                ],
            ],
            'required' => ['english_description', 'korean_description'],
        ]);

        return [
            'english_description' => trim((string) ($data['english_description'] ?? '')),
            'korean_description' => trim((string) ($data['korean_description'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{usage_context: string, english_usage_context: string}
     */
    public function regenerateUsageContext(Slang $slang, array $context = []): array
    {
        $slangContext = $this->buildSlangContext($slang, $context);

        $prompt = <<<PROMPT
당신은 한국어 욕설/슬랭 사전 편집자입니다.
아래 표현의 사용 상황 섹션만 다시 작성해주세요.

## 대상 단어
{$slangContext['korean']}

{$this->buildAiHintSection($slangContext['ai_generation_hint'])}

## 참고 정보
- pronunciation: {$slangContext['pronunciation']}
- level: {$slangContext['level']}
- usage_frequency: {$slangContext['usage_frequency']}
- english_description: {$slangContext['english_description']}
- korean_description: {$slangContext['korean_description']}

## 작성 규칙
1. usage_context: 한국어 사용 상황 설명 2~3문장
2. english_usage_context: usage_context의 자연스러운 영어 번역 2~3문장
3. 두 문장은 같은 상황과 뉘앙스를 반영해야 합니다.
4. 실제 사용되는 장면, 감정, 대화 맥락이 드러나게 작성해주세요.
5. JSON만 반환해주세요.
PROMPT;

        $data = $this->generateStructuredData($prompt, [
            'type' => 'OBJECT',
            'properties' => [
                'usage_context' => [
                    'type' => 'STRING',
                    'description' => 'Context and situations where the word is commonly used in Korean (2-3 sentences)',
                ],
                'english_usage_context' => [
                    'type' => 'STRING',
                    'description' => 'Natural English translation of usage_context (2-3 sentences)',
                ],
            ],
            'required' => ['usage_context', 'english_usage_context'],
        ]);

        return [
            'usage_context' => trim((string) ($data['usage_context'] ?? '')),
            'english_usage_context' => trim((string) ($data['english_usage_context'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{examples: array<int, array{korean_example: string, english_example: string}>}
     */
    public function generateAdditionalExamples(Slang $slang, array $context = [], int $count = 3): array
    {
        $slangContext = $this->buildSlangContext($slang, $context);
        $existingExamplesText = $this->formatExamplesForPrompt($slangContext['examples']);

        $prompt = <<<PROMPT
당신은 한국어 욕설/슬랭 사전 편집자입니다.
아래 표현에 대한 새로운 사용 예문 {$count}개를 추가로 작성해주세요.

## 대상 단어
{$slangContext['korean']}

{$this->buildAiHintSection($slangContext['ai_generation_hint'])}

## 참고 정보
- pronunciation: {$slangContext['pronunciation']}
- level: {$slangContext['level']}
- usage_frequency: {$slangContext['usage_frequency']}
- english_description: {$slangContext['english_description']}
- korean_description: {$slangContext['korean_description']}
- usage_context: {$slangContext['usage_context']}
- english_usage_context: {$slangContext['english_usage_context']}

## 기존 예문
{$existingExamplesText}

## 작성 규칙
1. 정확히 {$count}개의 새로운 예문을 작성해주세요.
2. 기존 예문과 표현이나 상황이 최대한 겹치지 않게 해주세요.
3. 각 예문은 korean_example, english_example을 함께 작성해주세요.
4. 한국어 예문은 실제 대화처럼 자연스럽게, 영어 예문은 의미가 맞는 자연스러운 번역으로 작성해주세요.
5. JSON만 반환해주세요.
PROMPT;

        $data = $this->generateStructuredData($prompt, [
            'type' => 'OBJECT',
            'properties' => [
                'examples' => [
                    'type' => 'ARRAY',
                    'description' => "Exactly {$count} additional examples",
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'korean_example' => [
                                'type' => 'STRING',
                                'description' => 'Example sentence in Korean',
                            ],
                            'english_example' => [
                                'type' => 'STRING',
                                'description' => 'English translation of the example',
                            ],
                        ],
                        'required' => ['korean_example', 'english_example'],
                    ],
                ],
            ],
            'required' => ['examples'],
        ]);

        $examples = collect($data['examples'] ?? [])
            ->filter(fn ($example) => is_array($example))
            ->map(function (array $example): array {
                return [
                    'korean_example' => trim((string) ($example['korean_example'] ?? '')),
                    'english_example' => trim((string) ($example['english_example'] ?? '')),
                ];
            })
            ->filter(function (array $example): bool {
                return $example['korean_example'] !== '' && $example['english_example'] !== '';
            })
            ->take($count)
            ->values()
            ->all();

        return [
            'examples' => $examples,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{
     *     public_slug: string,
     *     public_title_en: string,
     *     public_summary_en: string,
     *     seo_title_en: string,
     *     seo_description_en: string,
     *     seo_keywords_en: string
     * }
     */
    public function generateSeoFields(Slang $slang, array $context = []): array
    {
        $slangContext = $this->buildSlangContext($slang, $context);
        $categoriesText = $slangContext['category_names'] !== []
            ? implode(', ', $slangContext['category_names'])
            : '(카테고리 없음)';

        $prompt = <<<PROMPT
당신은 Google과 Bing 검색 최적화에 전문화된 Korean slang SEO 편집자입니다.
아래 슬랭 정보를 바탕으로 공개 슬랭 상세 페이지용 SEO 필드를 작성해주세요.

## 대상 단어
- korean: {$slangContext['korean']}
- pronunciation: {$slangContext['pronunciation']}
- level: {$slangContext['level']}
- usage_frequency: {$slangContext['usage_frequency']}
- categories: {$categoriesText}

{$this->buildAiHintSection($slangContext['ai_generation_hint'])}

## 참고 설명
- english_description: {$slangContext['english_description']}
- korean_description: {$slangContext['korean_description']}
- usage_context: {$slangContext['usage_context']}
- english_usage_context: {$slangContext['english_usage_context']}

## 현재 SEO 필드
- current public_slug: {$slangContext['public_slug']}
- current public_title_en: {$slangContext['public_title_en']}
- current public_summary_en: {$slangContext['public_summary_en']}
- current seo_title_en: {$slangContext['seo_title_en']}
- current seo_description_en: {$slangContext['seo_description_en']}

## URL 구조
- 페이지 URL 패턴: /korean-slang/{public_slug}
- 사이트명: kslang
- title 태그 패턴: {seo_title_en} | kslang

### public_slug
- 영어 소문자, 숫자, 하이픈만 사용
- 발음 기반의 짧고 명확한 slug (예: "neu-joh", "gaesaekki")

{$this->buildSeoRulesSection(['korean' => $slangContext['korean'], 'pronunciation' => $slangContext['pronunciation']])}

JSON만 반환해주세요.
PROMPT;

        $data = $this->generateStructuredData($prompt, [
            'type' => 'OBJECT',
            'properties' => [
                'public_slug' => [
                    'type' => 'STRING',
                    'description' => 'SEO-friendly slug using lowercase letters, numbers, and hyphens only',
                ],
                'public_title_en' => [
                    'type' => 'STRING',
                    'description' => 'Public page H1 title in English',
                ],
                'public_summary_en' => [
                    'type' => 'STRING',
                    'description' => 'Public page summary (2-3 sentences, English)',
                ],
                'seo_title_en' => [
                    'type' => 'STRING',
                    'description' => 'SEO title for SERP (50-60 characters, English, no brand name)',
                ],
                'seo_description_en' => [
                    'type' => 'STRING',
                    'description' => 'SEO meta description for SERP (140-160 characters, English)',
                ],
                'seo_keywords_en' => [
                    'type' => 'STRING',
                    'description' => 'Comma-separated SEO keywords (5-8 keywords, English)',
                ],
            ],
            'required' => [
                'public_slug',
                'public_title_en',
                'public_summary_en',
                'seo_title_en',
                'seo_description_en',
                'seo_keywords_en',
            ],
        ]);

        return [
            'public_slug' => Str::slug((string) ($data['public_slug'] ?? '')) ?: $this->resolvePublicSlug($slang, $slangContext['pronunciation']),
            'public_title_en' => trim((string) ($data['public_title_en'] ?? '')),
            'public_summary_en' => trim((string) ($data['public_summary_en'] ?? '')),
            'seo_title_en' => trim((string) ($data['seo_title_en'] ?? '')),
            'seo_description_en' => trim((string) ($data['seo_description_en'] ?? '')),
            'seo_keywords_en' => trim((string) ($data['seo_keywords_en'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{faq_items: list<array{question: string, answer: string}>}
     */
    public function generateFaqItems(Slang $slang, array $context = [], int $count = 5): array
    {
        $slangContext = $this->buildSlangContext($slang, $context);
        $categoriesText = $slangContext['category_names'] !== []
            ? implode(', ', $slangContext['category_names'])
            : '(카테고리 없음)';

        $prompt = <<<PROMPT
당신은 영어권 학습자를 위한 Korean slang FAQ 편집자입니다.
아래 슬랭 정보를 바탕으로 영문 FAQ {$count}개를 작성해주세요.

## 대상 단어
- korean: {$slangContext['korean']}
- pronunciation: {$slangContext['pronunciation']}
- level: {$slangContext['level']}
- usage_frequency: {$slangContext['usage_frequency']}
- categories: {$categoriesText}

{$this->buildAiHintSection($slangContext['ai_generation_hint'])}

## 참고 설명
- english_description: {$slangContext['english_description']}
- korean_description: {$slangContext['korean_description']}
- usage_context: {$slangContext['usage_context']}
- english_usage_context: {$slangContext['english_usage_context']}

## 작성 규칙
1. 정확히 {$count}개의 FAQ를 작성해주세요.
2. 질문(question)과 답변(answer)은 모두 영어로 작성해주세요.
3. 질문에 반드시 한글 원문({$slangContext['korean']})을 포함해주세요. (예: "What does {$slangContext['korean']} mean in Korean?")
4. 답변에도 첫 문장에 한글 원문과 발음을 함께 포함해주세요. (예: "{$slangContext['korean']} ({$slangContext['pronunciation']}) is ...")
5. 질문은 영어권 학습자가 실제로 검색할 만한 자연스러운 형태로 작성해주세요.
6. 답변은 2~3문장으로 간결하면서 유용한 정보를 담아주세요.
7. 기본 질문(의미, 강도, 사용 상황) 외에 문화 맥락, 비슷한 표현, 주의점 등 다양한 관점을 포함해주세요.
8. JSON만 반환해주세요.
PROMPT;

        $data = $this->generateStructuredData($prompt, [
            'type' => 'OBJECT',
            'properties' => [
                'faq_items' => [
                    'type' => 'ARRAY',
                    'description' => "Exactly {$count} FAQ items for the slang detail page",
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'question' => [
                                'type' => 'STRING',
                                'description' => 'FAQ question in English',
                            ],
                            'answer' => [
                                'type' => 'STRING',
                                'description' => 'FAQ answer in English (2-3 sentences)',
                            ],
                        ],
                        'required' => ['question', 'answer'],
                    ],
                ],
            ],
            'required' => ['faq_items'],
        ]);

        $faqItems = collect($data['faq_items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item): array {
                return [
                    'question' => trim((string) ($item['question'] ?? '')),
                    'answer' => trim((string) ($item['answer'] ?? '')),
                ];
            })
            ->filter(function (array $item): bool {
                return $item['question'] !== '' && $item['answer'] !== '';
            })
            ->take($count)
            ->values()
            ->all();

        return [
            'faq_items' => $faqItems,
        ];
    }

    private function buildPrompt(string $koreanWord, array $existingCategories, ?string $aiGenerationHint = null): string
    {
        $categoryList = ! empty($existingCategories)
            ? implode(', ', $existingCategories)
            : '(등록된 카테고리 없음)';

        return <<<PROMPT
당신은 한국어 욕설/슬랭 사전 편찬 전문가입니다.
아래 한국어 단어/표현에 대한 상세 정보를 JSON으로 작성해주세요.

## 대상 단어
{$koreanWord}

{$this->buildAiHintSection((string) $aiGenerationHint)}

## 콘텐츠 작성 규칙
1. pronunciation: 영어 로마자 발음 표기 (예: "ssi-bal", "gaesaekki")
2. english_description: 영어로 된 상세 설명 (2~3문장, 의미·뉘앙스·문화적 맥락 포함)
3. korean_description: 한국어로 된 상세 설명 (2~3문장, 의미·뉘앙스·사용 맥락 포함)
4. level: 강도 레벨 (1=순한맛, 2=중간맛, 3=매운맛, 4=극한맛)
5. usage_frequency: Usage frequency (must be one of "Common", "Occasional", "Rare")
6. usage_context: 주로 사용되는 상황·맥락 설명 (한국어, 2~3문장)
7. english_usage_context: usage_context의 자연스러운 영어 번역 (영어, 2~3문장)
8. examples: 사용 예문 2~4개 (각각 korean_example과 english_example)
9. suggested_categories: 현재 등록된 카테고리 중 적합한 것을 선택 (여러 개 가능)

{$this->buildSeoRulesSection(['korean' => $koreanWord, 'pronunciation' => '(pronunciation)'])}

## 현재 등록된 카테고리
{$categoryList}

정확하고 자연스러운 정보를 작성해주세요. 실제 한국 문화에서의 사용 맥락을 반영해주세요.
pronunciation을 먼저 결정한 다음, SEO 필드에 해당 pronunciation을 사용해주세요.
PROMPT;
    }

    /**
     * Gemini responseSchema를 DB 구조에 맞게 구성.
     *
     * @return array<string, mixed>
     */
    private function buildResponseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'pronunciation' => [
                    'type' => 'STRING',
                    'description' => 'English romanization of the Korean word',
                ],
                'english_description' => [
                    'type' => 'STRING',
                    'description' => 'Detailed English description (2-3 sentences)',
                ],
                'korean_description' => [
                    'type' => 'STRING',
                    'description' => 'Detailed Korean description (2-3 sentences)',
                ],
                'level' => [
                    'type' => 'INTEGER',
                    'description' => 'Intensity level: 1=Mild, 2=Moderate, 3=Strong, 4=Extreme',
                ],
                'usage_frequency' => [
                    'type' => 'STRING',
                    'description' => 'Usage frequency',
                    'enum' => ['Common', 'Occasional', 'Rare'],
                ],
                'usage_context' => [
                    'type' => 'STRING',
                    'description' => 'Context and situations where the word is commonly used (Korean, 2-3 sentences)',
                ],
                'english_usage_context' => [
                    'type' => 'STRING',
                    'description' => 'Natural English translation of usage_context (2-3 sentences)',
                ],
                'examples' => [
                    'type' => 'ARRAY',
                    'description' => '2-4 usage examples',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'korean_example' => [
                                'type' => 'STRING',
                                'description' => 'Example sentence in Korean',
                            ],
                            'english_example' => [
                                'type' => 'STRING',
                                'description' => 'English translation of the example',
                            ],
                        ],
                        'required' => ['korean_example', 'english_example'],
                    ],
                ],
                'suggested_categories' => [
                    'type' => 'ARRAY',
                    'description' => 'Matching category names from the provided list',
                    'items' => [
                        'type' => 'STRING',
                    ],
                ],
                'seo_title_en' => [
                    'type' => 'STRING',
                    'description' => 'SEO title for Google/Bing search results (50-60 characters, English)',
                ],
                'seo_description_en' => [
                    'type' => 'STRING',
                    'description' => 'SEO meta description for search results (140-160 characters, English)',
                ],
                'public_title_en' => [
                    'type' => 'STRING',
                    'description' => 'Public page H1 title in English',
                ],
                'public_summary_en' => [
                    'type' => 'STRING',
                    'description' => 'Public page summary in English (2-3 sentences)',
                ],
                'seo_keywords_en' => [
                    'type' => 'STRING',
                    'description' => 'Comma-separated SEO keywords (5-8 keywords, English)',
                ],
            ],
            'required' => [
                'pronunciation',
                'english_description',
                'korean_description',
                'level',
                'usage_frequency',
                'usage_context',
                'english_usage_context',
                'examples',
                'seo_title_en',
                'seo_description_en',
                'public_title_en',
                'public_summary_en',
                'seo_keywords_en',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $responseSchema
     * @return array<string, mixed>
     */
    private function generateStructuredData(string $prompt, array $responseSchema): array
    {
        $response = $this->geminiService->generate($prompt, $responseSchema, 'MEDIUM');
        $data = $response->json();

        if (! $data) {
            throw new \RuntimeException("Gemini 응답을 JSON으로 파싱할 수 없습니다: {$response->text}");
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{
     *     korean: string,
     *     ai_generation_hint: string,
     *     pronunciation: string,
     *     english_description: string,
     *     korean_description: string,
     *     level: string,
     *     usage_frequency: string,
     *     usage_context: string,
     *     english_usage_context: string,
     *     public_slug: string,
     *     public_title_en: string,
     *     public_summary_en: string,
     *     seo_title_en: string,
     *     seo_description_en: string,
     *     seo_keywords_en: string,
     *     category_names: list<string>,
     *     examples: Collection<int, array{korean_example: string, english_example: string}>
     * }
     */
    private function buildSlangContext(Slang $slang, array $context): array
    {
        $currentExamples = collect($context['examples'] ?? $slang->examples()->get(['korean_example', 'english_example'])->toArray())
            ->filter(fn ($example) => is_array($example))
            ->map(function (array $example): array {
                return [
                    'korean_example' => trim((string) ($example['korean_example'] ?? '')),
                    'english_example' => trim((string) ($example['english_example'] ?? '')),
                ];
            })
            ->filter(function (array $example): bool {
                return $example['korean_example'] !== '' || $example['english_example'] !== '';
            })
            ->values();

        return [
            'korean' => $this->resolveContextValue($context, 'korean', $slang->korean),
            'ai_generation_hint' => $this->resolveContextValue($context, 'ai_generation_hint', $slang->ai_generation_hint),
            'pronunciation' => $this->resolveContextValue($context, 'pronunciation', $slang->pronunciation),
            'english_description' => $this->resolveContextValue($context, 'english_description', $slang->english_description),
            'korean_description' => $this->resolveContextValue($context, 'korean_description', $slang->korean_description),
            'level' => (string) ($context['level'] ?? $slang->level),
            'usage_frequency' => $this->resolveContextValue($context, 'usage_frequency', $slang->usage_frequency),
            'usage_context' => $this->resolveContextValue($context, 'usage_context', $slang->usage_context),
            'english_usage_context' => $this->resolveContextValue($context, 'english_usage_context', $slang->english_usage_context),
            'public_slug' => $this->resolveContextValue($context, 'public_slug', $slang->public_slug),
            'public_title_en' => $this->resolveContextValue($context, 'public_title_en', $slang->public_title_en),
            'public_summary_en' => $this->resolveContextValue($context, 'public_summary_en', $slang->public_summary_en),
            'seo_title_en' => $this->resolveContextValue($context, 'seo_title_en', $slang->seo_title_en),
            'seo_description_en' => $this->resolveContextValue($context, 'seo_description_en', $slang->seo_description_en),
            'seo_keywords_en' => $this->resolveContextValue($context, 'seo_keywords_en', $slang->seo_keywords_en),
            'category_names' => collect($context['category_names'] ?? $slang->categories()->pluck('name')->all())
                ->map(fn ($name) => trim((string) $name))
                ->filter(fn ($name) => $name !== '')
                ->values()
                ->all(),
            'examples' => $currentExamples,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resolveContextValue(array $context, string $key, ?string $fallback): string
    {
        $value = $context[$key] ?? $fallback;

        return trim((string) $value);
    }

    /**
     * 모든 SEO 프롬프트에서 공유하는 표준 SEO 작성 규칙.
     *
     * @param  array{korean: string, pronunciation: string}  $word
     */
    private function buildSeoRulesSection(array $word): string
    {
        $korean = $word['korean'];
        $pronunciation = $word['pronunciation'];
        $exampleTitle = "{$korean} ({$pronunciation}) – Meaning & Usage in Korean";
        $exampleH1 = "{$korean} ({$pronunciation}) meaning in Korean";
        $exampleSummaryStart = "'{$pronunciation}' ({$korean})";

        return <<<RULES
## SEO 필수 패턴 (반드시 준수)

### 핵심 원칙
- 모든 SEO 필드에 한글 원문과 로마자 발음을 모두 포함
- Google에서 한글 검색("$korean 뜻")과 로마자 검색("$pronunciation meaning") 모두 매칭 가능하게
- SERP에서 한글이 시각적으로 눈에 띄어 CTR 향상

### seo_title_en (Google/Bing SERP title 태그)
- 패턴: "{한국어} ({발음}) – Meaning & Usage in Korean"
- 이 단어의 예: "$exampleTitle"
- 50~60자 이내 (뒤에 " | kslang" 브랜드가 자동 추가됨)
- 브랜드명(kslang) 포함 금지, 파이프(|) 문자 금지

### seo_description_en (Google/Bing SERP meta description)
- 140~160자 이내
- 첫 문장 시작: "{한국어} ({발음}) is ..." 또는 "Learn what {한국어} ({발음}) means ..."
- 반드시 한글 원문 + 발음을 첫 문장에 포함
- 강도(intensity level), 사용 맥락 등 차별화 정보 포함
- "Learn", "Discover" 같은 CTA 워드 자연스럽게 포함
- 과장된 clickbait 금지

### public_title_en (H1 태그)
- 패턴: "{한국어} ({발음}) meaning in Korean"
- 이 단어의 예: "$exampleH1"
- 반드시 한글 원문 + 발음 포함

### public_summary_en (페이지 상단 요약)
- 2~3문장
- 첫 문장: "'{발음}' ({한국어})" 형태로 시작
- 이 단어의 예 시작: "$exampleSummaryStart is a Korean slang ..."
- "Korean slang", "meaning" 등 핵심 키워드 자연스럽게 포함

### seo_keywords_en (meta keywords)
- 쉼표로 구분된 5~8개의 영어 키워드
- 반드시 포함: 한국어 원문($korean), 발음($pronunciation), "Korean slang", "meaning"
- long-tail 키워드 1~2개 포함 (예: "what does $korean mean in Korean")
RULES;
    }

    private function buildAiHintSection(string $hint): string
    {
        $hint = trim($hint);

        if ($hint === '') {
            return '';
        }

        return <<<PROMPT
## 관리자 제공 참고 설명
{$hint}

위 설명은 최신 유행어/신조어를 해석하기 위한 핵심 참고 정보입니다. 일반적인 사전 지식보다 위 설명의 의미와 맥락을 우선 반영하고, 부족한 부분만 자연스럽게 보완해주세요.
PROMPT;
    }

    /**
     * @param  Collection<int, array{korean_example: string, english_example: string}>  $examples
     */
    private function formatExamplesForPrompt(Collection $examples): string
    {
        if ($examples->isEmpty()) {
            return '(현재 입력된 예문 없음)';
        }

        return $examples
            ->map(function (array $example, int $index): string {
                $number = $index + 1;

                return "{$number}. 한국어: {$example['korean_example']} / 영어: {$example['english_example']}";
            })
            ->implode("\n");
    }

    /**
     * Gemini 응답 데이터를 슬랭에 적용.
     *
     * @param  array<string, mixed>  $data
     */
    private function applyGeneratedData(Slang $slang, array $data): Slang
    {
        return DB::transaction(function () use ($slang, $data) {
            $level = max(1, min(4, (int) ($data['level'] ?? 1)));

            $allowedFrequencies = ['Common', 'Occasional', 'Rare'];
            $frequency = in_array($data['usage_frequency'] ?? '', $allowedFrequencies)
                ? $data['usage_frequency']
                : 'Occasional';

            $slang->update([
                'pronunciation' => $data['pronunciation'] ?? '',
                'english_description' => $data['english_description'] ?? '',
                'korean_description' => $data['korean_description'] ?? '',
                'level' => $level,
                'usage_frequency' => $frequency,
                'usage_context' => $data['usage_context'] ?? '',
                'english_usage_context' => $data['english_usage_context'] ?? '',
                'public_slug' => $this->resolvePublicSlug($slang, (string) ($data['pronunciation'] ?? '')),
                'seo_title_en' => trim((string) ($data['seo_title_en'] ?? '')),
                'seo_description_en' => trim((string) ($data['seo_description_en'] ?? '')),
                'seo_keywords_en' => trim((string) ($data['seo_keywords_en'] ?? '')),
                'public_title_en' => trim((string) ($data['public_title_en'] ?? '')),
                'public_summary_en' => trim((string) ($data['public_summary_en'] ?? '')),
                'content_status' => Slang::STATUS_GENERATED,
                'is_active' => false,
                'is_new' => false,
                'approved_at' => null,
                'thread_post_formats' => null,
                'thread_post_generated_at' => null,
            ]);

            if (! empty($data['examples']) && is_array($data['examples'])) {
                $slang->examples()->delete();

                foreach (array_slice($data['examples'], 0, 10) as $index => $example) {
                    $slang->examples()->create([
                        'korean_example' => $example['korean_example'] ?? '',
                        'english_example' => $example['english_example'] ?? '',
                        'sort_order' => $index,
                    ]);
                }
            }

            if (! empty($data['suggested_categories']) && is_array($data['suggested_categories'])) {
                $categoryIds = Category::whereIn('name', $data['suggested_categories'])
                    ->pluck('id')
                    ->toArray();

                if (! empty($categoryIds)) {
                    $slang->categories()->sync($categoryIds);
                }
            }

            return $slang->fresh(['categories', 'examples']);
        });
    }

    private function resolvePublicSlug(Slang $slang, string $pronunciation): string
    {
        $currentSlug = trim((string) $slang->public_slug);

        if ($currentSlug !== '') {
            return $currentSlug;
        }

        $base = Str::slug($pronunciation);

        if ($base === '') {
            $base = 'slang';
        }

        $candidate = $base;
        $suffix = 2;

        while (
            Slang::query()
                ->where('public_slug', $candidate)
                ->where('id', '!=', $slang->id)
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
