<?php

declare(strict_types=1);

use Foodineers\Locale\Http\Controllers\CountryLangRedirector;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test', CountryLangRedirector::class);
});

it('redirects using country then language', function (array $config, string $locale, array $headers, string $target) {
    config($config);
    app()->setLocale($locale);

    $this->withHeaders($headers)->get('/test')->assertRedirect($target);
})->with([
    'one country' => [
        ['locale.countries' => ['it'], 'locale.langs' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-it/test',
    ],
    'cloudflare country' => [
        ['locale.countries' => ['it', 'de'], 'locale.langs' => ['en']],
        'en',
        ['CF-IPCountry' => 'DE'],
        '/en-de/test',
    ],
    'cloudflare country not allowed' => [
        ['locale.countries' => ['it', 'de'], 'locale.langs' => ['en']],
        'en',
        ['CF-IPCountry' => 'FR'],
        '/en/test',
    ],
    'no cloudflare header' => [
        ['locale.countries' => ['it', 'de'], 'locale.langs' => ['en']],
        'en',
        [],
        '/en/test',
    ],
    'no countries' => [
        ['locale.countries' => [], 'locale.langs' => ['en']],
        'en',
        ['CF-IPCountry' => 'IT'],
        '/en/test',
    ],
    'one language' => [
        ['locale.countries' => ['it'], 'locale.langs' => ['it']],
        'en',
        ['Accept-Language' => 'en,de;q=0.5'],
        '/it-it/test',
    ],
    'highest user language' => [
        ['locale.countries' => ['it'], 'locale.langs' => ['en', 'it', 'de']],
        'en',
        ['Accept-Language' => 'fr,it;q=0.8,en;q=0.2'],
        '/it-it/test',
    ],
    'first language when user matches none' => [
        ['locale.countries' => ['it'], 'locale.langs' => ['en', 'it']],
        'de',
        ['Accept-Language' => 'fr'],
        '/en-it/test',
    ],
    'app locale when no languages' => [
        ['locale.countries' => ['it'], 'locale.langs' => []],
        'de',
        ['Accept-Language' => 'fr'],
        '/de-it/test',
    ],
    'app locale drops region' => [
        ['locale.countries' => ['it'], 'locale.langs' => []],
        'en_CH',
        ['Accept-Language' => 'fr'],
        '/en-it/test',
    ],
]);
