<?php

namespace App\Http\Requests\Admin;

use App\Services\SupertoneTtsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateSupertoneTtsRequest extends FormRequest
{
    /**
     * @var array<int, string>
     */
    private const SUPPORTED_LANGUAGES = [
        'ko',
        'en',
        'ja',
        'bg',
        'cs',
        'da',
        'el',
        'es',
        'et',
        'fi',
        'hu',
        'it',
        'nl',
        'pl',
        'pt',
        'ro',
        'ar',
        'de',
        'fr',
        'hi',
        'id',
        'ru',
        'vi',
    ];

    /**
     * @var array<int, string>
     */
    private const SUPPORTED_MODELS = [
        'sona_speech_1',
        'supertonic_api_1',
        'sona_speech_2',
        'sona_speech_2_flash',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'api_key' => trim((string) $this->input('api_key')),
            'voice_id' => trim((string) $this->input('voice_id')),
            'text' => trim((string) $this->input('text')),
            'language' => trim((string) $this->input('language', 'ko')),
            'style' => $this->filled('style') ? trim((string) $this->input('style')) : null,
            'model' => trim((string) $this->input('model', 'sona_speech_1')),
            'pitch_shift' => $this->filled('pitch_shift') ? $this->input('pitch_shift') : null,
            'pitch_variance' => $this->filled('pitch_variance') ? $this->input('pitch_variance') : null,
            'speed' => $this->filled('speed') ? $this->input('speed') : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'api_key' => ['nullable', 'string', 'max:255'],
            'voice_id' => ['nullable', 'string', 'max:100'],
            'text' => ['required', 'string', 'max:300'],
            'language' => ['required', 'string', Rule::in(self::SUPPORTED_LANGUAGES)],
            'style' => ['nullable', 'string', 'max:50'],
            'model' => ['required', 'string', Rule::in(self::SUPPORTED_MODELS)],
            'pitch_shift' => ['nullable', 'integer', 'between:-24,24'],
            'pitch_variance' => ['nullable', 'numeric', 'between:0,2'],
            'speed' => ['nullable', 'numeric', 'between:0.5,2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'api_key.max' => 'API Key는 255자 이하여야 합니다.',
            'voice_id.max' => 'Voice ID는 100자 이하여야 합니다.',
            'text.required' => '변환할 텍스트를 입력해주세요.',
            'text.max' => '텍스트는 300자 이하여야 합니다.',
            'language.required' => '언어를 선택해주세요.',
            'language.in' => '지원하지 않는 언어 코드입니다.',
            'style.max' => '스타일은 50자 이하여야 합니다.',
            'model.required' => '모델을 선택해주세요.',
            'model.in' => '지원하지 않는 모델입니다.',
            'pitch_shift.integer' => 'Pitch Shift는 정수여야 합니다.',
            'pitch_shift.between' => 'Pitch Shift는 -24에서 24 사이여야 합니다.',
            'pitch_variance.numeric' => 'Pitch Variance는 숫자여야 합니다.',
            'pitch_variance.between' => 'Pitch Variance는 0에서 2 사이여야 합니다.',
            'speed.numeric' => '재생 속도는 숫자여야 합니다.',
            'speed.between' => '재생 속도는 0.5에서 2 사이여야 합니다.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $supertoneTtsService = app(SupertoneTtsService::class);

            if (! $this->filled('api_key') && ! $supertoneTtsService->hasConfiguredApiKey()) {
                $validator->errors()->add(
                    'api_key',
                    '환경값에 API Key가 없으면 여기에서 직접 입력해주세요.'
                );
            }

            if (! $this->filled('voice_id') && ! $supertoneTtsService->hasConfiguredVoiceId()) {
                $validator->errors()->add(
                    'voice_id',
                    '환경값에 Voice ID가 없으면 여기에서 직접 입력해주세요.'
                );
            }
        });
    }
}
