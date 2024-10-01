<?php

namespace App\Controller;

use App\Action\Player\ImportMissingPlayers;
use App\Action\Ranking\UpdateRankings;
use App\Action\Sets\DeleteSets;
use App\Action\Sets\ImportSets;
use App\ControllerData\EventData;
use App\Queries\Player\SetsForPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActionController extends AbstractApiController
{
    #[Route('/action/importSets', name: 'app_action_importSets')]
    public function importSets(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $playerId = $request->request->getInt('playerId');
        $eventIds = $request->request->all()['eventIds'] ?? [];

        if (!$playerId) {
            return new Response('no player');
        }

        if (!$eventIds) {
            return new Response('no events selected');
        }

        $query = new SetsForPlayer(
            playerId: $playerId,
            eventIds: $eventIds,
            perPage: count($eventIds) * 10,
        );

        $response = $this->sendRequest($query);

        $sets = SetsForPlayer::JsonToSetData($response);

        //Import sets
        $action = new ImportSets($entityManager);
        $action->importSets($sets);

        //
        $setData = implode(
            PHP_EOL,
            array_map(
                fn (EventData $eventData) => "{$eventData->tournamentName} {$eventData->eventName}",
                $sets->eventInfos
            )
        );

        $importPlayers = new ImportMissingPlayers($entityManager);
        $playerResult = $importPlayers->importPlayers($sets);

        $playerData = implode(
            PHP_EOL,
            $playerResult
        );

        $response = <<<EOD
        <pre>
        Sets imported:
        $setData
        New Players:
        $playerData
        </pre>
        EOD;

        return new Response($response);
    }

    #[Route('/action/deleteSets', name: 'app_action_deleteSets')]
    public function deleteSets(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $setIds = $request->request->all()['setIds'] ?? [];

        if (!$setIds) {
            return new Response('no sets selected');
        }

        $action = new DeleteSets($entityManager);
        $action->deleteSets($setIds);

        return $this->redirectToRoute(
            route: 'app_crud_players_sets',
            parameters:[
                'playerId' => $request->request->getString('playerId'),
            ],
        );
    }

    #[Route('/action/updateRankings', name: 'app_action_updateRankings')]
    public function updateRankings(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $seasonId = $request->request->getInt('seasonId');

        $ranking = $request->request->all()['playerId'] ?? [];

        $action = new UpdateRankings($entityManager);
        $action->updateRankings($seasonId, $ranking);

        return $this->redirectToRoute(
            route: 'app_ranking_season_ranking',
            parameters:[
                'seasonId' => $seasonId,
            ],
        );
    }
}
