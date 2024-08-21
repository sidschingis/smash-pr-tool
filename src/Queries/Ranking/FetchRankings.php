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
        int $idSeason,
    ): array {
        $sql = $this->getQuery(
            idSeason: $idSeason,
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
                'idPlayer' => $row['player_id'],
                'playerTag' => $row['player_tag'],
            ];
        }

        return $data;
    }

    private function getQuery(
        int $idSeason,
    ): string {
        return <<<EOD
            SELECT
                r.rank
                , player_id
                , COALESCE(p.tag,'') player_tag
            FROM "rank" r
            LEFT JOIN player p ON (p.id = r.player_id)
            WHERE r.season_id = $idSeason
            ORDER BY r.rank ASC
            ;
        EOD;
    }

}
