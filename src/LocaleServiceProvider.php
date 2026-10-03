<?php

declare(strict_types=1);

namespace Foodineers\Locale;

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
}
