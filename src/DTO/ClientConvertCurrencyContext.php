<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Account;
use Symfony\Component\Validator\Constraints as Assert;

class ClientConvertCurrencyContext implements ContextInterface
{
    #[Assert\Positive]
    #[Assert\NotBlank]
    public ?int $clientId = null;

    #[Assert\Choice(choices: [Account::CURRENCY_EUR, Account::CURRENCY_USD, Account::CURRENCY_RUB])]
    #[Assert\NotBlank]
    #[Assert\Expression(
        'value !== this.currencyTo',
        message: 'Currencies cannot be same'
    )]
    public ?string $currencyFrom = null;

    #[Assert\Choice(choices: [Account::CURRENCY_EUR, Account::CURRENCY_USD, Account::CURRENCY_RUB])]
    #[Assert\NotBlank]
    #[Assert\Expression(
        'value !== this.currencyFrom',
        message: 'Currencies cannot be same'
    )]
    public ?string $currencyTo = null;

    #[Assert\Positive]
    public ?int $amount = null;
}
