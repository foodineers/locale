<?php

declare(strict_types=1);

namespace Foodineers\Locale;

use Foodineers\Locale\Http\Middleware\SetLocale;
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
        $router = $this->app->make('router');

        $router->aliasMiddleware('locale', SetLocale::class);
    }
}
