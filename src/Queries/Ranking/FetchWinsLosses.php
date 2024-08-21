<?php

namespace App\Queries\Ranking;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

class FetchWinsLosses
{
    public function getData(
        EntityManagerInterface $entityManager,
        int $seasonId,
        int $playerId,
    ): array {
        $wins = $this->fetchWins(
            entityManager: $entityManager,
            seasonId: $seasonId,
            playerId: $playerId,
        );

        $losses = $this->fetchLosses(
            entityManager: $entityManager,
            seasonId: $seasonId,
            playerId: $playerId,
        );

        return [$wins, $losses];
    }

    /**
     * @return object[]
     */
    private function fetchWins(
        EntityManagerInterface $entityManager,
        int $seasonId,
        int $playerId,
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: 'winner_id',
            opponentColumn: 'loser_id',
            seasonId: $seasonId,
            playerId: $playerId,
        );

        return $this->fetchSets($sql, $entityManager);
    }

    /**
     * @return object[]
     */
    private function fetchLosses(
        EntityManagerInterface $entityManager,
        int $seasonId,
        int $playerId,
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: 'loser_id',
            opponentColumn: 'winner_id',
            seasonId: $seasonId,
            playerId: $playerId,
        );

        return $this->fetchSets($sql, $entityManager);
    }

    /**
     * @return object[]
     */
    private function fetchSets(
        string $sql,
        EntityManagerInterface $entityManager,
    ): array {

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');
        $rsm->addScalarResult('opponent_id', 'opponent_id');
        $rsm->addScalarResult('opponent_tag', 'opponent_tag');

        $query = $entityManager->createNativeQuery($sql, $rsm);

        $rawData = $query->getResult(Query::HYDRATE_ARRAY);

        $data = [];

        foreach($rawData as $row) {
            [
                'count' => $count,
                'opponent_id' => $opponent_id,
                'opponent_tag' => $opponent_tag,
            ] = $row;

            $name = $opponent_tag ?: "Unknown ({$opponent_id})";

            $data[] = $this->buildOpponent(
                $name,
                $count,
            );
        }

        return $data;
    }

    private function getSetQuery(
        string $playerColumn,
        string $opponentColumn,
        int $seasonId,
        int $playerId,
    ): string {
        return <<<EOD
            SELECT
                COUNT(1)
                ,s.$opponentColumn opponent_id
                ,COALESCE(op.tag,'') opponent_tag
            FROM "set" s
            JOIN season ON (season.id = $seasonId)
            LEFT JOIN player op ON (op.id = s.$opponentColumn)
            WHERE $playerColumn=$playerId
            AND s.date BETWEEN season.start_date and season.end_date
            GROUP BY s.$opponentColumn, op.tag
            ;
        EOD;
    }

    private function buildOpponent(...$args): object
    {
        return new class (...$args) {
            public function __construct(
                public string $name,
                public int $count,
            ) {
            }
        };
    }
}
