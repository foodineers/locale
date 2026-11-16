<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Controllers;

use Foodineers\Locale\LocaleCookie;
use Foodineers\Locale\LocaleResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LocaleRedirector
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        $lang = LocaleResolver::language($request);
        $country = LocaleResolver::country($request);

        $prefix = $country === null ? $lang : $lang . '-' . $country;
        $qs = $request->server->getString('QUERY_STRING');

        $redirect = redirect('/' . $prefix . '/' . $request->path() . ($qs === '' ? '' : '?' . $qs))
            ->withCookie(LocaleCookie::make('lang', $lang));

        if (count($countries) > 1 && $country !== null) {
            $redirect->withCookie(LocaleCookie::make('country', $country));
        }

        return $redirect;
    }
}
