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
        int $idSeason,
        array $ranking,
    ): void {
        $entityManager = $this->entityManager;

        $rankRepo = $entityManager->getRepository(Rank::class);

        /** @var Rank[] */
        $existingRankings = $rankRepo->findBy(
            criteria:[
                'seasonId' => $idSeason,
            ],
        );

        foreach ($existingRankings as $existingRank) {
            $rank = $existingRank->getRank();
            $newPlayer = $ranking[$rank];
            $existingRank->setPlayerId($newPlayer);

            unset($ranking[$rank]);
        }

        foreach ($ranking as $rank => $idPlayer) {
            $newRank = new Rank(
                seasonId:$idSeason,
                playerId: $idPlayer,
                rank: $rank,
            );

            $entityManager->persist($newRank);
        }

        $entityManager->flush();
    }

}
