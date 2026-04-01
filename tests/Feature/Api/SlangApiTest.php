<?php

use App\Models\Slang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('includes english usage context and example audio urls in slang api responses', function () {
    config(['app.api_key' => 'test-api-key']);
    config([
        'services.audio.disk' => 's3',
        'services.audio.legacy_disk' => 'public',
        'services.audio.use_temporary_url' => false,
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    Storage::fake('s3');

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
        'content_status' => Slang::STATUS_APPROVED,
        'is_new' => true,
        'approved_at' => now(),
    ]);

    Storage::disk('s3')->put('audio/slangs/test-slang.mp3', 'fake-slang-audio');
    Storage::disk('s3')->put('audio/slang-examples/test-example.mp3', 'fake-example-audio');

    $slang->update([
        'audio_file' => 'audio/slangs/test-slang.mp3',
        'audio_disk' => 's3',
    ]);

    $slang->examples()->create([
        'korean_example' => '그건 좀 억까 아니냐?',
        'english_example' => 'Isn\'t that a bit unfair?',
        'audio_file' => 'audio/slang-examples/test-example.mp3',
        'audio_disk' => 's3',
        'sort_order' => 0,
    ]);

    $response = $this->withHeaders([
        'X-API-Key' => 'test-api-key',
    ])->getJson("/api/v1/slangs/{$slang->id}");

    $response
        ->assertSuccessful()
        ->assertJsonPath('data.is_new', true)
        ->assertJsonPath('data.usage_context', '주로 온라인에서 억울함을 표현할 때 쓴다.')
        ->assertJsonPath('data.english_usage_context', 'It is mainly used online to express that the criticism feels unfair.');

    expect($response->json('data.audio_url'))->toBeString()
        ->and($response->json('data.audio_url'))->toContain('audio/slangs/test-slang.mp3');
    expect($response->json('data.examples.0.audio_url'))->toBeString()
        ->and($response->json('data.examples.0.audio_url'))->toContain('audio/slang-examples/test-example.mp3');
});

it('orders slang api list with new words first and older approvals first among them', function () {
    config(['app.api_key' => 'test-api-key']);

    Slang::create([
        'korean' => '기존 단어',
        'pronunciation' => 'gi-jon',
        'english_description' => 'Existing slang entry.',
        'korean_description' => '기존에 있던 단어다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '일반적인 사용 상황이다.',
        'english_usage_context' => 'This is a general usage context.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
        'is_new' => false,
        'approved_at' => null,
    ]);

    Slang::create([
        'korean' => '승인 하루차',
        'pronunciation' => 'seung-in-ha-ru-cha',
        'english_description' => 'Approved one day ago.',
        'korean_description' => '하루 전에 승인된 단어다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '최근 승인된 사용 상황이다.',
        'english_usage_context' => 'This entry was approved recently.',
        'sort_order' => 99,
        'is_active' => true,
        'content_status' => Slang::STATUS_APPROVED,
        'is_new' => true,
        'approved_at' => now()->subDay(),
    ]);

    Slang::create([
        'korean' => '승인 이틀차',
        'pronunciation' => 'seung-in-i-teul-cha',
        'english_description' => 'Approved two days ago.',
        'korean_description' => '이틀 전에 승인된 단어다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '조금 더 먼저 승인된 사용 상황이다.',
        'english_usage_context' => 'This entry was approved earlier than the other new word.',
        'sort_order' => 100,
        'is_active' => true,
        'content_status' => Slang::STATUS_APPROVED,
        'is_new' => true,
        'approved_at' => now()->subDays(2),
    ]);

    $response = $this->withHeaders([
        'X-API-Key' => 'test-api-key',
    ])->getJson('/api/v1/slangs');

    $response
        ->assertSuccessful()
        ->assertJsonPath('data.0.korean', '승인 이틀차')
        ->assertJsonPath('data.0.is_new', true)
        ->assertJsonPath('data.1.korean', '승인 하루차')
        ->assertJsonPath('data.1.is_new', true)
        ->assertJsonPath('data.2.korean', '기존 단어')
        ->assertJsonPath('data.2.is_new', false);
});
