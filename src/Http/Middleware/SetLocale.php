<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Middleware;

use Closure;
use Foodineers\Locale\LocaleCookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        /** @var list<string> $languages */
        $languages = config('locale.languages', []);

        $segment = mb_strtolower(explode('/', $request->path())[0]);
        $parts = explode('-', $segment, 2);
        $urlLang = $parts[0];
        $urlCountry = $parts[1] ?? null;

        /** @var array<string, string> $cookies */
        $cookies = [];

        if (is_string($urlCountry)) {
            abort_unless(in_array($urlCountry, $countries, true), 404);

            $storedCountry = $this->stored($request, 'country');

            if (! in_array($storedCountry, $countries, true)) {
                $cookies['country'] = $urlCountry;
            }
        }

        $storedLang = $this->stored($request, 'lang');

        if (in_array($storedLang, $languages, true)) {
            $lang = $storedLang;
        } elseif (in_array($urlLang, $languages, true)) {
            $lang = $urlLang;
        } else {
            $lang = $languages[0] ?? explode('_', app()->getLocale())[0];
        }

        if ($storedLang !== $lang) {
            $cookies['lang'] = $lang;
        }

        app()->setLocale($lang);

        $response = $next($request);

        foreach ($cookies as $name => $value) {
            $response->headers->setCookie(LocaleCookie::make($name, $value));
        }

        return $response;
    }

    private function stored(Request $request, string $name): string
    {
        $value = $request->cookie($name);

        return is_string($value) ? mb_strtolower($value) : '';
    }
}
