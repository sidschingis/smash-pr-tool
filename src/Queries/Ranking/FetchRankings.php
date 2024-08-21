<?php

namespace App\Queries\Ranking;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

class FetchRankings
{
    /**
     * @return mixed[][]
     */
    public function getData(
        EntityManagerInterface $entityManager,
        int $seasonId,
    ): array {
        $sql = $this->getQuery(
            seasonId: $seasonId,
        );

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('rank', 'rank');
        $rsm->addScalarResult('player_id', 'player_id');
        $rsm->addScalarResult('player_tag', 'player_tag');

        $query = $entityManager->createNativeQuery($sql, $rsm);

        $rawData = $query->getResult(Query::HYDRATE_ARRAY);

        $data = [];

        foreach($rawData as $row) {
            $rank = $row['rank'];
            $data[$rank] = [
                'playerId' => $row['player_id'],
                'playerTag' => $row['player_tag'],
            ];
        }

        return $data;
    }

    private function getQuery(
        int $seasonId,
    ): string {
        return <<<EOD
            SELECT
                r.rank
                , player_id
                , COALESCE(p.tag,'') player_tag
            FROM "rank" r
            LEFT JOIN player p ON (p.id = r.player_id)
            WHERE r.season_id = $seasonId
            ORDER BY r.rank ASC
            ;
        EOD;
    }

}
