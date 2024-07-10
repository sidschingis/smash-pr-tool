<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AjaxActionController extends AbstractController
{
    #[Route('/ajax/importTournament', name: 'app_ajax_importTournament')]
    public function importTournament(Request $request): Response
    {
        $playerId = $request->request->getInt('playerId');
        $tournamentId = $request->request->getInt('tournamentId');

        $jsonData = '';

        return new JsonResponse($jsonData);
    }
}
