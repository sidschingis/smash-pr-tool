<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private ?int $id = 0,
        #[ORM\Column(length: 20)]
        private ?string $tag = '',
        #[ORM\Column(length: 50)]
        private ?string $twitterTag = '',
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

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTwitterTag(): ?string
    {
        return $this->twitterTag;
    }

    public function setTwitterTag(string $twitterTag): static
    {
        $this->twitterTag = $twitterTag;

        return $this;
    }
}
