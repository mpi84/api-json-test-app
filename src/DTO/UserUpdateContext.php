<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\AppUser;
use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateContext implements ContextInterface
{
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Length(min: 6, max: 12)]
    public ?string $password = null;

    #[Assert\Choice(choices: [AppUser::USER_ADMIN_ROLE, AppUser::USER_MANAGER_ROLE])]
    public ?string $role = null;
}
