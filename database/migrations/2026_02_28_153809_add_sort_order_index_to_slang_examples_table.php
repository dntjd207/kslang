<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slang_examples', function (Blueprint $table) {
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('slang_examples', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
        });
    }
};
