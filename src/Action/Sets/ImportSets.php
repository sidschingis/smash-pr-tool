<?php

namespace App\Action\Sets;

use App\ControllerData\SetData;
use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;

class ImportSets
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function importSets(SetData $setData): bool
    {
        $entityManager = $this->entityManager;

        $newSets = $this->filterSets($setData);

        foreach ($newSets as $set) {
            $entityManager->persist($set);
        }

        $entityManager->flush();

        return true;
    }

    /**
     * @return Set[]
     */
    private function filterSets(SetData $setData): array
    {
        $entityManager = $this->entityManager;

        $setIds = [];
        foreach ($setData->eventInfos as $eventData) {
            $sets = $eventData->sets;

            foreach ($sets as $importSet) {
                $set = $importSet->set;
                $setIds[] = $set->getId();
            }
        }

        $setRepo = $entityManager->getRepository(Set::class);

        /** @var Set[] */
        $existingSets = $setRepo->findBy(
            criteria:[
                'id' => $setIds,
            ],
        );

        $existingIds = [];
        foreach ($existingSets as $existing) {
            $id = $existing->getId();
            $existingIds[$id] = $id;
        }

        $newSets = [];

        foreach ($setData->eventInfos as $eventData) {
            $sets = $eventData->sets;

            foreach ($sets as $importSet) {
                $set = $importSet->set;
                $id = $set->getId();
                if (array_key_exists($id, $existingIds)) {
                    continue;
                }

                $newSets[] = $set;
            }
        }

        return $newSets;
    }
}
