<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuickStoreSlangRequest extends FormRequest
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
            'words' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'words.required' => '단어를 입력해주세요.',
        ];
    }

    /**
     * 줄바꿈/쉼표로 구분된 단어 목록을 배열로 파싱.
     *
     * @return array<int, string>
     */
    public function parsedWords(): array
    {
        $raw = $this->input('words', '');

        return collect(preg_split('/[\r\n,]+/', $raw))
            ->map(fn (string $word) => trim($word))
            ->filter(fn (string $word) => $word !== '')
            ->unique()
            ->values()
            ->toArray();
    }
}
