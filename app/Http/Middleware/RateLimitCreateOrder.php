<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Config;

class RateLimitCreateOrder
{

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branchId = $request->input('branch_id')?? 0;
        $limiterKey = "create_order_{$branchId}";
        $branchConfig = Config::get("rate_limits.branches.{$branchId}", Config::get('rate_limits.default'));
        $maxAttempts = $branchConfig['attempts'];
        $decaySeconds = $branchConfig['decay'];

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            return ResponseHelper::failResponse(
                'Too many order creation attempts.',
                ['Too many order creation attempts. Please try again later.'],
                429
            );
        }

        RateLimiter::hit($limiterKey, $decaySeconds);
        return $next($request);
    }
}
