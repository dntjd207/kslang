<?php

namespace App\Console\Commands;

use App\Models\Slang;
use App\Services\SlangAutoFillService;
use Illuminate\Console\Command;

class AutoFillSlangsCommand extends Command
{
    protected $signature = 'slang:auto-fill {--limit=5 : 한 번에 처리할 최대 슬랭 수}';

    protected $description = 'pending 상태의 슬랭을 Gemini AI로 자동 콘텐츠 생성';

    public function handle(SlangAutoFillService $service): int
    {
        $limit = (int) $this->option('limit');
        $pendingCount = Slang::where('content_status', Slang::STATUS_PENDING)->count();

        if ($pendingCount === 0) {
            $this->info('처리할 pending 슬랭이 없습니다.');

            return self::SUCCESS;
        }

        $this->info("pending 슬랭 {$pendingCount}건 중 최대 {$limit}건 처리를 시작합니다.");

        $result = $service->fillPendingSlangs($limit);

        $this->info("완료: 성공 {$result['filled']}건, 실패 {$result['failed']}건");

        if ($result['failed'] > 0) {
            $this->warn('실패 건은 로그를 확인해주세요.');
        }

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
