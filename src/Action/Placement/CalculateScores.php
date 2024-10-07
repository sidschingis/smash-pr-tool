<?php

namespace App\Action\Placement;

use App\Entity\Event;
use App\Entity\Placement;
use App\Enum\Placement\Field;
use Doctrine\ORM\EntityManagerInterface;

class CalculateScores
{
    private const float PLACEMENT_BIAS = 3.0;
    private const float PLACEMENT_BASE = 2.0;
    private const float TOURNAMENT_BIAS = 0.5;
    private const float TOURNAMENT_BASE = 2.0;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function calculate(int $eventId): void
    {
        $entityManager = $this->entityManager;
        $event = $this->fetchEvent($eventId);
        $entrants = $event->getEntrants();

        $tournamentScore = $this->getTournamentScore($entrants);

        $placements = $this->fetchPlacements($eventId);

        foreach($placements as $placementEntity) {
            $placement = $placementEntity->getPlacement();
            $placementScore = $this->getPlacementScore($placement);

            $score = $this->calculateScore(
                $placementScore,
                $tournamentScore,
            );

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

    private function calculateScore(float $placementScore, float $tournamentScore): float
    {
        return $placementScore * $tournamentScore * 10;
    }

    private function getPlacementScore(int $placement): float
    {
        $log = log($placement, static::PLACEMENT_BASE);
        return 1 / ($log + static::PLACEMENT_BIAS) ;
    }

    private function getTournamentScore(int $tournamentSize): float
    {
        $log = log($tournamentSize, static::TOURNAMENT_BASE);
        return $log + pow(1 + static::TOURNAMENT_BIAS, $log);
    }
}
