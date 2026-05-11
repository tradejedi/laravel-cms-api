<?php

declare(strict_types=1);

use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\AuthorsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\BlocksController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\CategoriesController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\ContentParamsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\ContentRelationsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\FaqsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\LookupsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\MediaController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\MenusController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\PagesController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\PostsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\ProConsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\SliderItemsController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\TableBuildersController;
use CoolMacJedi\LaravelCmsApi\Http\Controllers\V1\WhoamiController;
use Illuminate\Support\Facades\Route;

/*
 * CMS v1 API — token-scoped surface for external AI agents.
 *
 * Loaded by CmsApiServiceProvider; mounted at /api/v1.
 */

Route::prefix('api/v1')
    ->middleware(['auth:sanctum', 'api.audit', 'api.idempotency'])
    ->group(function (): void {

        Route::get('/whoami', WhoamiController::class)->name('api.v1.whoami');

        // ── Reads ───────────────────────────────────────────────────────
        Route::middleware('abilities:content:read')->group(function (): void {
            Route::get('/categories', [CategoriesController::class, 'index']);
            Route::get('/categories/{slug}', [CategoriesController::class, 'show']);
            Route::get('/categories/{slug}/schema', [CategoriesController::class, 'schema']);

            Route::get('/posts', [PostsController::class, 'index']);
            Route::get('/posts/{key}', [PostsController::class, 'show']);

            Route::get('/pages', [PagesController::class, 'index']);
            Route::get('/pages/{key}', [PagesController::class, 'show']);

            Route::get('/posts/{post}/params', [ContentParamsController::class, 'index']);
            Route::get('/posts/{post}/faqs', [FaqsController::class, 'index']);
            Route::get('/posts/{post}/pro-cons', [ProConsController::class, 'index']);
            Route::get('/posts/{post}/relations', [ContentRelationsController::class, 'index']);

            Route::prefix('lookups')->group(function (): void {
                Route::get('/categories', [LookupsController::class, 'categories']);
                Route::get('/countries', [LookupsController::class, 'countries']);
                Route::get('/currencies', [LookupsController::class, 'currencies']);
                Route::get('/languages', [LookupsController::class, 'languages']);
                Route::get('/licenses', [LookupsController::class, 'licenses']);
                Route::get('/authors', [LookupsController::class, 'authors']);
            });
        });

        Route::middleware('abilities:block:read')->group(function (): void {
            Route::get('/blocks', [BlocksController::class, 'index']);
            Route::get('/blocks/{block}', [BlocksController::class, 'show']);
        });
        Route::middleware('abilities:table:read')->group(function (): void {
            Route::get('/tables', [TableBuildersController::class, 'index']);
            Route::get('/tables/{key}', [TableBuildersController::class, 'show']);
        });
        Route::middleware('abilities:menu:read')->group(function (): void {
            Route::get('/menus', [MenusController::class, 'index']);
            Route::get('/menus/{menu}', [MenusController::class, 'show']);
            Route::get('/menus/{menu}/items', [MenusController::class, 'indexItems']);
        });
        Route::middleware('abilities:slider:read')->group(function (): void {
            Route::get('/slider-items', [SliderItemsController::class, 'index']);
        });
        Route::middleware('abilities:author:read')->group(function (): void {
            Route::get('/authors', [AuthorsController::class, 'index']);
            Route::get('/authors/{key}', [AuthorsController::class, 'show']);
        });

        Route::middleware('abilities:media:read')->group(function (): void {
            Route::get('/posts/{post}/media', [MediaController::class, 'index']);
        });

        // ── Writes ──────────────────────────────────────────────────────
        Route::middleware('abilities:content:write')->group(function (): void {
            Route::post('/posts', [PostsController::class, 'store']);
            Route::patch('/posts/{post}', [PostsController::class, 'update']);

            Route::post('/pages', [PagesController::class, 'store']);
            Route::patch('/pages/{page}', [PagesController::class, 'update']);

            Route::patch('/posts/{post}/params/{key}', [ContentParamsController::class, 'upsert']);
            Route::delete('/posts/{post}/params/{key}', [ContentParamsController::class, 'destroy']);

            Route::post('/posts/{post}/faqs', [FaqsController::class, 'store']);
            Route::patch('/faqs/{faq}', [FaqsController::class, 'update']);
            Route::delete('/faqs/{faq}', [FaqsController::class, 'destroy']);

            Route::post('/posts/{post}/pro-cons', [ProConsController::class, 'store']);
            Route::patch('/pro-cons/{proCon}', [ProConsController::class, 'update']);
            Route::delete('/pro-cons/{proCon}', [ProConsController::class, 'destroy']);

            Route::post('/posts/{post}/relations', [ContentRelationsController::class, 'store']);
            Route::delete('/relations/{relation}', [ContentRelationsController::class, 'destroy']);
        });

        Route::middleware('abilities:media:upload')->group(function (): void {
            Route::post('/posts/{post}/media', [MediaController::class, 'store']);
        });
        Route::middleware('abilities:media:delete')->group(function (): void {
            Route::delete('/media/{media}', [MediaController::class, 'destroy']);
        });

        // ── Deletes (Content) ───────────────────────────────────────────
        Route::middleware('abilities:content:delete')->group(function (): void {
            Route::delete('/posts/{post}', [PostsController::class, 'destroy']);
            Route::delete('/pages/{page}', [PagesController::class, 'destroy']);
        });

        Route::middleware('abilities:block:write')->group(function (): void {
            Route::post('/blocks', [BlocksController::class, 'store']);
            Route::patch('/blocks/{block}', [BlocksController::class, 'update']);
            Route::delete('/blocks/{block}', [BlocksController::class, 'destroy']);
        });
        Route::middleware('abilities:table:write')->group(function (): void {
            Route::post('/tables', [TableBuildersController::class, 'store']);
            Route::patch('/tables/{table}', [TableBuildersController::class, 'update']);
            Route::delete('/tables/{table}', [TableBuildersController::class, 'destroy']);
        });
        Route::middleware('abilities:menu:write')->group(function (): void {
            Route::post('/menus', [MenusController::class, 'store']);
            Route::patch('/menus/{menu}', [MenusController::class, 'update']);
            Route::delete('/menus/{menu}', [MenusController::class, 'destroy']);

            Route::post('/menus/{menu}/items', [MenusController::class, 'storeItem']);
            Route::patch('/menus/items/{item}', [MenusController::class, 'updateItem']);
            Route::delete('/menus/items/{item}', [MenusController::class, 'destroyItem']);
        });
        Route::middleware('abilities:slider:write')->group(function (): void {
            Route::post('/slider-items', [SliderItemsController::class, 'store']);
            Route::patch('/slider-items/{item}', [SliderItemsController::class, 'update']);
            Route::delete('/slider-items/{item}', [SliderItemsController::class, 'destroy']);
        });
        Route::middleware('abilities:author:write')->group(function (): void {
            Route::post('/authors', [AuthorsController::class, 'store']);
            Route::patch('/authors/{author}', [AuthorsController::class, 'update']);
            Route::delete('/authors/{author}', [AuthorsController::class, 'destroy']);
        });
        Route::middleware('abilities:media:upload')->group(function (): void {
            Route::post('/authors/{author}/avatar', [AuthorsController::class, 'uploadAvatar']);
        });
    });
