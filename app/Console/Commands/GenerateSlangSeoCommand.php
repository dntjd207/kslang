<?php

namespace App\Console\Commands;

use App\Models\Slang;
use App\Services\SlangAutoFillService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateSlangSeoCommand extends Command
{
    protected $signature = 'slang:generate-seo
        {--all : SEO 필드가 이미 있는 단어도 포함하여 전부 재생성}
        {--id=* : 특정 슬랭 ID만 처리 (여러 개 가능)}
        {--delay=2 : API 호출 사이 대기 시간(초)}';

    protected $description = '활성 슬랭의 SEO 필드(title, description, keywords, summary)를 AI로 일괄 생성';

    public function handle(SlangAutoFillService $autoFillService): int
    {
        $slangs = $this->resolveSlangs();

        if ($slangs->isEmpty()) {
            $this->info('처리할 슬랭이 없습니다.');

            return self::SUCCESS;
        }

        $total = $slangs->count();
        $delay = max(0, (int) $this->option('delay'));
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;

        $this->newLine();
        $this->info("=== 슬랭 SEO 일괄 생성 시작 (총 {$total}건) ===");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(
            " %current%/%max% [%bar%] %percent:3s%%\n"
            ." 현재: %message%\n"
            ." 성공: <info>%succeeded%</info>  실패: <error>%failed%</error>  건너뜀: <comment>%skipped%</comment>\n"
        );
        $bar->setMessage('준비 중...');
        $bar->setMessage('0', 'succeeded');
        $bar->setMessage('0', 'failed');
        $bar->setMessage('0', 'skipped');
        $bar->start();

        foreach ($slangs as $index => $slang) {
            $label = "{$slang->korean} (ID: {$slang->id})";
            $bar->setMessage($label);

            if (! $this->shouldProcess($slang)) {
                $skipped++;
                $bar->setMessage((string) $skipped, 'skipped');
                $bar->advance();

                continue;
            }

            try {
                $autoFillService->generateAndSaveSeoFields($slang);
                $succeeded++;
                $bar->setMessage((string) $succeeded, 'succeeded');
            } catch (Throwable $e) {
                $failed++;
                $bar->setMessage((string) $failed, 'failed');
                $this->logFailure($slang, $e);
            }

            $bar->advance();

            if ($delay > 0 && $index < $total - 1) {
                sleep($delay);
            }
        }

        $bar->setMessage('완료!');
        $bar->finish();

        $this->newLine(2);
        $this->table(
            ['항목', '건수'],
            [
                ['전체 대상', $total],
                ['성공', $succeeded],
                ['실패', $failed],
                ['건너뜀 (이미 SEO 있음)', $skipped],
            ]
        );

        if ($failed > 0) {
            $this->warn("{$failed}건 실패 — 로그를 확인해주세요.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return Collection<int, Slang>
     */
    private function resolveSlangs(): Collection
    {
        $ids = array_filter(array_map('intval', $this->option('id')));

        if ($ids !== []) {
            return Slang::query()
                ->whereIn('id', $ids)
                ->orderBy('sort_order')
                ->get();
        }

        return Slang::query()
            ->where('is_active', true)
            ->whereIn('content_status', [Slang::STATUS_COMPLETE, Slang::STATUS_APPROVED])
            ->where(function (Builder $query): void {
                $query->whereNotNull('pronunciation')
                    ->where('pronunciation', '!=', '');
            })
            ->orderBy('sort_order')
            ->get();
    }

    private function shouldProcess(Slang $slang): bool
    {
        if ($this->option('all') || $this->option('id') !== []) {
            return true;
        }

        return trim((string) $slang->seo_title_en) === ''
            || trim((string) $slang->seo_description_en) === ''
            || trim((string) $slang->seo_keywords_en) === '';
    }

    private function logFailure(Slang $slang, Throwable $e): void
    {
        Log::error("SlangSeoGeneration 실패: {$slang->korean} (ID: {$slang->id})", [
            'error' => $e->getMessage(),
        ]);
    }
}
