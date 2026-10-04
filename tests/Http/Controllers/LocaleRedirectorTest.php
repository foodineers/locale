<?php

declare(strict_types=1);

use Foodineers\Locale\Http\Controllers\LocaleRedirector;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test', LocaleRedirector::class);
});

it('keeps the query string', function () {
    config(['locale.countries' => ['it'], 'locale.languages' => ['en']]);

    $this->get('/test?b=2&a=1')->assertRedirect('/en-it/test?b=2&a=1');
});

it('redirects using country then language', function (array $config, string $locale, array $headers, string $target, array $cookies = []) {
    config($config);
    app()->setLocale($locale);

    $response = $this->withHeaders($headers)->withUnencryptedCookies($cookies)->get('/test')->assertRedirect($target);

    [$lang, $country] = array_pad(explode('-', explode('/', mb_trim($target, '/'))[0], 2), 2, null);

    $response->assertPlainCookie('lang', $lang);

    if (count($config['locale.countries']) > 1 && $country !== null) {
        $response->assertPlainCookie('country', $country);
    } else {
        $response->assertCookieMissing('country');
    }
})->with([
    'one country' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-it/test',
    ],
    'cloudflare country' => [
        ['locale.countries' => ['it', 'de'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-de/test',
    ],
    'cloudflare country not allowed' => [
        ['locale.countries' => ['it', 'de'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'FR'],
        '/en/test',
    ],
    'no cloudflare header' => [
        ['locale.countries' => ['it', 'de'], 'locale.languages' => ['en']],
        'en',
        [],
        '/en/test',
    ],
    'no countries' => [
        ['locale.countries' => [], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'IT'],
        '/en/test',
    ],
    'one language' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['it']],
        'en',
        ['Accept-Language' => 'en,de;q=0.5'],
        '/it-it/test',
    ],
    'highest user language' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en', 'it', 'de']],
        'en',
        ['Accept-Language' => 'fr,it;q=0.8,en;q=0.2'],
        '/it-it/test',
    ],
    'first language when user matches none' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en', 'it']],
        'de',
        ['Accept-Language' => 'fr'],
        '/en-it/test',
    ],
    'app locale when no languages' => [
        ['locale.countries' => ['it'], 'locale.languages' => []],
        'de',
        ['Accept-Language' => 'fr'],
        '/de-it/test',
    ],
    'app locale drops region' => [
        ['locale.countries' => ['it'], 'locale.languages' => []],
        'en_CH',
        ['Accept-Language' => 'fr'],
        '/en-it/test',
    ],
    'country cookie beats cloudflare' => [
        ['locale.countries' => ['it', 'de'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-it/test',
        ['country' => 'it'],
    ],
    'country cookie not allowed falls through' => [
        ['locale.countries' => ['it', 'de'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-de/test',
        ['country' => 'fr'],
    ],
    'one country ignores cookie' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-it/test',
        ['country' => 'de'],
    ],
    'lang cookie beats accept-language' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en', 'it']],
        'en',
        ['Accept-Language' => 'en'],
        '/it-it/test',
        ['lang' => 'it'],
    ],
    'lang cookie not allowed falls through' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en', 'it']],
        'en',
        ['Accept-Language' => 'it'],
        '/it-it/test',
        ['lang' => 'fr'],
    ],
    'one language ignores cookie' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['en']],
        'en',
        ['Accept-Language' => 'it'],
        '/en-it/test',
        ['lang' => 'it'],
    ],
]);
