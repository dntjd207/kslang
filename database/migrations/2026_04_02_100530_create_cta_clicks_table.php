<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cta_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('target', 50)->default('google_play');
            $table->string('source_type', 50);
            $table->string('placement', 50);
            $table->foreignId('blog_post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('slang_id')->nullable()->constrained()->nullOnDelete();
            $table->text('page_url')->nullable();
            $table->text('referer_url')->nullable();
            $table->timestamps();

            $table->index(['target', 'source_type']);
            $table->index('placement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cta_clicks');
    }
};
