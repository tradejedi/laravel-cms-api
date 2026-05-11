<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use CoolMacJedi\LaravelCmsApi\Services\BackupShell;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 *   GET   /api/v1/backups                          list FTP backups for current site
 *   POST  /api/v1/backups                          trigger a new backup (sync, ~10s-60s)
 *   POST  /api/v1/backups/{date}/restore           restore from a given date
 *
 * The restore endpoint is destructive — see the confirmation gate below.
 */
final class BackupsController extends Controller
{
    public function __construct(private readonly BackupShell $shell) {}

    public function index(): JsonResponse
    {
        return response()->json($this->shell->list());
    }

    public function create(): JsonResponse
    {
        $result = $this->shell->create();
        $status = $result['status'] === 'ok' ? 201 : 500;

        return response()->json($result, $status);
    }

    /**
     * Destructive: replaces the current release directory with content from
     * the backup of the given date and pg_restores the database.
     *
     * Confirmation gate: must send `?confirm=RESTORE_<date>` literally,
     * otherwise the call short-circuits with 422 and a helpful message.
     */
    public function restore(string $date, Request $request): JsonResponse
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['errors' => ['date' => ['Bad date format (YYYY-MM-DD)']]], 422);
        }

        $expected = 'RESTORE_'.$date;
        $confirm = (string) $request->query('confirm', '');
        if ($confirm !== $expected) {
            return response()->json([
                'errors' => ['confirm' => [
                    "Restore is destructive. Pass ?confirm={$expected} to proceed.",
                ]],
                'date' => $date,
                'expected_confirm_token' => $expected,
            ], 422);
        }

        // Use the Idempotency-Key (already required for production agents) as
        // the request id so a retry doesn't try a second swap on top of itself.
        $reqId = $request->header('Idempotency-Key') ?: Str::uuid()->toString();
        $reqId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $reqId) ?: 'noid';

        $result = $this->shell->restore($date, $reqId);
        $status = ($result['status'] ?? '') === 'restored' ? 200 : 500;

        return response()->json($result, $status);
    }
}
