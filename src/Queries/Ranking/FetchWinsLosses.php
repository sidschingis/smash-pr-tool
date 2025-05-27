<?php

namespace App\Queries\Ranking;

use App\Enum\Event\Field as EventField;
use App\Enum\Placement\Field as PlacementField;
use App\Enum\Player\Field as PlayerField;
use App\Enum\Season\Field as SeasonField;
use App\Enum\Set\Field as SetField;
use App\Objects\Ranking\HeadToHead;
use App\Objects\Ranking\Placement;
use App\Objects\Ranking\PlacementContainer;
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

        $placementQuery = $this->getPlacingQuery(
            seasonId: $seasonId,
            playerId: $playerId,
        );
        $placements = $this->fetchPlacings(
            sql: $placementQuery,
            entityManager: $entityManager,
        );

        return [$wins, $losses,$placements];
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
            $result[$tier] = new ResultContainer(
                $tier,
                $headToHeads
            );
        }

        $this->tierSort($result);

        return $result;
    }

    private function tierSort(array &$input): void
    {
        $tierToOrder = fn ($tier) =>  match($tier) {
            'S' => 0,
            'A' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4,
        };

        uksort($input, function (mixed $keyA, mixed $keyB) use ($tierToOrder): int {
            return $tierToOrder($keyA) <=> $tierToOrder($keyB) ;
        });
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

    private function fetchPlacings(
        string $sql,
        EntityManagerInterface $entityManager,
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('placement', 'placement');
        $rsm->addScalarResult('score', 'score');
        $rsm->addScalarResult('tier', 'tier');

        $query = $entityManager->createNativeQuery($sql, $rsm);

        $rawData = $query->getResult(Query::HYDRATE_ARRAY);
        $data = [];

        foreach($rawData as $row) {
            [
                'name' => $name,
                'placement' => $placement,
                'score' => $score,
                'tier' => $tier,
            ] = $row;

            $data[$tier][] = new Placement(
                name: $name,
                placement: $placement,
                score: $score,
                tier: $tier
            );
        }

        $this->tierSort($data);

        $result = [];
        foreach ($data as $tier => $placements) {
            $result[] = new PlacementContainer(
                tier: $tier,
                placements: $placements
            );
        }

        return $result;
    }

    private function getPlacingQuery(
        int $seasonId,
        int $playerId,
    ): string {
        $formatter = new QueryFormatter();
        $selfId = $formatter->formatField(PlacementField::PLAYER_ID->value);
        $pEventId = $formatter->formatField(PlacementField::EVENT_ID->value);
        $startDate = $formatter->formatField(SeasonField::START_DATE->value);
        $endDate = $formatter->formatField(SeasonField::END_DATE->value);
        $tournamentName = $formatter->formatField(EventField::TOURNAMENT_NAME->value);
        $placement = PlacementField::PLACEMENT->value;
        $score = PlacementField::SCORE->value;
        $eventDate = EventField::DATE->value;
        $tier = EventField::TIER->value;
        $eEventId = EventField::ID->value;
        return <<<EOD
            SELECT
                e.$tournamentName name
                , p.$placement placement
                , p.$score score
                , e.$tier tier
            FROM placement p
            JOIN season ON (season.id = $seasonId)
            JOIN event e ON (e.$eEventId = p.$pEventId)
            WHERE $selfId = $playerId
            AND e.$eventDate BETWEEN season.$startDate and season.$endDate
            ;
        EOD;
    }
}
