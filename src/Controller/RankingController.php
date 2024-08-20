<?php

namespace App\Controller;

use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    #[Route(
        '/ranking/{idPlayer}/winsLosses',
        name: 'app_ranking_winsLosses',
        requirements: ['idPlayer' => '\d+']
    )]
    public function winsLosses(
        Request $request,
        EntityManagerInterface $entityManager,
        int $idPlayer,
    ): Response {
        /** @var ?Player */
        $player = $entityManager->find(Player::class, $idPlayer);
        $playerTag = $player?->getTag() ?: "Unknown($idPlayer)";

        $wins = $this->fetchWins($entityManager, $idPlayer);

        $losses = $this->fetchLosses($entityManager, $idPlayer);

        return $this->render(
            'ranking/winsLosses.html.twig',
            [
                'playerTag' => $playerTag,
                'wins' => $wins,
                'losses' => $losses,
            ],
        );
    }

    /**
     * @return object[]
     */
    private function fetchWins(
        EntityManagerInterface $entityManager,
        int $idPlayer
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: 'winner_id',
            opponentColumn: 'loser_id',
            idPlayer: $idPlayer,
        );

        return $this->fetchSets($sql, $entityManager);
    }

    /**
     * @return object[]
     */
    private function fetchLosses(
        EntityManagerInterface $entityManager,
        int $idPlayer
    ): array {
        $sql = $this->getSetQuery(
            playerColumn: 'loser_id',
            opponentColumn: 'winner_id',
            idPlayer: $idPlayer,
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
        int $idPlayer
    ): string {
        return <<<EOD
            SELECT
                COUNT(1)
                ,s.$opponentColumn opponent_id
                ,COALESCE(op.tag,'') opponent_tag
            FROM "set" s
            LEFT JOIN player op ON (op.id = s.$opponentColumn)
            WHERE $playerColumn=$idPlayer
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
