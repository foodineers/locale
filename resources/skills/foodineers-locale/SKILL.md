---
name: foodineers-locale
description: >-
  Wires foodineers/locale for /{lang} and /{lang}-{country} URLs: config
  locale.countries and locale.languages, the locale middleware, and
  LocaleRedirector. Use when adding or changing country or language prefixes,
  lang or country cookies, SetLocale, SetLocaleFromUrl, SetLocaleFromRequest, LocaleRedirector, CF-IPCountry,
  Accept-Language, or config/locale.php in an app that requires foodineers/locale.
---

# foodineers/locale

Prefix is `{lang}` or `{lang}-{country}`, one hyphen, lowercase. `en-it` is English for Italy. `zh-hans` is not a valid prefix.

There is no `Foodineers\Locale` class, no migrations, and no views. Publish only `locale-config`.

## Wire-up

1. Set `config/locale.php`. `countries` is ISO 3166-1 alpha-2. One country is always used. `languages` is the allow-list; the first entry is the fallback.
2. Put `locale.url`, `locale.request`, and `locale` on routes whose first segment is that prefix, in that order. The aliases are already registered.
3. Point unprefixed routes at `Foodineers\Locale\Http\Controllers\LocaleRedirector`. Register those routes after the prefixed ones.

```php
use Foodineers\Locale\Http\Controllers\LocaleRedirector;
use Illuminate\Support\Facades\Route;

Route::middleware(['locale.url', 'locale.request', 'locale'])
    ->prefix('{locale}')
    ->where(['locale' => '[a-z]{2}(-[a-z]{2})?'])
    ->group(function () {
        Route::get('/about', AboutController::class);
    });

Route::get('/{path?}', LocaleRedirector::class)
    ->where('path', '^(?![a-z]{2}(-[a-z]{2})?(/|$)).*');
```

The `where` keeps `/en-it/about` off the redirector. Without it the redirector nests the prefix (`/en-it/en-it/about`). Do not put `locale` middleware on the redirector route.

`/` redirects to `/{prefix}//`. Browsers collapse that to `/{prefix}/`. The query string is kept, in the original order.

## Resolution

Prefixed routes run three middlewares, in order. Do not put them on the redirector route.

| Middleware | Alias |
| --- | --- |
| `SetLocaleFromUrl` | `locale.url` |
| `SetLocaleFromRequest` | `locale.request` |
| `SetLocale` | `locale` |

`SetLocaleFromUrl` reads `{lang}` or `{lang}-{country}`. A country in the prefix must be configured, or the response is 404. A prefix with no country is allowed. An allowed prefix language overwrites the `lang` cookie. A prefix country overwrites the `country` cookie. A prefix language that is not allowed does not change the `lang` cookie.

`SetLocaleFromRequest` uses the same choice as `LocaleRedirector`. It writes a cookie only when that choice differs from the cookie already on the request, and it writes `country` only when more than one country is configured and one was chosen. That is what fills a missing cookie from `CF-IPCountry` or `Accept-Language`.

`SetLocale` reads the `lang` cookie and calls `app()->setLocale()`. The language only, never `lang-country`. An allowed cookie wins. Otherwise the first configured language, or the app locale with the region stripped.

`LocaleRedirector` picks the target. It does not set the app locale.

| | Country | Language |
| --- | --- | --- |
| One configured | That one. Cookie and `CF-IPCountry` are ignored. | That one. Cookie and `Accept-Language` are ignored. |
| Several | Cookie `country` if allowed, else `CF-IPCountry` if allowed, else omit it from the prefix. | Cookie `lang` if allowed, else `Accept-Language` via `getPreferredLanguage()`, else the first configured language. |
| None | Omit it. | `app()->getLocale()` with the region stripped (`de_CH` → `de`). |

The redirector always queues a one-year `lang` cookie. It queues `country` only when more than one country is configured and one was chosen.

Cookies `lang` and `country` last one year and copy `session.path`, `session.domain`, `session.secure`, `session.http_only`, `session.same_site`, and `session.partitioned`. Read them with `$request->cookie()`, not `$_COOKIE`.

`country()` returns that cookie as two lowercase letters, or null when it is missing or not two letters.
