<?php

namespace App\Entity;

use App\Repository\RankRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: RankRepository::class)]
#[UniqueConstraint(name: 'uniqueRank', fields: ['seasonId','rank'])]
#[Index(name: 'by_player', fields: ['playerId'])]
class Rank
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private ?int $id = null,
        #[ORM\Column]
        private ?int $seasonId = null,
        #[ORM\Column]
        private ?int $playerId = null,
        #[ORM\Column]
        private ?int $rank = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeasonId(): ?int
    {
        return $this->seasonId;
    }

    public function setSeasonId(int $seasonId): static
    {
        $this->seasonId = $seasonId;

        return $this;
    }

    public function getPlayerId(): ?int
    {
        return $this->playerId;
    }

    public function setPlayerId(int $playerId): static
    {
        $this->playerId = $playerId;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
