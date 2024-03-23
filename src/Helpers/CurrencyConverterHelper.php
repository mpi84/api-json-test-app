<?php

declare(strict_types=1);

namespace App\Helpers;

trait CurrencyConverterHelper
{
    public function convertBetweenTwoCurrencies(string $currencyFrom, string $currencyTo, int $amount): int
    {
        $rates = $this->getConvertCurrencyRates();

        return (int) round($amount * $rates[$currencyFrom][$currencyTo]);
    }

    public function getConvertCurrencyRates(): array
    {
        return [
            'usd' => [
                'rub' => 90,
                'eur' => 0.8,
            ],
            'eur' => [
                'rub' => 110,
                'usd' => 1.2,
            ],
            'rub' => [
                'eur' => 0.0091,
                'usd' => 0.011,
            ],
        ];
    }
}
