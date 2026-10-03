<?php

declare(strict_types=1);

namespace Foodineers\Locale;

use Foodineers\Locale\Commands\LocaleCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LocaleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('locale')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_locale_table')
            ->hasCommand(LocaleCommand::class);
    }
}
