<?php

namespace App\Action\Ranking;

use App\Entity\Rank;
use Doctrine\ORM\EntityManagerInterface;

class UpdateRankings
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateRankings(
        int $seasonId,
        array $ranking,
    ): void {
        $entityManager = $this->entityManager;

        $rankRepo = $entityManager->getRepository(Rank::class);

        /** @var Rank[] */
        $existingRankings = $rankRepo->findBy(
            criteria:[
                'seasonId' => $seasonId,
            ],
        );

        foreach ($existingRankings as $existingRank) {
            $rank = $existingRank->getRank();
            $newPlayer = $ranking[$rank];
            $existingRank->setPlayerId($newPlayer);

            unset($ranking[$rank]);
        }

        foreach ($ranking as $rank => $playerId) {
            $newRank = new Rank(
                seasonId: $seasonId,
                playerId: $playerId,
                rank: $rank,
            );

            $entityManager->persist($newRank);
        }

        $entityManager->flush();
    }

}
