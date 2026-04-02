<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->string('public_slug')->nullable()->after('pronunciation');
            $table->string('public_title_en')->nullable()->after('english_usage_context');
            $table->text('public_summary_en')->nullable()->after('public_title_en');
            $table->string('seo_title_en')->nullable()->after('public_summary_en');
            $table->text('seo_description_en')->nullable()->after('seo_title_en');

            $table->unique('public_slug');
        });

        DB::table('slangs')
            ->select(['id', 'korean', 'pronunciation'])
            ->orderBy('id')
            ->get()
            ->each(function ($slang): void {
                $base = Str::slug((string) $slang->pronunciation);

                if ($base === '') {
                    $base = Str::slug((string) $slang->korean);
                }

                if ($base === '') {
                    $base = 'slang';
                }

                $slug = $this->resolveUniqueSlug($base, (int) $slang->id);

                DB::table('slangs')
                    ->where('id', $slang->id)
                    ->update([
                        'public_slug' => $slug,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('slangs', function (Blueprint $table) {
            $table->dropUnique('slangs_public_slug_unique');
            $table->dropColumn([
                'public_slug',
                'public_title_en',
                'public_summary_en',
                'seo_title_en',
                'seo_description_en',
            ]);
        });
    }

    private function resolveUniqueSlug(string $base, int $slangId): string
    {
        $candidate = $base;
        $suffix = 2;

        while (
            DB::table('slangs')
                ->where('public_slug', $candidate)
                ->where('id', '!=', $slangId)
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
};
