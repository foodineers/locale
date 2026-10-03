# Locale

Laravel package for multicountry, multilanguage sites. URLs look like `/en-it/about` or `/en/about`. A `locale` middleware reads that prefix, and `LocaleRedirector` sends unprefixed requests to one.

## Installation

```bash
composer require foodineers/locale
php artisan vendor:publish --tag=locale-config
```

## Usage

`config/locale.php`:

```php
return [
    'countries' => ['it'], // ISO 3166-1 alpha-2. One entry is the default country.
    'languages' => ['en', 'it'],
];
```

Register prefixed routes before the redirector. The redirector appends `/{lang}` or `/{lang}-{country}` to the current path, so it must not match an already-prefixed URL.

```php
use Foodineers\Locale\Http\Controllers\LocaleRedirector;
use Illuminate\Support\Facades\Route;

Route::middleware('locale')
    ->prefix('{locale}')
    ->where(['locale' => '[a-z]{2}(-[a-z]{2})?'])
    ->group(function () {
        Route::get('/about', AboutController::class);
    });

Route::get('/{path?}', LocaleRedirector::class)
    ->where('path', '^(?![a-z]{2}(-[a-z]{2})?(/|$)).*');
```

The `locale` middleware alias is registered by the package. `app()->setLocale()` receives the language only.

## Agent skill

Apps that sync skills with [`llm/skills`](https://github.com/roxblnfk/skills) get `foodineers-locale`. The instructions live in [`resources/skills/foodineers-locale/SKILL.md`](resources/skills/foodineers-locale/SKILL.md).

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## License

MIT. See [LICENSE](LICENSE).
