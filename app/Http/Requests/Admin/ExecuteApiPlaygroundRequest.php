<?php

namespace App\Http\Requests\Admin;

use App\Services\ApiPlaygroundService;
use Illuminate\Foundation\Http\FormRequest;

class ExecuteApiPlaygroundRequest extends FormRequest
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
            'endpoint_key' => ['required', 'string', 'max:100'],
            'path_params' => ['nullable', 'array'],
            'path_params.*' => ['nullable', 'string', 'max:255'],
            'query_params' => ['nullable', 'array'],
            'query_params.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'endpoint_key.required' => '실행할 API 엔드포인트를 선택해주세요.',
            'endpoint_key.max' => '엔드포인트 키가 너무 깁니다.',
            'path_params.array' => '경로 파라미터 형식이 올바르지 않습니다.',
            'path_params.*.max' => '경로 파라미터 값은 255자 이하여야 합니다.',
            'query_params.array' => '쿼리 파라미터 형식이 올바르지 않습니다.',
            'query_params.*.max' => '쿼리 파라미터 값은 255자 이하여야 합니다.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'path_params' => is_array($this->input('path_params')) ? $this->input('path_params') : [],
            'query_params' => is_array($this->input('query_params')) ? $this->input('query_params') : [],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = app(ApiPlaygroundService::class);
            $endpoint = $service->findEndpoint((string) $this->input('endpoint_key'));

            if ($endpoint === null) {
                $validator->errors()->add('endpoint_key', '허용되지 않은 API 엔드포인트입니다.');

                return;
            }

            $this->validateParameters($validator, $endpoint, 'path_params');
            $this->validateParameters($validator, $endpoint, 'query_params');
        });
    }

    /**
     * @return array<string, string>
     */
    public function pathParameters(): array
    {
        return $this->normalizeParameters($this->validated('path_params', []));
    }

    /**
     * @return array<string, string>
     */
    public function queryParameters(): array
    {
        return $this->normalizeParameters($this->validated('query_params', []));
    }

    /**
     * @param  array<string, mixed>  $endpoint
     */
    private function validateParameters($validator, array $endpoint, string $parameterType): void
    {
        /** @var array<string, mixed> $provided */
        $provided = $this->input($parameterType, []);
        $definitions = $endpoint[$parameterType] ?? [];

        $allowedNames = array_map(
            static fn (array $definition): string => $definition['name'],
            $definitions
        );

        foreach ($provided as $name => $value) {
            if (! in_array($name, $allowedNames, true)) {
                $validator->errors()->add(
                    "{$parameterType}.{$name}",
                    '허용되지 않은 파라미터입니다.'
                );
            }
        }

        foreach ($definitions as $definition) {
            $name = $definition['name'];
            $value = trim((string) ($provided[$name] ?? ''));

            if (($definition['required'] ?? false) && $value === '') {
                $validator->errors()->add(
                    "{$parameterType}.{$name}",
                    "{$definition['label']} 값을 입력해주세요."
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, string>
     */
    private function normalizeParameters(array $parameters): array
    {
        $normalized = [];

        foreach ($parameters as $name => $value) {
            $trimmed = trim((string) $value);

            if ($trimmed === '') {
                continue;
            }

            $normalized[$name] = $trimmed;
        }

        return $normalized;
    }
}
