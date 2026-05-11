<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Middleware;

use Closure;
use CoolMacJedi\LaravelCmsApi\Models\ApiAudit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every non-GET API request into api_audits after the response
 * is built so we capture both the input payload and the result status.
 *
 * GET requests are skipped — they don't change state and the volume would
 * overwhelm the table.
 */
final class AuditApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET')) {
            return $response;
        }

        $token = $request->user()?->currentAccessToken();

        ApiAudit::create([
            'token_id' => $token?->id,
            'token_name' => $token?->name,
            'user_id' => $request->user()?->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'idempotency_key' => $request->header('Idempotency-Key'),
            'payload' => $this->redact($request->all()),
            'status' => $response->getStatusCode(),
            'response_meta' => $this->responseMeta($response),
        ]);

        return $response;
    }

    /** @param  array<string, mixed>  $payload */
    private function redact(array $payload): array
    {
        foreach ($payload as $k => $v) {
            if (in_array(strtolower((string) $k), ['password', 'token', 'secret', 'api_key'], true)) {
                $payload[$k] = '[redacted]';
            }
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function responseMeta(Response $response): array
    {
        $body = $response->getContent();
        $decoded = json_decode((string) $body, true);

        if (is_array($decoded) && isset($decoded['id'])) {
            return ['resource_id' => $decoded['id']];
        }
        if (is_array($decoded) && isset($decoded['data']['id'])) {
            return ['resource_id' => $decoded['data']['id']];
        }

        return [];
    }
}
