<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->boolean('is_new')->default(false)->after('content_status');
            $table->timestamp('approved_at')->nullable()->after('is_new');
            $table->index(['is_new', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropIndex(['is_new', 'approved_at']);
            $table->dropColumn(['is_new', 'approved_at']);
        });
    }
};
