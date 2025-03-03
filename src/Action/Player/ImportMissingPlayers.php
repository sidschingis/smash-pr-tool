<?php

namespace App\Action\Player;

use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;

class ImportMissingPlayers
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ImportSet[] $importSets
     */
    public function importPlayers(array $importSets): array
    {
        $entityManager = $this->entityManager;

        $playerData = $this->parsePlayers($importSets);

        $newPlayers = $this->filterPlayers($playerData);

        $importResult = [];

        foreach ($newPlayers as $player) {
            $entityManager->persist($player);

            $tag = $player->getTag();
            $id = $player->getId();
            $importResult[] = "{$tag} ({$id})";
        }

        $entityManager->flush();

        return $importResult;
    }


    /**
     * @param ImportSet[] $importSets
     * @return string[] <int,string>
     */
    private function parsePlayers(array $importSets): array
    {
        $playerData = [];
        foreach($importSets as $importSet) {
            $playerData += [
                $importSet->set->getWinnerId() => $importSet->winnerTag,
                $importSet->set->getLoserId() => $importSet->loserTag,
            ];
        }

        return $playerData;
    }

    /**
     * @param string[] $playerData <int,string>
     * @return Player[]
     */
    private function filterPlayers(array $playerData): array
    {
        $entityManager = $this->entityManager;

        $playerIds = array_keys($playerData);

        $playerRepo = $entityManager->getRepository(Player::class);

        /** @var Player[] */
        $existingPlayers = $playerRepo->findBy(
            criteria:[
                'id' => $playerIds,
            ],
        );

        $existingIds = [];
        foreach ($existingPlayers as $existing) {
            $id = $existing->getId();
            $existingIds[$id] = $id;
        }

        $newPlayers = [];

        foreach ($playerData as $playerId => $tag) {
            if (array_key_exists($playerId, $existingIds)) {
                continue;
            }

            $newPlayers[] = new Player(
                id: $playerId,
                tag: $tag,
            );
        }

        return $newPlayers;
    }
}
