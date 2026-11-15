<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('returns the two-letter country from the cookie', function (?string $cookie, string $expected) {
    Route::get('/country', fn (): string => country() ?? '');

    $test = $cookie === null ? $this : $this->withUnencryptedCookies(['country' => $cookie]);

    $test->get('/country')->assertOk()->assertContent($expected);
})->with([
    ['it', 'it'],
    ['CH', 'ch'],
    [null, ''],
    ['italy', ''],
    ['i', ''],
]);
