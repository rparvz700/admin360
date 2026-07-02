<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticBearerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.mobile_api.token');
        $requestToken = $request->bearerToken();

        if (!$configuredToken || !$requestToken || !hash_equals($configuredToken, $requestToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
