<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTotalRevenueReportJob;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Services\RevenueManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;

class SendTotalRevenueReportJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test if the job completes successfully.
     *
     * @return void
     */
    public function test_job_completes_successfully()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verify123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(['id' => 'report456'], 200),
            'https://revenue-reporting.com/reports/confirm' => Http::response(['status' => 'confirmed'], 200),
        ]);

        $job = new SendTotalRevenueReportJob();
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() == 'https://revenue-verifier.com';
        });

        Http::assertSent(function ($request) {
            return $request->url() == 'https://revenue-reporting.com/reports'
                && $request['verification_id'] == 'verify123';
        });

        Http::assertSent(function ($request) {
            return $request->url() == 'https://revenue-reporting.com/reports/confirm'
                && $request['report_id'] == 'report456';
        });

        $this->assertNull(Cache::get($job->cacheKey . '_step'));
        $this->assertNull(Cache::get($job->cacheKey . '_verification'));
        $this->assertNull(Cache::get($job->cacheKey . '_report'));
        $this->assertNull(Cache::get('daily_total_revenue'));
    }


    /**
     * Test if the job retries on server error.
     *
     * @return void
     */
    public function test_job_retries_on_server_error()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verify123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(null, 500),
        ]);

        $job = $this->getMockBuilder(SendTotalRevenueReportJob::class)
            ->onlyMethods(['release'])
            ->getMock();

        $job->expects($this->once())
            ->method('release');

        $job->handle();

        $this->assertEquals(1, Cache::get($job->cacheKey . '_step'));
        $this->assertNotNull(Cache::get($job->cacheKey . '_verification'));
    }

    /**
     * Test if the job retries on rate limit error.
     *
     * @return void
     */
    public function test_job_retries_on_rateLimit_error()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verify123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(null, 429),
        ]);

        $job = $this->getMockBuilder(SendTotalRevenueReportJob::class)
            ->onlyMethods(['release'])
            ->getMock();

        $job->expects($this->once())
            ->method('release');

        $job->handle();

        $this->assertEquals(1, Cache::get($job->cacheKey . '_step'));
        $this->assertNotNull(Cache::get($job->cacheKey . '_verification'));
    }

    /**
     * Test if the job retries on client error.
     *
     * @return void
     */
    public function test_job_retries_on_client_error()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verify123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(null, 400),
        ]);

        $job = $this->getMockBuilder(SendTotalRevenueReportJob::class)
            ->onlyMethods(['fail'])
            ->getMock();

        $job->expects($this->once())
            ->method('fail')
            ->with($this->isInstanceOf(RequestException::class));

        $job->handle();

        $this->assertEquals(1, Cache::get($job->cacheKey . '_step'));
        $this->assertNotNull(Cache::get($job->cacheKey . '_verification'));
    }

    /**
     * Test if the job retries on network error.
     *
     * @return void
     */
    public function test_job_resumes_from_last_successful_step()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verify123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(['id' => 'report456'], 200),
            'https://revenue-reporting.com/reports/confirm' => Http::response(['status' => 'confirmed'], 200),
        ]);

        $job = new SendTotalRevenueReportJob();

        Cache::put($job->cacheKey . '_step', 1, now()->addDay());
        Cache::put($job->cacheKey . '_verification', ['id' => 'verify123'], now()->addDay());

        $job->handle();

        Http::assertNotSent(function ($request) {
            return $request->url() == 'https://revenue-verifier.com';
        });

        Http::assertSent(function ($request) {
            return $request->url() == 'https://revenue-reporting.com/reports'
                && $request['verification_id'] == 'verify123';
        });

        Http::assertSent(function ($request) {
            return $request->url() == 'https://revenue-reporting.com/reports/confirm'
                && $request['report_id'] == 'report456';
        });

        $this->assertNull(Cache::get($job->cacheKey . '_step'));
        $this->assertNull(Cache::get($job->cacheKey . '_verification'));
        $this->assertNull(Cache::get($job->cacheKey . '_report'));
    }
}
