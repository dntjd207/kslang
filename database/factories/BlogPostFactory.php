<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titleKo = '테스트 블로그 초안';
        $titleEn = fake()->sentence(6);
        $publishedAt = now()->subDays(fake()->numberBetween(1, 14));

        return [
            'slug' => Str::slug($titleEn).'-'.fake()->unique()->numberBetween(100, 999),
            'status' => BlogPost::STATUS_DRAFT,
            'translation_status' => BlogPost::TRANSLATION_SYNCED,
            'category_name' => fake()->randomElement(['Meaning', 'Usage', 'Culture']),
            'tag_names' => 'korean slang, k-drama',
            'search_intent' => fake()->randomElement(['meaning', 'usage', 'comparison', 'warning', 'listicle', 'culture-context']),
            'primary_keyword' => fake()->words(3, true),
            'content_brief_ko' => '이 글은 테스트용 한국어 브리프입니다.',
            'title_ko' => $titleKo,
            'excerpt_ko' => '한국어 요약입니다.',
            'body_ko' => '<h2>개요</h2><p>한국어 본문입니다.</p>',
            'title_en' => $titleEn,
            'excerpt_en' => fake()->sentence(14),
            'body_en' => '<h2>Overview</h2><p>This is an English body for testing.</p>',
            'seo_title_en' => Str::limit($titleEn.' | kslang', 60, ''),
            'seo_description_en' => fake()->sentence(18),
            'canonical_url' => null,
            'translation_model' => 'gemini-3.1-flash-lite-preview',
            'last_ko_updated_at' => now()->subDay(),
            'en_synced_at' => now()->subHours(12),
            'last_auto_saved_at' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'status' => BlogPost::STATUS_PUBLISHED,
                'translation_status' => BlogPost::TRANSLATION_SYNCED,
                'published_at' => now()->subDay(),
                'en_synced_at' => now()->subDay(),
            ];
        });
    }
}
