<?php

namespace App\Controller;

use App\Http\LinkData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        $links = [
            new LinkData($this->generateUrl('app_crud_players'), 'Players'),
            new LinkData($this->generateUrl('app_ranking_season_crud'), 'Seasons'),
        ];

        return $this->render(
            'index.html.twig',
            [
                'links' => $links
            ],
        );
    }

}
