<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi;

use CoolMacJedi\LaravelCmsApi\Console\Commands\IssueToken;
use CoolMacJedi\LaravelCmsApi\Http\Middleware\AuditApiRequest;
use CoolMacJedi\LaravelCmsApi\Http\Middleware\ResolveIdempotency;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

final class CmsApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('api.audit', AuditApiRequest::class);
        $router->aliasMiddleware('api.idempotency', ResolveIdempotency::class);
        // Sanctum's per-route ability checkers aren't auto-aliased in L11+/Sanctum 4.
        $router->aliasMiddleware('abilities', CheckAbilities::class);
        $router->aliasMiddleware('ability', CheckForAnyAbility::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                IssueToken::class,
            ]);
        }
    }
}
