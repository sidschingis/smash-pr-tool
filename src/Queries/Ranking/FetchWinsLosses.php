<?php

namespace App\Queries\Ranking;

use App\Enum\Player\Field as PlayerField;
use App\Enum\Set\Field as SetField;
use App\Enum\Event\Field as EventField;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Season\Field as SeasonField;
use App\Objects\Ranking\HeadToHead;
use App\Objects\Ranking\ResultContainer;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

class FetchWinsLosses
{
    /**
     * @return ResultContainer[][]
     */
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
     * @return ResultContainer[]
     */
    private function fetchWins(
        EntityManagerInterface $entityManager,
        int $seasonId,
        int $playerId,
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: $this->formatField(SetField::WINNER_ID->value),
            opponentColumn:$this->formatField(SetField::LOSER_ID->value),
            seasonId: $seasonId,
            playerId: $playerId,
        );

        return $this->fetchSets($sql, $entityManager);
    }

    /**
     * @return ResultContainer[]
     */
    private function fetchLosses(
        EntityManagerInterface $entityManager,
        int $seasonId,
        int $playerId,
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: $this->formatField(SetField::LOSER_ID->value),
            opponentColumn: $this->formatField(SetField::WINNER_ID->value),
            seasonId: $seasonId,
            playerId: $playerId,
        );

        return $this->fetchSets($sql, $entityManager);
    }

    /**
     * @return ResultContainer[]
     */
    private function fetchSets(
        string $sql,
        EntityManagerInterface $entityManager,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');
        $rsm->addScalarResult('opponent_id', 'opponent_id');
        $rsm->addScalarResult('opponent_tag', 'opponent_tag');
        $rsm->addScalarResult('tier', 'tier');

        $query = $entityManager->createNativeQuery($sql, $rsm);

        $rawData = $query->getResult(Query::HYDRATE_ARRAY);

        $data = [];

        foreach($rawData as $row) {
            [
                'count' => $count,
                'opponent_id' => $opponent_id,
                'opponent_tag' => $opponent_tag,
                'tier' => $tier,
            ] = $row;

            $name = $opponent_tag ?: "Unknown ({$opponent_id})";

            $data[$tier][] = new HeadToHead(
                $name,
                $count,
            );
        }

        $result = [];
        foreach ($data as $tier => $headToHeads) {
            $result[] = new ResultContainer(
                $tier,
                $headToHeads
            );
        }

        return $result;
    }

    private function getSetQuery(
        string $playerColumn,
        string $opponentColumn,
        int $seasonId,
        int $playerId,
    ): string {
        $pId = PlayerField::ID->value;
        $tag = PlayerField::TAG->value;
        $setDate = SetField::DATE->value;
        $sEventId = $this->formatField(SetField::EVENT_ID->value);
        $startDate = $this->formatField(SeasonField::START_DATE->value);
        $endDate = $this->formatField(SeasonField::END_DATE->value);
        $tier = EventField::TIER->value;
        $eEventId = EventField::ID->value;
        return <<<EOD
            SELECT
                COUNT(1)
                ,s.$opponentColumn opponent_id
                ,COALESCE(op.$tag,'') opponent_tag
                , e.$tier tier
            FROM set s
            JOIN season ON (season.id = $seasonId)
            LEFT JOIN player op ON (op.$pId = s.$opponentColumn)
            JOIN event e ON (e.$eEventId = s.$sEventId)
            WHERE $playerColumn=$playerId
            AND s.$setDate BETWEEN season.$startDate and season.$endDate
            GROUP BY s.$opponentColumn, op.$tag, e.$tier
            ;
        EOD;
    }

    private function formatField(string $input): string
    {
        return preg_replace('/[A-Z]/', '_$0', $input);
    }
}
