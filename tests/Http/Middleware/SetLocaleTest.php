<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('locale')->get('/{prefix}/page', fn () => app()->getLocale());
});

it('sets locale from allowed cookies or the url prefix', function (array $config, string $appLocale, string $url, int $status, string $locale, array $requestCookies, array $responseCookies) {
    config($config);
    app()->setLocale($appLocale);

    $response = $this->withUnencryptedCookies($requestCookies)->get($url);

    $response->assertStatus($status);

    if ($status === 200) {
        $response->assertContent($locale);
    }

    foreach (['lang', 'country'] as $name) {
        if (array_key_exists($name, $responseCookies)) {
            $response->assertPlainCookie($name, $responseCookies[$name]);
        } else {
            $response->assertCookieMissing($name);
        }
    }
})->with([
    'url sets cookies and locale' => [
        ['locale.countries' => ['ch', 'it'], 'locale.languages' => ['fr', 'en']],
        'en',
        '/fr-ch/page',
        200,
        'fr',
        [],
        ['lang' => 'fr', 'country' => 'ch'],
    ],
    'country not allowed' => [
        ['locale.countries' => ['it'], 'locale.languages' => ['fr']],
        'en',
        '/fr-ch/page',
        404,
        '',
        [],
        [],
    ],
    'lang not allowed picks first' => [
        ['locale.countries' => ['ch'], 'locale.languages' => ['en', 'it']],
        'it',
        '/fr-ch/page',
        200,
        'en',
        [],
        ['lang' => 'en', 'country' => 'ch'],
    ],
    'no languages uses app locale' => [
        ['locale.countries' => ['ch'], 'locale.languages' => []],
        'de_CH',
        '/fr-ch/page',
        200,
        'de',
        [],
        ['lang' => 'de', 'country' => 'ch'],
    ],
    'allowed cookies win' => [
        ['locale.countries' => ['ch', 'it'], 'locale.languages' => ['fr', 'it']],
        'en',
        '/fr-ch/page',
        200,
        'it',
        ['lang' => 'it', 'country' => 'it'],
        [],
    ],
    'invalid cookies take url' => [
        ['locale.countries' => ['ch'], 'locale.languages' => ['fr']],
        'en',
        '/fr-ch/page',
        200,
        'fr',
        ['lang' => 'de', 'country' => 'it'],
        ['lang' => 'fr', 'country' => 'ch'],
    ],
    'lang only url' => [
        ['locale.countries' => [], 'locale.languages' => ['fr']],
        'en',
        '/fr/page',
        200,
        'fr',
        [],
        ['lang' => 'fr'],
    ],
    'missing country is kept' => [
        ['locale.countries' => ['ch', 'it'], 'locale.languages' => ['fr']],
        'en',
        '/fr/page',
        200,
        'fr',
        [],
        ['lang' => 'fr'],
    ],
    'uppercase url' => [
        ['locale.countries' => ['ch'], 'locale.languages' => ['fr']],
        'en',
        '/FR-CH/page',
        200,
        'fr',
        [],
        ['lang' => 'fr', 'country' => 'ch'],
    ],
    'cookie lang kept when url lang not allowed' => [
        ['locale.countries' => ['ch'], 'locale.languages' => ['en', 'it']],
        'en',
        '/fr-ch/page',
        200,
        'it',
        ['lang' => 'it'],
        ['country' => 'ch'],
    ],
]);

it('copies session cookie settings onto locale cookies', function () {
    config([
        'locale.countries' => ['ch'],
        'locale.languages' => ['fr'],
        'session.path' => '/app',
        'session.domain' => '.example.test',
        'session.secure' => true,
        'session.http_only' => false,
        'session.same_site' => 'strict',
        'session.partitioned' => true,
    ]);

    $response = $this->get('/fr-ch/page');

    foreach (['lang', 'country'] as $name) {
        $cookie = $response->getCookie($name, false);

        expect($cookie)->not->toBeNull()
            ->and($cookie?->getPath())->toBe('/app')
            ->and($cookie?->getDomain())->toBe('.example.test')
            ->and($cookie?->isSecure())->toBeTrue()
            ->and($cookie?->isHttpOnly())->toBeFalse()
            ->and($cookie?->getSameSite())->toBe('strict')
            ->and($cookie?->isPartitioned())->toBeTrue();
    }
});
