<?php

namespace App\Action\Sets;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;

class DeleteSets
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteSets(array $setIds): bool
    {
        $entityManager = $this->entityManager;

        foreach ($setIds as $id) {
            $set =   $entityManager->getReference(Set::class, $id);
            $entityManager->remove($set);
        }

        $entityManager->flush();

        return true;
    }
}
