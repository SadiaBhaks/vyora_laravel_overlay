<?php

namespace App\Support;

/** Integer-cents helpers so money maths never touches floats beyond the boundary. */
final class Money
{
    public static function toCents(string|int|float|null $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    public static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function format(int $cents): string
    {
        return config('vyora.currency_symbol').number_format($cents / 100, 2);
    }
}
