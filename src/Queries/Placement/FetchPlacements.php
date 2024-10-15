<?php

namespace App\Queries\Placement;

use App\Entity\Event;
use App\Entity\Placement;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Player\Field as PlayerField;
use App\Enum\Placement\Field as PlacementField;
use App\Enum\Event\Field as EventField;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;

class FetchPlacements
{
    /**
     * @return mixed[][]
     */
    public function getData(
        EntityManagerInterface $entityManager,
        int $playerFilter = 0,
        int $eventFilter = 0,
        int $site = 1,
        int $perSite = 20,
    ): array {
        $pId = PlayerField::ID->value;
        $tag = PlayerField::TAG->value;
        $playerId = PlacementField::PLAYER_ID->value;
        $pEventId = PlacementField::EVENT_ID->value;
        $placement = PlacementField::PLACEMENT->value;
        $score = PlacementField::SCORE->value;
        $eName = EventField::EVENT_NAME->value;
        $tName = EventField::TOURNAMENT_NAME->value;
        $entrants = EventField::ENTRANTS->value;
        $eEventId = EventField::ID->value;

        $qb = $entityManager->createQueryBuilder();
        $qb->select(
            "pl.{$tag}",
            "e.{$tName}",
            "e.{$eName}",
            "p.{$placement}",
            "e.{$entrants}",
            "p.{$score}",
        );

        $qb->from(Placement::class, 'p')
            ->join(Event::class, 'e', Join::WITH, "e.{$eEventId}=p.{$pEventId}")
            ->join(Player::class, 'pl', Join::WITH, "pl.{$pId}=p.{$playerId}");

        if ($playerFilter > 0) {
            $qb->andWhere("p.{$playerId}={$playerFilter}");
        }

        if ($eventFilter > 0) {
            $qb->andWhere("p.{$pEventId}={$eventFilter}");
        }

        $query = $qb
            ->setMaxResults($perSite)
            ->getQuery();

        $data = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $data;
    }

}
