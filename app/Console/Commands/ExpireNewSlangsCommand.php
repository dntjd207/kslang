<?php

namespace App\Console\Commands;

use App\Services\SlangService;
use Illuminate\Console\Command;

class ExpireNewSlangsCommand extends Command
{
    protected $signature = 'slang:expire-new {--days=3 : 신규 표시를 유지할 승인 경과 일수}';

    protected $description = '승인 후 일정 기간이 지난 슬랭의 신규 표시를 해제';

    public function handle(SlangService $slangService): int
    {
        $days = max(1, (int) $this->option('days'));
        $expiredCount = $slangService->expireNewSlangs($days);

        if ($expiredCount === 0) {
            $this->info("{$days}일이 지난 신규 슬랭이 없습니다.");

            return self::SUCCESS;
        }

        $this->info("{$expiredCount}건의 슬랭 신규 표시를 해제했습니다.");

        return self::SUCCESS;
    }
}
