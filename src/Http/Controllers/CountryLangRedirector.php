<?php

declare(strict_types=1);

namespace Foodineers\Locale\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CountryLangRedirector
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        $country = null;

        if (count($countries) === 1) {
            $country = $countries[0];
        } elseif (count($countries) > 1) {
            $header = $request->header('CF-IPCountry');
            $code = is_string($header) ? mb_strtolower($header) : '';
            $country = in_array($code, $countries, true) ? $code : null;
        }

        /** @var list<string> $langs */
        $langs = config('locale.langs', []);

        if (count($langs) === 1) {
            $lang = $langs[0];
        } elseif ($langs === []) {
            $lang = explode('_', app()->getLocale())[0];
        } else {
            $lang = $request->getPreferredLanguage($langs) ?? $langs[0];
        }

        $prefix = $country === null ? $lang : $lang.'-'.$country;

        return redirect('/'.$prefix.'/'.$request->path());
    }
}
