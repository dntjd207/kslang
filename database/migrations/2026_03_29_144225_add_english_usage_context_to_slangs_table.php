<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->text('english_usage_context')->nullable()->after('usage_context');
        });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropColumn('english_usage_context');
        });
    }
};
