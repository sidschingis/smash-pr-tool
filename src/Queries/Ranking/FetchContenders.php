<?php

namespace App\Queries\Ranking;

use App\Config\Defaults;
use App\Entity\Event;
use App\Entity\Placement;
use App\Entity\Player;
use App\Entity\Season;
use App\Enum\Event\Field as EventField;
use App\Enum\Placement\Field as PlacementField;
use App\Enum\Player\Field as PlayerField;
use App\Enum\Season\Field as SeasonField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class FetchContenders
{
    public function __construct(
        private string $regionFilter = Defaults::REGION,
        private int $minEvents = Defaults::MIN_EVENTS,
        private int $minRegionalEvents = Defaults::MIN_REGIONAL_EVENTS,
    ) {
    }

    /**
     * @return mixed[][]
     */
    public function getData(
        EntityManagerInterface $entityManager,
        int $seasonFilter,
    ): array {
        $pId = PlayerField::ID->value;
        $tag = PlayerField::TAG->value;
        $region = PlayerField::REGION->value;
        $seasonId = SeasonField::ID->value;
        $start = SeasonField::START_DATE->value;
        $end = SeasonField::END_DATE->value;
        $playerId = PlacementField::PLAYER_ID->value;
        $pEventId = PlacementField::EVENT_ID->value;
        $score = PlacementField::SCORE->value;
        $eDate = EventField::DATE->value;
        $eEventId = EventField::ID->value;
        $regionFilter = $this->regionFilter;

        $qb = $entityManager->createQueryBuilder();
        $qb->select(
            "pl.{$pId}",
            "pl.{$tag}",
            "AVG(p.{$score}) as avg",
        );

        $qb->from(Season::class, 's')
            ->join(
                Event::class,
                'e',
                Join::WITH,
                <<<EOD
                e.$eDate BETWEEN s.$start AND s.$end
                EOD
            )
            ->join(Placement::class, 'p', Join::WITH, "p.{$pEventId}=e.{$eEventId}")
            ->join(Player::class, 'pl', Join::WITH, "pl.{$pId}=p.{$playerId}");

        $qb->andWhere("s.{$seasonId}=:season")
            ->setParameter('season', $seasonFilter);
        $qb->andWhere("pl.{$region}=:region")
            ->setParameter('region', $regionFilter);

        $qb->groupBy("pl.{$pId}");
        $qb->orderBy('avg', 'DESC');

        $this->addMinEventCondition(
            $qb,
        );

        $query = $qb
            ->getQuery();

        $data = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $data;
    }

    private function addMinEventCondition(
        QueryBuilder $qb,
    ): void {
        $minRegional = $this->minRegionalEvents;
        $minEvents = $this->minEvents;

        $region = EventField::REGION->value;
        $eEventId = EventField::ID->value;

        $qb->addSelect(
            "SUM(CASE WHEN e.{$region} = :region THEN 1 ELSE 0 END) as regionalCount",
            "COUNT(e.{$eEventId}) as eventCount",
        );

        // $qb->andHaving(
        //     "regionalCount >= $minRegional",
        //     "eventCount >= $minEvents",
        // );
    }


}
