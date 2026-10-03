<?php

declare(strict_types=1);

namespace Foodineers\Locale;

use Symfony\Component\HttpFoundation\Cookie;

final class LocaleCookie
{
    public static function make(string $name, string $value): Cookie
    {
        /** @var string|null $path */
        $path = config('session.path');

        /** @var string|null $domain */
        $domain = config('session.domain');

        /** @var bool|null $secure */
        $secure = config('session.secure');

        /** @var bool $httpOnly */
        $httpOnly = config('session.http_only', true);

        /** @var string|null $sameSite */
        $sameSite = config('session.same_site');

        /** @var bool $partitioned */
        $partitioned = config('session.partitioned', false);

        return cookie()->forever($name, $value, $path, $domain, $secure, $httpOnly, false, $sameSite)
            ->withPartitioned($partitioned);
    }
}
