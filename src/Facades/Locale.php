<?php

declare(strict_types=1);

namespace Foodineers\Locale\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Foodineers\Locale\Locale
 */
final class Locale extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Foodineers\Locale\Locale::class;
    }
}
