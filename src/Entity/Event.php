<?php

namespace App\Entity;

use App\Enum\Event\Tier;
use App\Repository\EventRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id = 0,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private ?DateTimeInterface $date = null,
        #[ORM\Column(length: 100)]
        private string $eventName = '',
        #[ORM\Column(length: 100)]
        private string $tournamentName = '',
        #[ORM\Column]
        private int $entrants = 0,
        #[ORM\Column]
        private int $notables = 0,
        #[ORM\Column(type: 'string', enumType: Tier::class)]
        private Tier $tier = Tier::NONE,
        #[ORM\Column(length: 10, options:['default' => ''])]
        private string $region = '',
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;
        return $this;
    }
}
