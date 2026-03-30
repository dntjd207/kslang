<?php

use App\Models\Slang;
use App\Models\User;
use App\Services\SlangAutoFillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->mock(SlangAutoFillService::class, function (MockInterface $mock): void {});

    Storage::fake('s3');

    config([
        'services.supertone.api_key' => 'configured-api-key',
        'services.supertone.voice_id' => 'configured-voice-id',
        'services.supertone.base_url' => 'https://supertoneapi.com',
        'services.supertone.model' => 'sona_speech_1',
        'services.audio.disk' => 's3',
        'services.audio.legacy_disk' => 'public',
        'services.audio.use_temporary_url' => false,
        'filesystems.disks.s3.bucket' => 'kslang-audio-test',
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);
});

function slangAudioAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);
}

function createAudioTestSlang(array $attributes = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '억까',
        'ai_generation_hint' => null,
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '억지로 비난하거나 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 사용한다.',
        'english_usage_context' => 'It is used online to express unfair criticism.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_COMPLETE,
    ], $attributes));
}

it('generates and saves slang audio from the current korean text', function () {
    $slang = createAudioTestSlang();

    Http::fake([
        'https://supertoneapi.com/v1/text-to-speech/*' => Http::response(
            'fake-slang-mp3',
            200,
            [
                'Content-Type' => 'audio/mpeg',
                'X-Audio-Length' => '1.76',
            ]
        ),
    ]);

    $response = $this->actingAs(slangAudioAdminUser())
        ->postJson(route('admin.slangs.generateAudio', $slang), [
            'text' => '안녕하세요 호호호',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('result.text', '안녕하세요 호호호')
        ->assertJsonPath('result.audio_disk', 's3')
        ->assertJsonPath('result.audio_length_seconds', 1.76);

    $slang->refresh();

    expect($slang->audio_file)->toBeString()
        ->and($slang->audio_file)->toStartWith('audio/slangs/')
        ->and($slang->audio_disk)->toBe('s3');

    Storage::disk('s3')->assertExists($slang->audio_file);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://supertoneapi.com/v1/text-to-speech/configured-voice-id?output_format=mp3'
            && $request->hasHeader('x-sup-api-key', 'configured-api-key')
            && $data['text'] === '안녕하세요 호호호'
            && $data['language'] === 'ko'
            && $data['model'] === 'sona_speech_1'
            && $data['voice_settings']['speed'] === 0.8;
    });
});

it('generates and persists audio for an existing slang example', function () {
    $slang = createAudioTestSlang();
    $example = $slang->examples()->create([
        'korean_example' => '이건 좀 억까 아니냐?',
        'english_example' => 'Isn\'t that an unfair nitpick?',
        'sort_order' => 0,
    ]);

    Http::fake([
        'https://supertoneapi.com/v1/text-to-speech/*' => Http::response(
            'fake-example-mp3',
            200,
            [
                'Content-Type' => 'audio/mpeg',
                'X-Audio-Length' => '2.21',
            ]
        ),
    ]);

    $response = $this->actingAs(slangAudioAdminUser())
        ->postJson(route('admin.slangs.generateExampleAudio', $slang), [
            'example_id' => $example->id,
            'example_index' => 0,
            'text' => '이건 좀 억까 아니냐?',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('result.example_id', $example->id)
        ->assertJsonPath('result.persisted', true)
        ->assertJsonPath('result.audio_disk', 's3');

    $example->refresh();

    expect($example->audio_file)->toBeString()
        ->and($example->audio_file)->toStartWith('audio/slang-examples/')
        ->and($example->audio_disk)->toBe('s3');

    Storage::disk('s3')->assertExists($example->audio_file);
});

it('generates audio for a new unsaved example row and returns hidden field values', function () {
    $slang = createAudioTestSlang();

    Http::fake([
        'https://supertoneapi.com/v1/text-to-speech/*' => Http::response(
            'fake-unsaved-example-mp3',
            200,
            [
                'Content-Type' => 'audio/mpeg',
                'X-Audio-Length' => '2.05',
            ]
        ),
    ]);

    $response = $this->actingAs(slangAudioAdminUser())
        ->postJson(route('admin.slangs.generateExampleAudio', $slang), [
            'example_index' => 3,
            'text' => '새 예문도 바로 mp3 만들기',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('result.example_id', null)
        ->assertJsonPath('result.example_index', 3)
        ->assertJsonPath('result.persisted', false)
        ->assertJsonPath('result.audio_disk', 's3');

    expect($slang->examples()->count())->toBe(0);

    $generatedPath = $response->json('result.audio_file');

    expect($generatedPath)->toBeString()
        ->and($generatedPath)->toStartWith('audio/slang-examples/');

    Storage::disk('s3')->assertExists($generatedPath);
});
