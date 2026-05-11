<?php

declare(strict_types=1);

use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\CategoriesController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\LookupsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\PostsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\WhoamiController;
use Illuminate\Support\Facades\Route;

/*
 * CMS v1 API — token-scoped surface for external AI agents.
 *
 * Loaded by CmsApiServiceProvider; mounted at /api/v1 in the host app.
 *
 * - All routes require a Sanctum bearer token.
 * - Each group narrows further with `abilities:<scope>` (Sanctum middleware).
 * - Idempotency-Key header is honored on writes (api.idempotency).
 * - Writes are audit-logged (api.audit).
 * - Drafts only: new Content is created with is_published = false.
 */

Route::prefix('api/v1')
    ->middleware(['auth:sanctum', 'api.audit', 'api.idempotency'])
    ->group(function (): void {

        // Bootstrap probe — any token works.
        Route::get('/whoami', WhoamiController::class)->name('api.v1.whoami');

        // Reads — require content:read
        Route::middleware('abilities:content:read')->group(function (): void {
            Route::get('/categories', [CategoriesController::class, 'index'])->name('api.v1.categories.index');
            Route::get('/categories/{slug}', [CategoriesController::class, 'show'])->name('api.v1.categories.show');
            Route::get('/categories/{slug}/schema', [CategoriesController::class, 'schema'])->name('api.v1.categories.schema');

            Route::get('/posts', [PostsController::class, 'index'])->name('api.v1.posts.index');
            Route::get('/posts/{key}', [PostsController::class, 'show'])->name('api.v1.posts.show');

            // Lookups for FK resolution — read-coupled by convention.
            Route::prefix('lookups')->group(function (): void {
                Route::get('/categories', [LookupsController::class, 'categories'])->name('api.v1.lookups.categories');
                Route::get('/countries', [LookupsController::class, 'countries'])->name('api.v1.lookups.countries');
                Route::get('/currencies', [LookupsController::class, 'currencies'])->name('api.v1.lookups.currencies');
                Route::get('/languages', [LookupsController::class, 'languages'])->name('api.v1.lookups.languages');
                Route::get('/licenses', [LookupsController::class, 'licenses'])->name('api.v1.lookups.licenses');
                Route::get('/authors', [LookupsController::class, 'authors'])->name('api.v1.lookups.authors');
            });
        });

        // Writes — require content:write
        Route::middleware('abilities:content:write')->group(function (): void {
            Route::post('/posts', [PostsController::class, 'store'])->name('api.v1.posts.store');
            Route::patch('/posts/{post}', [PostsController::class, 'update'])->name('api.v1.posts.update');
        });

        // Deletes — require content:delete (separate scope; agents rarely need it)
        Route::middleware('abilities:content:delete')->group(function (): void {
            Route::delete('/posts/{post}', [PostsController::class, 'destroy'])->name('api.v1.posts.destroy');
        });
    });
