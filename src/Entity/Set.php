<?php

namespace App\Entity;

use App\Repository\SetRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity(repositoryClass: SetRepository::class)]
#[Index(name: 'by_winner', fields: ['winnerId', 'date'])]
#[Index(name: 'by_loser', fields: ['loserId', 'date'])]
#[Index(name: 'by_event', fields: ['eventId'])]
class Set
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private ?int $id = null,
        #[ORM\Column]
        private ?int $winnerId = null,
        #[ORM\Column]
        private ?int $loserId = null,
        #[ORM\Column(length: 100)]
        private ?string $displayScore = null,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private ?DateTimeInterface $date = null,
        #[ORM\Column(options:['default' => 0])]
        private int $eventId = 0,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): static
    {
        $this->eventId = $eventId;

        return $this;
    }

    public function getWinnerId(): ?int
    {
        return $this->winnerId;
    }

    public function setWinnerId(int $winnerId): static
    {
        $this->winnerId = $winnerId;

        return $this;
    }

    public function getDisplayScore(): ?string
    {
        return $this->displayScore;
    }

    public function setDisplayScore(string $displayScore): static
    {
        $this->displayScore = $displayScore;

        return $this;
    }

    public function getLoserId(): ?int
    {
        return $this->loserId;
    }

    public function setLoserId(int $loserId): static
    {
        $this->loserId = $loserId;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
