<?php

namespace App\Helpers;

final class DecimalMath
{
    public static function add(string $left, string $right, int $scale = 18): string
    {
        return bcadd($left, $right, $scale);
    }

    public static function subtract(string $left, string $right, int $scale = 18): string
    {
        return bcsub($left, $right, $scale);
    }

    public static function multiply(string $left, string $right, int $scale = 18): string
    {
        return bcmul($left, $right, $scale);
    }

    public static function divide(string $left, string $right, int $scale = 18): string
    {
        if (bccomp($right, '0', $scale) === 0) {
            throw new \InvalidArgumentException('Cannot divide by zero.');
        }

        return bcdiv($left, $right, $scale);
    }

    public static function compare(string $left, string $right, int $scale = 18): int
    {
        return bccomp($left, $right, $scale);
    }

    public static function scale(string $value, int $scale): string
    {
        return bcadd($value, '0', $scale);
    }
}