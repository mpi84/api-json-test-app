<?php

declare(strict_types=1);

namespace App\Entity;

interface EntityInterface
{
    public function toFilteredArray(): array;
}
