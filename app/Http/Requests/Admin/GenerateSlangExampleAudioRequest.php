<?php

namespace App\Http\Requests\Admin;

use App\Models\Slang;
use App\Models\SlangExample;
use Illuminate\Foundation\Http\FormRequest;

class GenerateSlangExampleAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'example_id' => $this->filled('example_id') ? $this->input('example_id') : null,
            'example_index' => $this->filled('example_index') ? $this->input('example_index') : null,
            'text' => trim((string) $this->input('text')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'example_id' => ['nullable', 'integer', 'exists:slang_examples,id'],
            'example_index' => ['required', 'integer', 'min:0'],
            'text' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'example_id.integer' => '예문 ID 형식이 올바르지 않습니다.',
            'example_id.exists' => '존재하지 않는 예문입니다.',
            'example_index.required' => '예문 위치 정보가 없습니다.',
            'example_index.integer' => '예문 위치 정보가 올바르지 않습니다.',
            'example_index.min' => '예문 위치 정보가 올바르지 않습니다.',
            'text.required' => '음성을 생성할 한국어 예문을 입력해주세요.',
            'text.max' => '한국어 예문은 500자 이하여야 합니다.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exampleId = $this->input('example_id');

            if ($exampleId === null) {
                return;
            }

            /** @var Slang|null $slang */
            $slang = $this->route('slang');

            if (! $slang instanceof Slang) {
                return;
            }

            $belongsToSlang = SlangExample::query()
                ->whereKey($exampleId)
                ->where('slang_id', $slang->id)
                ->exists();

            if (! $belongsToSlang) {
                $validator->errors()->add('example_id', '해당 슬랭에 속한 예문만 음성을 생성할 수 있습니다.');
            }
        });
    }
}
