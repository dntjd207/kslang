<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('status', 20)->default('draft');
            $table->string('translation_status', 20)->default('none');
            $table->string('category_name')->nullable();
            $table->text('tag_names')->nullable();
            $table->string('search_intent', 50)->nullable();
            $table->string('primary_keyword')->nullable();
            $table->text('content_brief_ko')->nullable();
            $table->string('title_ko')->nullable();
            $table->text('excerpt_ko')->nullable();
            $table->longText('body_ko')->nullable();
            $table->string('title_en')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('body_en')->nullable();
            $table->string('seo_title_en')->nullable();
            $table->text('seo_description_en')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('translation_model')->nullable();
            $table->timestamp('last_ko_updated_at')->nullable();
            $table->timestamp('en_synced_at')->nullable();
            $table->timestamp('last_auto_saved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('category_name');
            $table->index('status');
            $table->index('translation_status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
