<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slangs', function (Blueprint $table) {
            $table->id();
            $table->string('korean');
            $table->string('pronunciation');
            $table->text('english_description');
            $table->text('korean_description');
            $table->tinyInteger('level')->unsigned();
            $table->string('usage_frequency', 50);
            $table->text('usage_context');
            $table->string('audio_file')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('level');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slangs');
    }
};
