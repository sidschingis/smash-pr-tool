<?php

namespace App\Queries\Ranking;

use App\Enum\Event\Field as EventField;
use App\Enum\Player\Field as PlayerField;
use App\Enum\Season\Field as SeasonField;
use App\Enum\Set\Field as SetField;
use App\Objects\Ranking\HeadToHead;
use App\Objects\Ranking\ResultContainer;
use App\Util\QueryFormatter;
use Doctrine\ORM\EntityManagerInterface;
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
            playerColumn: SetField::WINNER_ID->value,
            opponentColumn:SetField::LOSER_ID->value,
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
            playerColumn: SetField::LOSER_ID->value,
            opponentColumn: SetField::WINNER_ID->value,
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
        $formatter = new QueryFormatter();
        $pId = PlayerField::ID->value;
        $tag = PlayerField::TAG->value;
        $setDate = SetField::DATE->value;
        $selfId = $formatter->formatField($playerColumn);
        $otherId = $formatter->formatField($opponentColumn);
        $sEventId = $formatter->formatField(SetField::EVENT_ID->value);
        $startDate = $formatter->formatField(SeasonField::START_DATE->value);
        $endDate = $formatter->formatField(SeasonField::END_DATE->value);
        $tier = EventField::TIER->value;
        $eEventId = EventField::ID->value;
        return <<<EOD
            SELECT
                COUNT(1)
                ,s.$otherId opponent_id
                ,COALESCE(op.$tag,'') opponent_tag
                , e.$tier tier
            FROM set s
            JOIN season ON (season.id = $seasonId)
            LEFT JOIN player op ON (op.$pId = s.$otherId)
            JOIN event e ON (e.$eEventId = s.$sEventId)
            WHERE $selfId=$playerId
            AND s.$setDate BETWEEN season.$startDate and season.$endDate
            GROUP BY s.$otherId, op.$tag, e.$tier
            ORDER BY e.$tier DESC
            ;
        EOD;
    }
}
