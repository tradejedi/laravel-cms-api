<?php

declare(strict_types=1);

use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\WhoamiController;
use Illuminate\Support\Facades\Route;

/*
 * CMS v1 API — token-scoped surface for external AI agents.
 *
 * Loaded by CmsApiServiceProvider; mounted at /api/v1 in the host app.
 *
 * - All routes require a Sanctum bearer token with explicit abilities.
 * - Idempotency-Key header is honored on writes via ResolveIdempotency.
 * - Every write is audit-logged via AuditApiRequest middleware.
 * - Drafts only: new Content is created with is_published = false; the API never
 *   auto-publishes.
 */

Route::prefix('api/v1')
    ->middleware(['auth:sanctum', 'api.audit', 'api.idempotency'])
    ->group(function (): void {
        Route::get('/whoami', WhoamiController::class)->name('api.v1.whoami');
    });
