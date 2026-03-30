<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slang_examples', function (Blueprint $table) {
            $table->string('audio_file')->nullable();
            $table->string('audio_disk', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('slang_examples', function (Blueprint $table) {
            $table->dropColumn(['audio_file', 'audio_disk']);
        });
    }
};
