<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\AppUser;
use Symfony\Component\Validator\Constraints as Assert;

class UserCreateContext implements ContextInterface
{
    #[Assert\Email]
    #[Assert\NotBlank]
    public ?string $email = null;

    #[Assert\Length(min: 6, max: 12)]
    #[Assert\NotBlank]
    public ?string $password = null;

    #[Assert\Choice(choices: [AppUser::USER_ADMIN_ROLE, AppUser::USER_MANAGER_ROLE])]
    #[Assert\NotBlank]
    public ?string $role = null;
}
