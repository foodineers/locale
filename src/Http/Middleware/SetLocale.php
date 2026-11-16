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
        /** @var list<string> $languages */
        $languages = config('locale.languages', []);

        $lang = LocaleCookie::value($request, 'lang');

        if (! in_array($lang, $languages, true)) {
            $lang = $languages[0] ?? explode('_', app()->getLocale())[0];
        }

        app()->setLocale($lang);

        return $next($request);
    }
}
