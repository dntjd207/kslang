<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function supertoneAdminUser(): User
{
    $user = new User([
        'id' => 1,
        'name' => '관리자',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);

    $user->exists = true;

    return $user;
}

beforeEach(function () {
    $this->withoutVite();
});

it('shows the supertone tts admin page to authenticated admins', function () {
    Storage::fake('s3');

    config([
        'services.supertone.storage_disk' => 's3',
        'services.supertone.use_temporary_url' => false,
    ]);

    $this->actingAs(supertoneAdminUser())
        ->get('/admin/supertone-tts')
        ->assertSuccessful()
        ->assertSee('Supertone TTS 테스트')
        ->assertSee('음성 생성 및 저장');
});

it('generates and stores an mp3 file using configured credentials', function () {
    Storage::fake('s3');

    config([
        'services.supertone.api_key' => 'configured-api-key',
        'services.supertone.voice_id' => 'configured-voice-id',
        'services.supertone.base_url' => 'https://supertoneapi.com',
        'services.supertone.storage_disk' => 's3',
        'services.supertone.use_temporary_url' => false,
        'filesystems.disks.s3.bucket' => 'tts-bucket',
    ]);

    Http::fake([
        'https://supertoneapi.com/v1/text-to-speech/*' => Http::response(
            'fake-mp3-binary',
            200,
            [
                'Content-Type' => 'audio/mpeg',
                'X-Audio-Length' => '2.87',
            ]
        ),
    ]);

    $response = $this->actingAs(supertoneAdminUser())
        ->postJson('/admin/supertone-tts/generate', [
            'text' => '안녕하세요. 수퍼톤 테스트입니다.',
            'language' => 'ko',
            'model' => 'sona_speech_1',
            'pitch_shift' => 0,
            'pitch_variance' => 1,
            'speed' => 1,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('result.voice_id', 'configured-voice-id')
        ->assertJsonPath('result.language', 'ko')
        ->assertJsonPath('result.model', 'sona_speech_1')
        ->assertJsonPath('result.audio_length_seconds', 2.87)
        ->assertJsonPath('result.storage_disk', 's3')
        ->assertJsonPath('result.storage_location', 's3://tts-bucket/audio/supertone-tts');

    $audioPath = $response->json('result.audio_path');

    expect($audioPath)->toBeString();
    expect($audioPath)->toContain('audio/supertone-tts/');

    Storage::disk('s3')->assertExists($audioPath);
    Storage::disk('s3')->assertExists(str_replace('.mp3', '.json', $audioPath));

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://supertoneapi.com/v1/text-to-speech/configured-voice-id?output_format=mp3'
            && $request->hasHeader('x-sup-api-key', 'configured-api-key')
            && $data['text'] === '안녕하세요. 수퍼톤 테스트입니다.'
            && $data['language'] === 'ko'
            && $data['model'] === 'sona_speech_1'
            && $data['voice_settings']['speed'] === 1.0;
    });
});

it('accepts api key and voice id directly from the request when env config is missing', function () {
    Storage::fake('s3');

    config([
        'services.supertone.api_key' => null,
        'services.supertone.voice_id' => null,
        'services.supertone.base_url' => 'https://supertoneapi.com',
        'services.supertone.storage_disk' => 's3',
        'services.supertone.use_temporary_url' => false,
        'filesystems.disks.s3.bucket' => 'tts-bucket',
    ]);

    Http::fake([
        'https://supertoneapi.com/v1/text-to-speech/*' => Http::response(
            'manual-mp3-binary',
            200,
            [
                'Content-Type' => 'audio/mpeg',
            ]
        ),
    ]);

    $this->actingAs(supertoneAdminUser())
        ->postJson('/admin/supertone-tts/generate', [
            'api_key' => 'manual-api-key',
            'voice_id' => 'manual-voice-id',
            'text' => '직접 입력 자격증명 테스트',
            'language' => 'ko',
            'model' => 'sona_speech_1',
            'pitch_shift' => 0,
            'pitch_variance' => 1,
            'speed' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('result.voice_id', 'manual-voice-id');

    Http::assertSent(function ($request) {
        return $request->hasHeader('x-sup-api-key', 'manual-api-key')
            && $request->url() === 'https://supertoneapi.com/v1/text-to-speech/manual-voice-id?output_format=mp3';
    });
});

it('validates api key and voice id when no configured defaults exist', function () {
    config([
        'services.supertone.api_key' => null,
        'services.supertone.voice_id' => null,
    ]);

    $this->actingAs(supertoneAdminUser())
        ->postJson('/admin/supertone-tts/generate', [
            'text' => '검증 오류 테스트',
            'language' => 'ko',
            'model' => 'sona_speech_1',
            'pitch_shift' => 0,
            'pitch_variance' => 1,
            'speed' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['api_key', 'voice_id']);
});
