<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Page;
use App\Models\Slang;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class ApiPlaygroundService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEndpoints(): array
    {
        $samples = $this->sampleValues();

        return [
            [
                'key' => 'slangs.index',
                'group' => 'Slangs',
                'label' => '욕/슬랭 목록',
                'method' => 'GET',
                'path' => '/api/v1/slangs',
                'uri' => '/slangs',
                'description' => '활성 + 승인된 욕/슬랭 목록을 페이지네이션으로 조회합니다.',
                'path_params' => [],
                'query_params' => [
                    $this->parameter('per_page', '페이지당 개수', 'number', false, '20', '기본 20, 최대 100'),
                    $this->parameter('page', '페이지 번호', 'number', false, '1', '기본 1'),
                    $this->parameter('level', '수위 레벨', 'number', false, '', '1~4 중 하나'),
                    $this->parameter('category_id', '카테고리 ID', 'number', false, $samples['category_id'], '특정 카테고리만 조회'),
                ],
                'notes' => [
                    '정렬은 sort_order 오름차순입니다.',
                    '활성 + 승인된 데이터만 반환됩니다.',
                ],
            ],
            [
                'key' => 'slangs.search',
                'group' => 'Slangs',
                'label' => '욕/슬랭 검색',
                'method' => 'GET',
                'path' => '/api/v1/slangs/search',
                'uri' => '/slangs/search',
                'description' => '검색어로 욕/슬랭을 조회합니다.',
                'path_params' => [],
                'query_params' => [
                    $this->parameter('q', '검색어', 'text', true, '억까', '최소 2자 이상'),
                    $this->parameter('per_page', '페이지당 개수', 'number', false, '20', '기본 20, 최대 100'),
                    $this->parameter('page', '페이지 번호', 'number', false, '1', '기본 1'),
                ],
                'notes' => [
                    '검색어가 2자 미만이면 빈 결과가 반환됩니다.',
                    '한국어, 발음, 설명, 영어 사용 상황까지 함께 검색합니다.',
                ],
            ],
            [
                'key' => 'slangs.random',
                'group' => 'Slangs',
                'label' => '랜덤 슬랭',
                'method' => 'GET',
                'path' => '/api/v1/slangs/random',
                'uri' => '/slangs/random',
                'description' => '임의의 욕/슬랭을 조회합니다.',
                'path_params' => [],
                'query_params' => [
                    $this->parameter('count', '반환 개수', 'number', false, '1', '기본 1, 최대 10'),
                ],
                'notes' => [
                    'count=1이면 단일 객체를 반환합니다.',
                    'count가 2 이상이면 배열을 반환합니다.',
                ],
            ],
            [
                'key' => 'slangs.daily',
                'group' => 'Slangs',
                'label' => '오늘의 슬랭',
                'method' => 'GET',
                'path' => '/api/v1/slangs/daily',
                'uri' => '/slangs/daily',
                'description' => '서버 날짜 기준 오늘의 슬랭 1건을 반환합니다.',
                'path_params' => [],
                'query_params' => [],
                'notes' => [
                    '같은 날에는 동일한 결과가 반환됩니다.',
                    '활성 슬랭이 없으면 404를 반환합니다.',
                ],
            ],
            [
                'key' => 'slangs.show',
                'group' => 'Slangs',
                'label' => '욕/슬랭 상세',
                'method' => 'GET',
                'path' => '/api/v1/slangs/{slang}',
                'uri' => '/slangs/{slang}',
                'description' => '특정 욕/슬랭의 상세 정보를 조회합니다.',
                'path_params' => [
                    $this->parameter('slang', '슬랭 ID', 'number', true, $samples['slang_id'], '조회할 슬랭 ID'),
                ],
                'query_params' => [],
                'notes' => [
                    '비활성 또는 미승인 슬랭은 404를 반환합니다.',
                    'categories, examples가 함께 포함됩니다.',
                ],
            ],
            [
                'key' => 'categories.index',
                'group' => 'Categories',
                'label' => '카테고리 목록',
                'method' => 'GET',
                'path' => '/api/v1/categories',
                'uri' => '/categories',
                'description' => '전체 카테고리를 정렬 순서대로 조회합니다.',
                'path_params' => [],
                'query_params' => [],
                'notes' => [
                    '활성 슬랭 개수가 함께 반환됩니다.',
                    '정렬은 sort_order 오름차순입니다.',
                ],
            ],
            [
                'key' => 'categories.show',
                'group' => 'Categories',
                'label' => '카테고리 상세',
                'method' => 'GET',
                'path' => '/api/v1/categories/{category}',
                'uri' => '/categories/{category}',
                'description' => '카테고리 정보와 소속 슬랭 목록을 함께 조회합니다.',
                'path_params' => [
                    $this->parameter('category', '카테고리 ID', 'number', true, $samples['category_id'], '조회할 카테고리 ID'),
                ],
                'query_params' => [
                    $this->parameter('per_page', '페이지당 개수', 'number', false, '20', '기본 20, 최대 100'),
                    $this->parameter('page', '페이지 번호', 'number', false, '1', '기본 1'),
                ],
                'notes' => [
                    'category와 slangs 페이지네이션 객체가 함께 반환됩니다.',
                    'slangs는 활성 + 승인된 데이터만 포함됩니다.',
                ],
            ],
            [
                'key' => 'app.version',
                'group' => 'App',
                'label' => '앱 버전 정보',
                'method' => 'GET',
                'path' => '/api/v1/app/version',
                'uri' => '/app/version',
                'description' => '최소 지원 버전, 최신 버전, Play Store URL을 조회합니다.',
                'path_params' => [],
                'query_params' => [],
                'notes' => [
                    '강제 업데이트 판단에 사용됩니다.',
                    '설정되지 않은 값은 null로 반환됩니다.',
                ],
            ],
            [
                'key' => 'app.sync',
                'group' => 'App',
                'label' => '데이터 동기화 정보',
                'method' => 'GET',
                'path' => '/api/v1/app/sync',
                'uri' => '/app/sync',
                'description' => '슬랭/카테고리 총 개수와 최종 수정일을 조회합니다.',
                'path_params' => [],
                'query_params' => [],
                'notes' => [
                    '앱 로컬 캐시 갱신 여부 판단에 사용됩니다.',
                    'last_updated_at은 문자열 또는 null입니다.',
                ],
            ],
            [
                'key' => 'pages.show',
                'group' => 'Pages',
                'label' => '페이지 조회',
                'method' => 'GET',
                'path' => '/api/v1/pages/{slug}',
                'uri' => '/pages/{slug}',
                'description' => '이용약관 또는 개인정보처리방침 HTML을 조회합니다.',
                'path_params' => [
                    $this->parameter('slug', '페이지 slug', 'text', true, $samples['page_slug'], 'privacy 또는 terms'),
                ],
                'query_params' => [],
                'notes' => [
                    'content는 HTML 문자열입니다.',
                    '허용 slug는 privacy, terms 두 가지입니다.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEndpoint(string $key): ?array
    {
        foreach ($this->getEndpoints() as $endpoint) {
            if ($endpoint['key'] === $key) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $endpoint
     * @param  array<string, string>  $pathParams
     * @param  array<string, string>  $queryParams
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     */
    public function execute(array $endpoint, array $pathParams, array $queryParams, string $baseUrl): array
    {
        $normalizedPathParams = $this->normalizeParameters($pathParams, $endpoint['path_params']);
        $normalizedQueryParams = $this->normalizeParameters($queryParams, $endpoint['query_params']);
        $resolvedPath = $this->resolvePath($endpoint['path'], $normalizedPathParams);
        $requestUrl = $this->buildUrl($baseUrl, $resolvedPath, $normalizedQueryParams);

        $startedAt = microtime(true);

        $response = Http::timeout(15)
            ->acceptJson()
            ->withHeaders([
                'X-API-Key' => (string) config('app.api_key'),
            ])
            ->send($endpoint['method'], rtrim($baseUrl, '/').$resolvedPath, [
                'query' => $normalizedQueryParams,
            ]);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        return [
            'request' => [
                'method' => $endpoint['method'],
                'url' => $requestUrl,
                'headers' => [
                    'Accept' => 'application/json',
                    'X-API-Key' => $this->maskApiKey(config('app.api_key')),
                ],
                'curl' => $this->buildCurlCommand($endpoint['method'], $requestUrl),
            ],
            'response' => [
                'status' => $response->status(),
                'ok' => $response->successful(),
                'duration_ms' => $durationMs,
                'headers' => $this->serializeHeaders($response),
                'body' => $this->decodeBody($response),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sampleValues(): array
    {
        return [
            'slang_id' => $this->sampleSlangId(),
            'category_id' => $this->sampleCategoryId(),
            'page_slug' => $this->samplePageSlug(),
        ];
    }

    private function sampleSlangId(): string
    {
        try {
            return (string) (Slang::query()
                ->apiVisible()
                ->orderBy('sort_order')
                ->value('id') ?? 1);
        } catch (Throwable) {
            return '1';
        }
    }

    private function sampleCategoryId(): string
    {
        try {
            return (string) (Category::query()
                ->orderBy('sort_order')
                ->value('id') ?? 1);
        } catch (Throwable) {
            return '1';
        }
    }

    private function samplePageSlug(): string
    {
        try {
            return (string) (Page::query()
                ->whereIn('slug', Page::$allowedSlugs)
                ->orderBy('slug')
                ->value('slug') ?? 'terms');
        } catch (Throwable) {
            return 'terms';
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function parameter(
        string $name,
        string $label,
        string $type,
        bool $required,
        string $default,
        string $description
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => $required,
            'default' => $default,
            'description' => $description,
        ];
    }

    /**
     * @param  array<string, string>  $parameters
     * @param  array<int, array<string, mixed>>  $definitions
     * @return array<string, string>
     */
    private function normalizeParameters(array $parameters, array $definitions): array
    {
        $normalized = [];

        foreach ($definitions as $definition) {
            $name = $definition['name'];

            if (! array_key_exists($name, $parameters)) {
                continue;
            }

            $value = trim((string) $parameters[$name]);

            if ($value === '') {
                continue;
            }

            $normalized[$name] = $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $pathParams
     */
    private function resolvePath(string $path, array $pathParams): string
    {
        foreach ($pathParams as $name => $value) {
            $path = str_replace('{'.$name.'}', rawurlencode($value), $path);
        }

        return $path;
    }

    /**
     * @param  array<string, string>  $queryParams
     */
    private function buildUrl(string $baseUrl, string $path, array $queryParams): string
    {
        $url = rtrim($baseUrl, '/').$path;

        if ($queryParams === []) {
            return $url;
        }

        return $url.'?'.http_build_query($queryParams);
    }

    /**
     * @return array<string, string>
     */
    private function serializeHeaders(Response $response): array
    {
        $headers = [];

        foreach ($response->headers() as $key => $values) {
            $headers[$key] = implode(', ', $values);
        }

        return $headers;
    }

    private function decodeBody(Response $response): mixed
    {
        $body = $response->body();

        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $body;
    }

    private function buildCurlCommand(string $method, string $url): string
    {
        return sprintf(
            'curl -X %s "%s" -H "Accept: application/json" -H "X-API-Key: YOUR_API_KEY"',
            $method,
            $url
        );
    }

    private function maskApiKey(?string $apiKey): string
    {
        $apiKey = trim((string) $apiKey);

        if ($apiKey === '') {
            return '(설정 안 됨)';
        }

        if (strlen($apiKey) <= 8) {
            return str_repeat('*', strlen($apiKey));
        }

        return substr($apiKey, 0, 4)
            .str_repeat('*', strlen($apiKey) - 8)
            .substr($apiKey, -4);
    }
}
