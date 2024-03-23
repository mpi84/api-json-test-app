<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Account;
use Symfony\Component\Validator\Constraints as Assert;

class AccountCreateContext implements ContextInterface
{
    #[Assert\Choice(choices: [Account::CURRENCY_EUR, Account::CURRENCY_USD, Account::CURRENCY_RUB])]
    #[Assert\NotBlank]
    public ?string $currency = null;

    #[Assert\Positive]
    #[Assert\NotBlank]
    public ?int $clientId = null;

    #[Assert\PositiveOrZero]
    public int $amount = 0;
}
