<?php

namespace App\Action\Placement;

use App\Entity\Event;
use App\Entity\Placement;
use App\Enum\Event\Tier;
use App\Enum\Placement\Field;
use Doctrine\ORM\EntityManagerInterface;

class CalculateScores
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function calculate(int $eventId): void
    {
        $entityManager = $this->entityManager;
        $event = $this->fetchEvent($eventId);
        $entrants = $event->getEntrants();

        $placements = $this->fetchPlacements($eventId);

        $tier = $event->getTier();

        $multiplier = match($tier) {
            Tier::S => 2.5,
            Tier::A => 2,
            Tier::B => 1.5,
            Tier::C => 1,
            default => 1,
        };

        foreach($placements as $placementEntity) {
            $placement = $placementEntity->getPlacement();

            $score = $this->calculateScore(
                placement:$placement,
                tournamentSize: $entrants,
            ) * 10 * $multiplier;

            $placementEntity->setScore((int) $score);
            $entityManager->persist($placementEntity);
        }

        $entityManager->flush();
    }

    private function fetchEvent(int $eventId): Event
    {

        $repo = $this->entityManager->getRepository(Event::class);

        return $repo->find($eventId);
    }

    /**
     * @return Placement[]
     */
    private function fetchPlacements(int $eventId): array
    {

        $repo = $this->entityManager->getRepository(Placement::class);

        return $repo->findBy([
            Field::EVENT_ID->value => $eventId,
        ]);
    }

    private function calculateScore(int $placement, int $tournamentSize): float
    {
        $rounds = $this->getRounds($tournamentSize);
        $sizeBonus = $this->getSizeBonus($tournamentSize);
        $possiblePlacings  = [
            1,2,3,4,5,
            7,9,13,17,25,
            33,49,65,97,129,
            193,257,385,
        ];

        $roundsLeft = array_flip($possiblePlacings)[$placement] ?? 99;

        return $rounds - $roundsLeft + $sizeBonus - 1;
    }

    private function getRounds(int $tournamentSize): int
    {
        return ceil(2 * (log(num:$tournamentSize, base: 2) - 1)) + 2;
    }

    private function getSizeBonus(int $tournamentSize): float
    {
        return (log(num:$tournamentSize, base: 2) - 3) / 10;
    }
}
