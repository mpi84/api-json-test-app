<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ClientUpdateContext implements ContextInterface
{
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Positive]
    public ?int $managerId = null;
}
