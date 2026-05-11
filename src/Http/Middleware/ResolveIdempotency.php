<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stripe-style idempotency for write requests.
 *
 * If the client sends Idempotency-Key on a non-GET request, the first
 * response is cached for IDEMPOTENCY_TTL seconds; subsequent calls with
 * the same key replay that response verbatim. Lets AI agents safely
 * retry on flaky network without creating duplicates.
 *
 * Cache key is namespaced by token id so two tokens can reuse the same
 * Idempotency-Key string without collision.
 */
final class ResolveIdempotency
{
    private const TTL_SECONDS = 600;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');
        if (! $key) {
            return $next($request);
        }

        $token = $request->user()?->currentAccessToken();
        $cacheKey = 'api.idempotency.'.($token?->id ?? 'anon').'.'.$key;

        if ($cached = Cache::get($cacheKey)) {
            return response($cached['body'], $cached['status'])
                ->withHeaders($cached['headers']);
        }

        $response = $next($request);

        Cache::put($cacheKey, [
            'status' => $response->getStatusCode(),
            'body' => $response->getContent(),
            'headers' => [
                'Content-Type' => $response->headers->get('Content-Type'),
                'X-Idempotent-Replay-Of' => $key,
            ],
        ], self::TTL_SECONDS);

        return $response;
    }
}
