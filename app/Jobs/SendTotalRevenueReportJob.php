<?php

namespace App\Jobs;

use App\Services\RevenueManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class SendTotalRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 5;

    /**
     * The number of seconds the job should wait before retrying.
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public ?string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->cacheKey = 'revenue_report_' . now()->format('Y-m-d');
    }

    /**
     * Execute the job.
     *
     * @throws RequestException
     */
    public function handle(): void
    {
        $step = Cache::get($this->cacheKey . '_step', 0);

        try {
            if ($step < 1) {
                $verificationResponse = $this->postVerification();
                Cache::put($this->cacheKey . '_verification', $verificationResponse, now()->addDay());
                Cache::put($this->cacheKey . '_step', 1, now()->addDay());
            } else {
                $verificationResponse = Cache::get($this->cacheKey . '_verification');
            }

            if ($step < 2) {
                $reportResponse = $this->postReport($verificationResponse);
                Cache::put($this->cacheKey . '_report', $reportResponse, now()->addDay());
                Cache::put($this->cacheKey . '_step', 2, now()->addDay());
            } else {
                $reportResponse = Cache::get($this->cacheKey . '_report');
            }

            $this->postReportConfirmation($reportResponse);

            // Clear the cache after successful job completion
            $this->clearCache();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Perform HTTP POST to verification endpoint.
     *
     * @throws RequestException
     * @return array
     */
    private function postVerification(): array
    {
        return Http::post('https://revenue-verifier.com')->throw()->json();
    }

    /**
     * Perform HTTP POST to report endpoint.
     *
     * @param array $verificationResponse
     *
     * @return array
     * @throws RequestException
     */
    private function postReport(array $verificationResponse): array
    {
        return Http::post('https://revenue-reporting.com/reports', [
            'verification_id' => $verificationResponse['id'],
            'total_revenue' => RevenueManager::calculateTotalRevenue(),
        ])->throw()->json();
    }

    /**
     * Perform HTTP POST to report confirmation endpoint.
     *
     * @param array $reportResponse
     *
     * @return array
     * @throws RequestException
     */
    private function postReportConfirmation(array $reportResponse): array
    {
        return Http::post('https://revenue-reporting.com/reports/confirm', [
            'report_id' => $reportResponse['id'],
            'timestamp' => now()->timestamp,
        ])->throw()->json();
    }

    /**
     * Handle the exception.
     *
     * @param Exception $e
     * @return void
     */
    private function handleException(Exception $e): void
    {
        if ($e instanceof RequestException) {
            if ($e->response->status() === 429) {
                $this->release(now()->addMinutes(15));
            } elseif ($e->response->status() >= 500) {
                $this->release(now()->addSeconds(30));
            } else {
                $this->fail($e);
            }
        } else {
            $this->fail($e);
        }
    }

    /**
     * Clear the cache.
     * @return void
     */
    private function clearCache(): void
    {
        Cache::forget($this->cacheKey . '_step');
        Cache::forget($this->cacheKey . '_verification');
        Cache::forget($this->cacheKey . '_report');
        Cache::forget('daily_total_revenue');
    }
}
