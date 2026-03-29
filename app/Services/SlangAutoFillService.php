<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Slang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $prompt = $this->buildPrompt($slang->korean, $categories);
        $schema = $this->buildResponseSchema();

        $response = $this->geminiService->generate($prompt, $schema, 'MEDIUM');
        $data = $response->json();

        if (! $data) {
            throw new \RuntimeException("Gemini 응답을 JSON으로 파싱할 수 없습니다: {$response->text}");
        }

        return $this->applyGeneratedData($slang, $data);
    }

    private function buildPrompt(string $koreanWord, array $existingCategories): string
    {
        $categoryList = ! empty($existingCategories)
            ? implode(', ', $existingCategories)
            : '(등록된 카테고리 없음)';

        return <<<PROMPT
당신은 한국어 욕설/슬랭 사전 편찬 전문가입니다.
아래 한국어 단어/표현에 대한 상세 정보를 JSON으로 작성해주세요.

## 대상 단어
{$koreanWord}

## 작성 규칙
1. pronunciation: 영어 로마자 발음 표기 (예: "ssi-bal", "gaesaekki")
2. english_description: 영어로 된 상세 설명 (2~3문장, 의미·뉘앙스·문화적 맥락 포함)
3. korean_description: 한국어로 된 상세 설명 (2~3문장, 의미·뉘앙스·사용 맥락 포함)
4. level: 강도 레벨 (1=순한맛, 2=중간맛, 3=매운맛, 4=극한맛)
5. usage_frequency: Usage frequency (must be one of "Common", "Occasional", "Rare")
6. usage_context: 주로 사용되는 상황·맥락 설명 (한국어, 2~3문장)
7. english_usage_context: usage_context의 자연스러운 영어 번역 (영어, 2~3문장)
8. examples: 사용 예문 2~4개 (각각 korean_example과 english_example)
9. suggested_categories: 현재 등록된 카테고리 중 적합한 것을 선택 (여러 개 가능)

## 현재 등록된 카테고리
{$categoryList}

정확하고 자연스러운 정보를 작성해주세요. 실제 한국 문화에서의 사용 맥락을 반영해주세요.
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
            ],
        ];
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
                'content_status' => Slang::STATUS_GENERATED,
                'is_active' => false,
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
}
