<?php

namespace App\Controller;

use App\Forms\TournamentForm;
use App\Http\ApiRequest;
use App\Queries\Player\SetsForPlayer;
use App\Queries\Player\TournamentsForPlayer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractApiController extends AbstractApiController
{
    #[Route(
        '/import/player/{idPlayer}/sets',
        name: 'import_player_sets',
        requirements: ['idPlayer' => '\d+']
    )]
    public function playerSets(
        int $idPlayer
    ): Response {
        $httpRequest = Request::createFromGlobals();

        $queryParams =  $httpRequest->query;

        $tournamentIds = $queryParams->all()['tournamentIds'] ?? [];
        $startTime = $queryParams->getInt('startTime');

        $token = $this->getToken();

        $request = new ApiRequest();
        $query = new SetsForPlayer(
            playerId: $idPlayer,
            tournamentIds: $tournamentIds,
            startTimeStamp: $startTime,
        );

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        $sets = SetsForPlayer::JsonToSetData($response);

        return $this->render(
            'player/sets/setView.html.twig',
            [
                'action'     => $this->generateUrl('app_action_importSets'),
                'playerName' => $sets->playerName,
                'playerId' => $sets->playerId,
                'eventInfos' => $sets->eventInfos,
            ],
        );
    }

    #[Route('/import/{idPlayer}/tournaments', name: 'player_events', requirements: ['idPlayer' => '\d+'])]
    public function playerTournaments(int $idPlayer): Response
    {
        $token = $this->getToken();

        $request = new ApiRequest();
        $query = new TournamentsForPlayer(playerId: $idPlayer);

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        $tournamentData = TournamentsForPlayer::JsonToTournamentData($response);
        $tournaments = $tournamentData->tournaments;
        $debug = var_export($tournaments, true);

        $route = $this->generateUrl(
            TestController::ROUTE_PLAYER_SETS,
            parameters: [
                'idPlayer' => $idPlayer,
            ],
        );

        $choices = [];
        foreach ($tournaments as $tournament) {
            $id = $tournament->id;
            $label = "$tournament->name";

            $choices[$label] = $id;
        }

        $form = $this->createForm(
            TournamentForm::class,
            options: [
                'data' => [
                    'action' => $route,
                    'choices' => $choices,
                ],
            ],
        );

        return $this->render(
            'player/tournaments/formContainer.html.twig',
            [
                'debug' => $debug,
                'playerName' => $tournamentData->name,
                'tournaments' => $tournaments,
                'form' => $form,
                'route' => $route,
            ],
        );
    }
}
