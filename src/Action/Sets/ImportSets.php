<?php

namespace App\Action\Sets;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;

class ImportSets
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ImportSet[] $importSets
     */
    public function importSets(array $importSets): bool
    {
        $entityManager = $this->entityManager;

        $newSets = $this->filterSets($importSets);

        foreach ($newSets as $set) {
            $entityManager->persist($set);
        }

        $entityManager->flush();

        return true;
    }

    /**
     * @param ImportSet[] $importSets
     * @return Set[]
     */
    private function filterSets(array $importSets): array
    {
        $entityManager = $this->entityManager;

        $setIds = [];
        foreach ($importSets as $importSet) {
            $set = $importSet->set;
            $setIds[] = $set->getId();
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

        foreach ($importSets as $importSet) {
            $set = $importSet->set;
            $id = $set->getId();
            if (array_key_exists($id, $existingIds)) {
                continue;
            }

            $newSets[] = $set;
        }

        return $newSets;
    }
}
