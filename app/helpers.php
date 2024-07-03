<?php

if (!function_exists('numbersOnly')) {
    function numbersOnly($string): string|null
    {
        if (is_null($string)) {
            return null;
        }

        return preg_replace('([^0-9])', '', $string);
    }
}

if (!function_exists('formatDecimals')) {
    function formatDecimals(string $value, ?int $decimals = null): string|null
    {
        if (is_null($decimals)) {
            $decimals = 2;
        }

        return intval(number_format($value, $decimals) * (10 ** $decimals));
    }
}
