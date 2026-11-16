<?php

declare(strict_types=1);

namespace Foodineers\Locale;

use Illuminate\Http\Request;

final class LocaleResolver
{
    public static function language(Request $request): string
    {
        /** @var list<string> $languages */
        $languages = config('locale.languages', []);

        if (count($languages) === 1) {
            return $languages[0];
        }

        if ($languages === []) {
            return explode('_', app()->getLocale())[0];
        }

        $cookie = $request->cookie('lang');
        $code = is_string($cookie) ? mb_strtolower($cookie) : '';

        return in_array($code, $languages, true) ? $code : ($request->getPreferredLanguage($languages) ?? $languages[0]);
    }

    public static function country(Request $request): ?string
    {
        /** @var list<string> $countries */
        $countries = config('locale.countries', []);

        if (count($countries) === 1) {
            return $countries[0];
        }

        if (count($countries) === 0) {
            return null;
        }

        foreach ([$request->cookie('country'), $request->header('CF-IPCountry')] as $raw) {
            $code = is_string($raw) ? mb_strtolower($raw) : '';
            if (in_array($code, $countries, true)) {
                return $code;
            }
        }

        return null;
    }
}
