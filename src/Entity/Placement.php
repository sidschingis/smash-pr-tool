<?php

namespace App\Entity;

use App\Repository\PlacementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlacementRepository::class)]
class Placement
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $playerId = 0,
        #[ORM\Id]
        #[ORM\Column]
        private int $eventId = 0,
        #[ORM\Column]
        private int $placement = 0,
        #[ORM\Column]
        private int $score = 0,
    ) {
    }

    public function getPlayerId(): int
    {
        return $this->playerId;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getPlacement(): int
    {
        return $this->placement;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }
}
