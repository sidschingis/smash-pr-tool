<?php

namespace App\Queries\Event;

use App\Enum\Event\Field as EventField;
use App\Enum\Season\Field as SeasonField;
use App\Objects\Ranking\ResultContainer;
use App\Util\QueryFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

class FetchEventsBySeason
{
    /**
     * @return ResultContainer[][]
     */
    public function getData(
        EntityManagerInterface $entityManager,
        int $seasonId,
    ): array {
        $sql = $this->getQuery(
            seasonId: $seasonId,
        );

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $entityManager->createNativeQuery($sql, $rsm);

        $eventIds = $query->getResult(Query::HYDRATE_SCALAR_COLUMN);

        return $eventIds;
    }

    private function getQuery(
        int $seasonId,
    ): string {
        $formatter = new QueryFormatter();
        $idEvent = $formatter->formatField(EventField::ID->value);
        $startDate = $formatter->formatField(SeasonField::START_DATE->value);
        $endDate = $formatter->formatField(SeasonField::END_DATE->value);
        $eventDate = EventField::DATE->value;
        return <<<EOD
            SELECT
                e.$idEvent id
            FROM event e
            JOIN season ON (season.id = $seasonId)
            WHERE e.$eventDate BETWEEN season.$startDate and season.$endDate
            ;
        EOD;
    }
}
