<?php

use App\Models\CtaClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function ctaAdminUser(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin@cta.test',
        'login_id' => 'cta_admin',
        'password' => 'password',
    ]);
}

it('requires authentication to access the cta clicks page', function () {
    $this->get(route('admin.cta-clicks.index'))
        ->assertRedirect();
});

it('renders the cta clicks dashboard for authenticated admin', function () {
    $this->actingAs(ctaAdminUser())
        ->get(route('admin.cta-clicks.index'))
        ->assertOk()
        ->assertSee('CTA 클릭 집계');
});

it('shows click stats grouped by source type and placement', function () {
    CtaClick::create([
        'target' => 'google_play',
        'source_type' => 'blog_show',
        'placement' => 'sidebar',
        'page_url' => 'https://kslang.test/blog/test',
    ]);

    CtaClick::create([
        'target' => 'google_play',
        'source_type' => 'slang_show',
        'placement' => 'hero',
        'page_url' => 'https://kslang.test/korean-slang/test',
    ]);

    $this->actingAs(ctaAdminUser())
        ->get(route('admin.cta-clicks.index', ['range' => 'all']))
        ->assertOk()
        ->assertSee('blog_show')
        ->assertSee('slang_show')
        ->assertSee('sidebar')
        ->assertSee('hero');
});

it('filters clicks by date range', function () {
    CtaClick::create([
        'target' => 'google_play',
        'source_type' => 'blog_show',
        'placement' => 'inline_top',
        'page_url' => 'https://kslang.test/blog/old',
        'created_at' => now()->subDays(60),
    ]);

    CtaClick::create([
        'target' => 'google_play',
        'source_type' => 'slang_show',
        'placement' => 'hero',
        'page_url' => 'https://kslang.test/korean-slang/recent',
        'created_at' => now(),
    ]);

    $response = $this->actingAs(ctaAdminUser())
        ->get(route('admin.cta-clicks.index', ['range' => '7d']));

    $response->assertOk()
        ->assertSee('slang_show');
});
