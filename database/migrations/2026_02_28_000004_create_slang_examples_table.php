<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slang_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slang_id')->constrained()->cascadeOnDelete();
            $table->text('korean_example');
            $table->text('english_example');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('slang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slang_examples');
    }
};
