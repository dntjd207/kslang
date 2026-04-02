<?php

use App\Models\User;
use App\Services\BlogPostAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function blogAiAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'ai-admin@example.com',
        'login_id' => 'ai-admin',
        'password' => 'password',
    ]);
}

it('returns generated korean and english draft content for the current form payload', function () {
    $this->mock(BlogPostAiService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('generateDraft')
            ->once()
            ->withArgs(function (array $payload): bool {
                expect($payload['primary_keyword'])->toBe('what does eok-kka mean in korean')
                    ->and($payload['search_intent'])->toBe('meaning')
                    ->and($payload['category_name'])->toBe('Meaning')
                    ->and($payload['tag_names'])->toBe('korean slang, internet slang')
                    ->and($payload['content_brief_ko'])->toContain('영어권 사용자');

                return true;
            })
            ->andReturn([
                'title_ko' => '억까 뜻 정리',
                'excerpt_ko' => '한국어 요약',
                'body_ko' => '<h2>뜻</h2><p>한국어 초안</p>',
                'title_en' => 'What Does 억까 Mean in Korean?',
                'excerpt_en' => 'English summary.',
                'body_en' => '<h2>Meaning</h2><p>English draft.</p>',
                'seo_title_en' => 'What Does 억까 Mean in Korean?',
                'seo_description_en' => 'Learn what 억까 means in Korean.',
                'translation_model' => 'gemini-3.1-flash-lite-preview',
            ]);
    });

    $this->actingAs(blogAiAdminUser())
        ->postJson(route('admin.blog-posts.generate-draft'), [
            'category_name' => 'Meaning',
            'tag_names' => 'korean slang, internet slang',
            'primary_keyword' => 'what does eok-kka mean in korean',
            'search_intent' => 'meaning',
            'content_brief_ko' => '영어권 사용자를 위한 뜻 설명형 글이다.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title_ko', '억까 뜻 정리')
        ->assertJsonPath('data.title_en', 'What Does 억까 Mean in Korean?')
        ->assertJsonPath('data.translation_model', 'gemini-3.1-flash-lite-preview');
});

it('returns translated english content from the current korean source', function () {
    $this->mock(BlogPostAiService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('translate')
            ->once()
            ->withArgs(function (array $payload): bool {
                expect($payload['title_ko'])->toBe('억까 뜻 정리')
                    ->and($payload['body_ko'])->toContain('억까는 억지 비난')
                    ->and($payload['category_name'])->toBe('Meaning')
                    ->and($payload['tag_names'])->toBe('korean slang, internet slang')
                    ->and($payload['primary_keyword'])->toBe('what does eok-kka mean in korean');

                return true;
            })
            ->andReturn([
                'title_en' => 'What Does 억까 Mean in Korean?',
                'excerpt_en' => 'An English summary.',
                'body_en' => '<h2>Meaning</h2><p>억까 refers to unfair criticism.</p>',
                'seo_title_en' => 'What Does 억까 Mean in Korean?',
                'seo_description_en' => 'Learn what 억까 means in Korean and when people use it.',
                'translation_model' => 'gemini-3.1-flash-lite-preview',
            ]);
    });

    $this->actingAs(blogAiAdminUser())
        ->postJson(route('admin.blog-posts.translate'), [
            'category_name' => 'Meaning',
            'tag_names' => 'korean slang, internet slang',
            'primary_keyword' => 'what does eok-kka mean in korean',
            'search_intent' => 'meaning',
            'title_ko' => '억까 뜻 정리',
            'excerpt_ko' => '한국어 요약',
            'body_ko' => '<h2>뜻</h2><p>억까는 억지 비난을 뜻한다.</p>',
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.body_en', '<h2>Meaning</h2><p>억까 refers to unfair criticism.</p>')
        ->assertJsonPath('data.translation_model', 'gemini-3.1-flash-lite-preview');
});
