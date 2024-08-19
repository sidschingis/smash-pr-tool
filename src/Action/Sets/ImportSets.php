<?php

namespace App\Action\Sets;

use App\ControllerData\SetData;
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

        foreach ($setData->eventInfos as $eventData) {
            $sets = $eventData->sets;

            foreach ($sets as $set) {
                $entityManager->persist($set);
            }
        }

        $entityManager->flush();

        return true;
    }
}
