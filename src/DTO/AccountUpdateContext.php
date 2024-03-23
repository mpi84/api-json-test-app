<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Account;
use Symfony\Component\Validator\Constraints as Assert;

class AccountUpdateContext implements ContextInterface
{
    #[Assert\Choice(choices: [Account::CURRENCY_EUR, Account::CURRENCY_USD, Account::CURRENCY_RUB])]
    public ?string $currency = null;

    #[Assert\PositiveOrZero]
    public ?int $amount = null;
}
