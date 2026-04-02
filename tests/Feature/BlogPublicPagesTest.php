<?php

use App\Models\AppSetting;
use App\Models\BlogPost;
use App\Models\Slang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function blogPublicSlang(array $overrides = []): Slang
{
    return Slang::create(array_merge([
        'korean' => '억까',
        'pronunciation' => 'eok-kka',
        'english_description' => 'A slang term for unfair criticism.',
        'korean_description' => '부당하게 트집을 잡는 상황을 뜻한다.',
        'level' => 1,
        'usage_frequency' => 'Common',
        'usage_context' => '온라인에서 억울함을 표현할 때 자주 쓴다.',
        'english_usage_context' => 'It is often used online when someone feels unfairly judged.',
        'public_slug' => 'eok-kka',
        'public_title_en' => 'What does 억까 mean in Korean?',
        'public_summary_en' => 'A quick guide to the Korean slang term 억까.',
        'seo_title_en' => 'What Does 억까 Mean in Korean?',
        'seo_description_en' => 'Learn what 억까 means in Korean, when it is used, and what nuance it carries.',
        'sort_order' => 0,
        'is_active' => true,
        'content_status' => Slang::STATUS_APPROVED,
        'is_new' => true,
        'approved_at' => now()->subDay(),
    ], $overrides));
}

it('shows published blog posts on the public index and detail pages', function () {
    AppSetting::setValue('play_store_url', 'https://play.google.com/store/apps/details?id=com.example.kslang');

    $slang = blogPublicSlang();

    $publishedPost = BlogPost::factory()->published()->create([
        'slug' => 'what-does-eok-kka-mean-in-korean',
        'category_name' => 'Meaning',
        'tag_names' => 'korean slang, internet slang',
        'title_en' => 'What Does 억까 Mean in Korean?',
        'excerpt_en' => 'A guide to the Korean slang term 억까.',
        'body_en' => '<h2>Meaning</h2><p>억까 refers to unfair criticism.</p>',
        'seo_title_en' => 'What Does 억까 Mean in Korean?',
        'seo_description_en' => 'Learn what 억까 means in Korean and why people use it.',
    ]);
    $publishedPost->slangs()->sync([$slang->id]);

    BlogPost::factory()->create([
        'slug' => 'draft-only',
        'status' => BlogPost::STATUS_DRAFT,
        'published_at' => null,
        'title_en' => 'Draft Only',
    ]);

    $this->get(route('blog.index'))
        ->assertSuccessful()
        ->assertSee('What Does 억까 Mean in Korean?')
        ->assertSee('Featured guide')
        ->assertSee('data-cta-source-type="site_nav"', false)
        ->assertSee('data-cta-source-type="blog_index"', false)
        ->assertDontSee('Draft Only');

    $this->get(route('blog.show', ['blogPost' => $publishedPost->slug]))
        ->assertSuccessful()
        ->assertSee('What Does 억까 Mean in Korean?')
        ->assertSee('A guide to the Korean slang term 억까.')
        ->assertSee('Meaning')
        ->assertSee('internet slang')
        ->assertSee('Article snapshot')
        ->assertSee('data-cta-source-type="blog_show"', false)
        ->assertSee('억까')
        ->assertSee(route('slangs.public.show', ['slang' => $slang->public_slug]), false);
});

it('filters the public blog index by category and tag', function () {
    BlogPost::factory()->published()->create([
        'slug' => 'meaning-post',
        'category_name' => 'Meaning',
        'tag_names' => 'korean slang, internet slang',
        'title_en' => 'Meaning Post',
    ]);

    BlogPost::factory()->published()->create([
        'slug' => 'usage-post',
        'category_name' => 'Usage',
        'tag_names' => 'texting',
        'title_en' => 'Usage Post',
    ]);

    $this->get(route('blog.index', ['category' => 'Meaning']))
        ->assertSuccessful()
        ->assertSee('Meaning Post')
        ->assertDontSee('Usage Post');

    $this->get(route('blog.index', ['tag' => 'texting']))
        ->assertSuccessful()
        ->assertSee('Usage Post')
        ->assertDontSee('Meaning Post');
});

it('shows table of contents from blog post headings', function () {
    $blogPost = BlogPost::factory()->published()->create([
        'slug' => 'toc-test-post',
        'title_en' => 'TOC Test',
        'body_en' => '<h2>Introduction</h2><p>Hello</p><h3>Background</h3><p>Details</p><h2>Conclusion</h2><p>End</p>',
    ]);

    $this->get(route('blog.show', ['blogPost' => $blogPost->slug]))
        ->assertSuccessful()
        ->assertSee('Table of Contents')
        ->assertSee('id="introduction"', false)
        ->assertSee('id="background"', false)
        ->assertSee('id="conclusion"', false)
        ->assertSee('href="#introduction"', false)
        ->assertSee('href="#conclusion"', false);
});

it('includes published blog and public slang urls in the sitemap', function () {
    $slang = blogPublicSlang();

    $blogPost = BlogPost::factory()->published()->create([
        'slug' => 'what-does-eok-kka-mean-in-korean',
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertSuccessful();

    expect($response->getContent())->toContain(route('blog.show', ['blogPost' => $blogPost->slug]))
        ->and($response->getContent())->toContain(route('slangs.public.show', ['slang' => $slang->public_slug]));
});
