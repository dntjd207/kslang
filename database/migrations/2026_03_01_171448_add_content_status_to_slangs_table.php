<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->string('content_status', 20)->default('complete')->after('is_active');
            $table->index('content_status');
        });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropIndex(['content_status']);
            $table->dropColumn('content_status');
        });
    }
};
