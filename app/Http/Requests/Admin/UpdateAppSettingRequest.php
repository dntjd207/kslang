<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
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
            'min_version' => ['required', 'string', 'regex:/^\d+\.\d+\.\d+$/'],
            'latest_version' => ['required', 'string', 'regex:/^\d+\.\d+\.\d+$/'],
            'play_store_url' => ['nullable', 'string', 'url', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'min_version.required' => '최소 지원 버전을 입력해주세요.',
            'min_version.regex' => '올바른 버전 형식(x.y.z)을 입력해주세요.',
            'latest_version.required' => '최신 버전을 입력해주세요.',
            'latest_version.regex' => '올바른 버전 형식(x.y.z)을 입력해주세요.',
            'play_store_url.url' => '올바른 URL 형식을 입력해주세요.',
            'play_store_url.max' => 'Play Store URL은 500자 이하여야 합니다.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('min_version') || ! $this->filled('latest_version')) {
                return;
            }

            if (! $this->isValidVersionFormat($this->min_version) || ! $this->isValidVersionFormat($this->latest_version)) {
                return;
            }

            if (version_compare($this->min_version, $this->latest_version) > 0) {
                $validator->errors()->add(
                    'min_version',
                    '최소 지원 버전은 최신 버전보다 높을 수 없습니다.'
                );
            }
        });
    }

    private function isValidVersionFormat(string $version): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+$/', $version);
    }
}
