<?php

namespace App\Controller;

use App\Forms\TournamentForm;
use App\Queries\Player\SetsForPlayer;
use App\Queries\Player\TournamentsForPlayer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractApiController
{
    public const ROUTE_IMPORT_SETS = 'app_import_player_sets';

    #[Route(
        '/import/player/{idPlayer}/sets',
        name: ImportController::ROUTE_IMPORT_SETS,
        requirements: ['idPlayer' => '\d+']
    )]
    public function playerSets(
        int $idPlayer
    ): Response {
        $httpRequest = Request::createFromGlobals();

        $queryParams =  $httpRequest->query;

        $tournamentIds = $queryParams->all()['tournamentIds'] ?? [];
        $startTime = $queryParams->getInt('startTime');

        $query = new SetsForPlayer(
            playerId: $idPlayer,
            tournamentIds: $tournamentIds,
            startTimeStamp: $startTime,
        );

        $response = $this->sendRequest($query);

        $sets = SetsForPlayer::JsonToSetData($response);

        return $this->render(
            'import/player/sets/setView.html.twig',
            [
                'action'     => $this->generateUrl('app_action_importSets'),
                'playerTag' => $sets->playerTag,
                'playerId' => $sets->playerId,
                'eventInfos' => $sets->eventInfos,
            ],
        );
    }

    #[Route(
        '/import/player/{idPlayer}/events',
        name: 'app_import_player_events',
        requirements: ['idPlayer' => '\d+']
    )]
    public function playerTournaments(int $idPlayer): Response
    {
        $query = new TournamentsForPlayer(playerId: $idPlayer);

        $response = $this->sendRequest($query);

        $tournamentData = TournamentsForPlayer::JsonToTournamentData($response);
        $tournaments = $tournamentData->tournaments;
        $debug = var_export($tournaments, true);

        $route = $this->generateUrl(
            ImportController::ROUTE_IMPORT_SETS,
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
            'import/player/tournaments/tournamentSelect.html.twig',
            [
                'debug' => $debug,
                'playerTag' => $tournamentData->name,
                'tournaments' => $tournaments,
                'form' => $form,
                'route' => $route,
            ],
        );
    }
}
