<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->string('seo_keywords_en', 500)->nullable()->after('seo_description_en');
        });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropColumn('seo_keywords_en');
        });
    }
};
