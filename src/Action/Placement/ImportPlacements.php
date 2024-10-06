<?php

namespace App\Action\Placement;

use App\Entity\Placement;
use App\Enum\Placement\Field;
use App\Objects\ImportPlacement;
use Doctrine\ORM\EntityManagerInterface;

class ImportPlacements
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ImportPlacement[] $importPlacements
     */
    public function importPlacements(array $importPlacements): bool
    {
        $entityManager = $this->entityManager;

        $newPlacements = $this->filterPlacements($importPlacements);

        foreach ($newPlacements as $entity) {
            $entityManager->persist($entity);
        }

        $entityManager->flush();

        return true;
    }

    /**
     * @param ImportPlacement[] $importPlacements
     * @return Placement[]
     */
    private function filterPlacements(array $importPlacements): array
    {
        $entityManager = $this->entityManager;

        $placements =[];
        foreach ($importPlacements as $importPlacement) {
            foreach ($importPlacement->placements as $placement) {
                $placements[] = $placement;
            }
        }

        $eventIds = [];
        $playerIds = [];
        foreach ($placements as $placement) {
            $eventIds[] = $placement->getEventId();
            $playerIds[] = $placement->getPlayerId();
        }

        $setRepo = $entityManager->getRepository(Placement::class);

        /** @var Placement[] */
        $existingPlacements = $setRepo->findBy(
            criteria:[
                Field::EVENT_ID->value => $eventIds,
                Field::PLAYER_ID->value => $playerIds,
            ],
        );

        $getId = function (Placement $placement): string {
            $eventId =  $placement->getEventId();
            $playerId  = $placement->getPlayerId();
            $id = "{$eventId}-{$playerId}";

            return $id;
        };

        $existingIds = [];
        foreach ($existingPlacements as $placement) {
            $id = $getId($placement);
            $existingIds[$id] = $id;
        }

        $newPlacements = [];

        foreach ($placements as $placement) {
            $id = $getId($placement);
            if (array_key_exists($id, $existingIds)) {
                continue;
            }

            $newPlacements[] = $placement;
        }

        return $newPlacements;
    }
}
