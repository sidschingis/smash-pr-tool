<?php

namespace App\Controller;

use App\Config\DateFormat;
use App\Forms\TournamentForm;
use App\Queries\Player\SetsForPlayer;
use App\Queries\Player\TournamentsForPlayer;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractApiController
{
    public const ROUTE_IMPORT_SETS = 'app_import_player_sets';

    #[Route(
        '/import/player/{playerId}/sets',
        name: ImportController::ROUTE_IMPORT_SETS,
        requirements: ['playerId' => '\d+']
    )]
    public function playerSets(
        int $playerId
    ): Response {
        $httpRequest = Request::createFromGlobals();

        $queryParams =  $httpRequest->query;

        $tournamentIds = $queryParams->all()['tournamentIds'] ?? [];
        $startTime = $queryParams->getInt('startTime');

        $query = new SetsForPlayer(
            playerId: $playerId,
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
        '/import/player/{playerId}/events',
        name: 'app_import_player_events',
        requirements: ['playerId' => '\d+']
    )]
    public function playerTournaments(int $playerId): Response
    {
        $query = new TournamentsForPlayer(playerId: $playerId);

        $response = $this->sendRequest($query);

        $tournamentData = TournamentsForPlayer::JsonToTournamentData($response);
        $tournaments = $tournamentData->tournaments;
        $debug = var_export($tournaments, true);

        $route = $this->generateUrl(
            ImportController::ROUTE_IMPORT_SETS,
            parameters: [
                'playerId' => $playerId,
            ],
        );

        $choices = [];
        foreach ($tournaments as $tournament) {
            $id = $tournament->id;
            $name = $tournament->name;
            $date = (new DateTime())
                ->setTimestamp($tournament->startTime)
                ->format(DateFormat::DATE->value);
            $label = "{$date} {$name}";

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
