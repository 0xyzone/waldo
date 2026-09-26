<?php

namespace App\Jobs;

use App\Models\TipsReport;
use App\Services\TipsCalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateTipsDistributionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public TipsReport $report,
        public array $payload = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TipsCalculationService $service): void
    {
        try {
            $service->generate($this->report, $this->payload);
        } catch (\Throwable $e) {
            Log::error('Failed to generate tips distribution: '.$e->getMessage(), [
                'report_id' => $this->report->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->report->update(['status' => 'draft']);
            throw $e;
        }
    }
}
