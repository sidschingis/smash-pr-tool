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

class FetchContenders
{
    /**
     * @return mixed[][]
     */
    public function getData(
        EntityManagerInterface $entityManager,
        int $seasonFilter,
        string $regionFilter = Defaults::REGION,
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

        $query = $qb
            ->getQuery();

        $data = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $data;
    }


}
