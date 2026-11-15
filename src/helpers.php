<?php

declare(strict_types=1);

if (! function_exists('country')) {
    function country(): ?string
    {
        $value = request()->cookie('country');

        return is_string($value) && preg_match('/^[a-z]{2}$/i', $value) === 1
            ? mb_strtolower($value)
            : null;
    }
}
