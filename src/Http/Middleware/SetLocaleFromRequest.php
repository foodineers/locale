<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Middleware;

use Closure;
use Foodineers\Locale\LocaleCookie;
use Foodineers\Locale\LocaleResolver;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocaleFromRequest
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var array<string, string> $cookies */
        $cookies = [];

        $lang = LocaleResolver::language($request);

        if (LocaleCookie::value($request, 'lang') !== $lang) {
            $request->cookies->set('lang', $lang);
            $cookies['lang'] = $lang;
        }

        /** @var list<string> $countries */
        $countries = config('locale.countries', []);
        $country = LocaleResolver::country($request);

        if (count($countries) > 1 && is_string($country) && LocaleCookie::value($request, 'country') !== $country) {
            $request->cookies->set('country', $country);
            $cookies['country'] = $country;
        }

        $response = $next($request);

        foreach ($cookies as $name => $value) {
            $response->headers->setCookie(LocaleCookie::make($name, $value));
        }

        return $response;
    }
}
