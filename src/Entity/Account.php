<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helpers\TimestampableEntityHelper;
use App\Repository\AccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\UniqueConstraint(
    columns: ['client_id', 'currency']
)]
#[ORM\HasLifecycleCallbacks]
class Account implements EntityInterface
{
    use TimestampableEntityHelper;

    public const CURRENCY_USD = 'usd';
    public const CURRENCY_EUR = 'eur';
    public const CURRENCY_RUB = 'rub';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    #[ORM\Column(length: 3, nullable: false)]
    private string $currency;

    #[ORM\Column(type: Types::BIGINT, nullable: false)]
    private int $amount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAllowedCurrencies(): array
    {
        return [
            self::CURRENCY_EUR,
            self::CURRENCY_USD,
            self::CURRENCY_RUB,
        ];
    }

    public function toFilteredArray(): array
    {
        return [
            'id' => $this->getId(),
            'client' => $this->getClient()->getId(),
            'currency' => $this->getCurrency(),
            'amount' => $this->getAmount(),
            'createdAt' => $this->getFormattedCreatedAt(),
            'updatedAt' => $this->getFormattedUpdatedAt(),
        ];
    }
}
