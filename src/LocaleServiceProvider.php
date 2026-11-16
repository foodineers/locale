<?php

declare(strict_types=1);

namespace Foodineers\Locale;

use Foodineers\Locale\Http\Middleware\SetLocale;
use Foodineers\Locale\Http\Middleware\SetLocaleFromRequest;
use Foodineers\Locale\Http\Middleware\SetLocaleFromUrl;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LocaleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('locale')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('locale.url', SetLocaleFromUrl::class);
        $router->aliasMiddleware('locale.request', SetLocaleFromRequest::class);
        $router->aliasMiddleware('locale', SetLocale::class);
    }
}
