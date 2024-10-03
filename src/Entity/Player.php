<?php

namespace App\Entity;

use App\Enum\Player\Field;
use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[Index(name: 'search_tag', fields: [Field::TAG->value])]
#[Index(name: 'search_region', fields: [Field::REGION->value])]
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
        #[ORM\Column(length: 50, options:['default' => ''])]
        private ?string $region = '',
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }
}
