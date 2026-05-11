<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi;

use CoolMacJedi\LaravelCmsApi\Console\Commands\IssueToken;
use CoolMacJedi\LaravelCmsApi\Http\Middleware\AuditApiRequest;
use CoolMacJedi\LaravelCmsApi\Http\Middleware\ResolveIdempotency;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

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

        if ($this->app->runningInConsole()) {
            $this->commands([
                IssueToken::class,
            ]);
        }
    }
}
