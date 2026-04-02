<?php

use App\Models\BlogPost;
use App\Models\CtaClick;
use App\Models\Slang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a tracked cta click for a blog page', function () {
    $blogPost = BlogPost::factory()->published()->create();
    $slang = Slang::create([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '부당하게 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 자주 쓴다.',
        'english_usage_context' => 'It is often used online when someone feels unfairly judged.',
        'public_slug' => 'eok-kka',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_APPROVED,
        'approved_at' => now()->subDay(),
    ]);

    $this->withHeader('referer', 'https://kslang.test/blog/'.$blogPost->slug)
        ->postJson(route('cta-clicks.store'), [
            'target' => 'google_play',
            'source_type' => 'blog_show',
            'placement' => 'sidebar',
            'blog_post_id' => $blogPost->id,
            'slang_id' => $slang->id,
            'page_url' => 'https://kslang.test/blog/'.$blogPost->slug,
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $ctaClick = CtaClick::query()->first();

    expect($ctaClick)->not->toBeNull()
        ->and($ctaClick->target)->toBe('google_play')
        ->and($ctaClick->source_type)->toBe('blog_show')
        ->and($ctaClick->placement)->toBe('sidebar')
        ->and($ctaClick->blog_post_id)->toBe($blogPost->id)
        ->and($ctaClick->slang_id)->toBe($slang->id)
        ->and($ctaClick->page_url)->toBe('https://kslang.test/blog/'.$blogPost->slug)
        ->and($ctaClick->referer_url)->toBe('https://kslang.test/blog/'.$blogPost->slug);
});
