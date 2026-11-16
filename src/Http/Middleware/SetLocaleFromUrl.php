<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Middleware;

use Closure;
use Foodineers\Locale\LocaleCookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocaleFromUrl
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $segment = mb_strtolower(explode('/', $request->path())[0]);
        $parts = explode('-', $segment, 2);
        $urlLang = $parts[0];
        $urlCountry = $parts[1] ?? null;

        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        /** @var list<string> $languages */
        $languages = config('locale.languages', []);

        /** @var array<string, string> $cookies */
        $cookies = [];

        if (is_string($urlCountry)) {
            abort_unless(in_array($urlCountry, $countries, true), 404);

            if (LocaleCookie::value($request, 'country') !== $urlCountry) {
                $cookies['country'] = $urlCountry;
            }
        }

        if (in_array($urlLang, $languages, true) && LocaleCookie::value($request, 'lang') !== $urlLang) {
            $cookies['lang'] = $urlLang;
        }

        foreach ($cookies as $name => $value) {
            $request->cookies->set($name, $value);
        }

        $response = $next($request);

        foreach ($cookies as $name => $value) {
            $response->headers->setCookie(LocaleCookie::make($name, $value));
        }

        return $response;
    }
}
