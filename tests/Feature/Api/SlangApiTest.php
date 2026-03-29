<?php

use App\Models\Slang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes english usage context in slang api responses', function () {
    config(['app.api_key' => 'test-api-key']);

    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '주로 온라인에서 억울함을 표현할 때 쓴다.',
        'english_usage_context' => 'It is mainly used online to express that the criticism feels unfair.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ]);

    $this->withHeaders([
        'X-API-Key' => 'test-api-key',
    ])->getJson("/api/v1/slangs/{$slang->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.usage_context', '주로 온라인에서 억울함을 표현할 때 쓴다.')
        ->assertJsonPath('data.english_usage_context', 'It is mainly used online to express that the criticism feels unfair.');
});
