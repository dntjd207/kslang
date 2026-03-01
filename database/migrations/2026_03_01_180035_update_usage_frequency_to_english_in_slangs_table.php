<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $mapping = [
        '자주 사용' => 'Common',
        '가끔 사용' => 'Occasional',
        '거의 안씀' => 'Rare',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $korean => $english) {
            DB::table('slangs')
                ->where('usage_frequency', $korean)
                ->update(['usage_frequency' => $english]);
        }
    }

    public function down(): void
    {
        foreach ($this->mapping as $korean => $english) {
            DB::table('slangs')
                ->where('usage_frequency', $english)
                ->update(['usage_frequency' => $korean]);
        }
    }
};
