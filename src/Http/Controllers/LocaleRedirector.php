<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Controllers;

use Foodineers\Locale\LocaleCookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LocaleRedirector
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        $country = null;

        if (count($countries) === 1) {
            $country = $countries[0];
        } elseif (count($countries) > 1) {
            foreach ([$request->cookie('country'), $request->header('CF-IPCountry')] as $raw) {
                $code = is_string($raw) ? mb_strtolower($raw) : '';
                if (in_array($code, $countries, true)) {
                    $country = $code;
                    break;
                }
            }
        }

        /** @var list<string> $languages */
        $languages = config('locale.languages', []);

        if (count($languages) === 1) {
            $lang = $languages[0];
        } elseif ($languages === []) {
            $lang = explode('_', app()->getLocale())[0];
        } else {
            $cookie = $request->cookie('lang');
            $code = is_string($cookie) ? mb_strtolower($cookie) : '';
            $lang = in_array($code, $languages, true) ? $code : ($request->getPreferredLanguage($languages) ?? $languages[0]);
        }

        $prefix = $country === null ? $lang : $lang.'-'.$country;
        $qs = $request->server->get('QUERY_STRING', '');

        $redirect = redirect('/'.$prefix.'/'.$request->path().($qs === '' ? '' : '?'.$qs))
            ->withCookie(LocaleCookie::make('lang', $lang));

        if (count($countries) > 1 && $country !== null) {
            $redirect->withCookie(LocaleCookie::make('country', $country));
        }

        return $redirect;
    }
}
