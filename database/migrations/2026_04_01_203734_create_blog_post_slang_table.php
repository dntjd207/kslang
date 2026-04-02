<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_slang', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slang_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['blog_post_id', 'slang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_slang');
    }
};
