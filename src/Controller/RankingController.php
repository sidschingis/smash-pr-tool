<?php

namespace App\Controller;

use App\Entity\Player;
use App\Queries\Ranking\FetchWinsLosses;
use Doctrine\ORM\EntityManagerInterface;
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

        $query = new FetchWinsLosses();
        [$wins, $losses] = $query->getData($entityManager, $idPlayer);

        return $this->render(
            'ranking/winsLosses.html.twig',
            [
                'playerTag' => $playerTag,
                'wins' => $wins,
                'losses' => $losses,
            ],
        );
    }

}
