<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->json('thread_post_formats')->nullable()->after('content_status');
            $table->timestamp('thread_post_generated_at')->nullable()->after('thread_post_formats');
        });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropColumn(['thread_post_formats', 'thread_post_generated_at']);
        });
    }
};
