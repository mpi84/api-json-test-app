<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ClientCreateContext implements ContextInterface
{
    #[Assert\Email]
    #[Assert\NotBlank]
    public ?string $email = null;

    #[Assert\Positive]
    public ?int $managerId = null;
}
