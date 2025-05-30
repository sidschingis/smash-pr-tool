<?php

namespace App\Controller;

use App\Action\Event\ImportEvents;
use App\Action\Placement\CalculateScores;
use App\Action\Placement\ImportPlacements;
use App\Action\Player\ImportMissingPlayers;
use App\Action\Ranking\UpdateRankings;
use App\Action\Sets\DeleteSets;
use App\Action\Sets\ImportSets;
use App\ControllerData\EventData;
use App\Entity\Placement;
use App\Objects\ImportEvent;
use App\Queries\Event\EventById;
use App\Queries\Event\FetchEventsBySeason;
use App\Queries\Placement\PlacementsForEvent;
use App\Queries\Player\SetsForPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
        $importSets = [];
        foreach ($sets->eventInfos as $importEvent) {
            $importSets = array_merge($importSets, $importEvent->sets);
        }
        $action->importSets($importSets);

        //
        $setData = implode(
            PHP_EOL,
            array_map(
                fn (EventData $eventData) => "{$eventData->tournamentName} {$eventData->eventName}",
                $sets->eventInfos
            )
        );

        $importPlayers = new ImportMissingPlayers($entityManager);
        $playerResult = $importPlayers->importPlayers($importSets);

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

    #[Route('/action/importEvents', name: 'app_action_importEvents')]
    public function importEvents(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $eventIds = $request->request->all()['eventIds'] ?? [];

        if (!is_array($eventIds)) {
            return new Response('Invalid event ids');
        }

        $singleId = $request->request->getInt('singleEventId');

        if ($singleId) {
            $eventIds[] = $singleId;
        }

        if ($eventIds === []) {
            return new Response('No events selected');
        }

        set_time_limit(30 * count($eventIds));

        $importEvents = [];
        foreach ($eventIds as $eventId) {
            $eventData = $this->getEventData($eventId);
            /** @var ImportEvent[] */
            $importEvents = array_merge($importEvents, $eventData);
        }

        $importer = new ImportEvents(
            eventManager: $entityManager,
        );

        $importResult = $importer->importEvents($importEvents);

        /**
         * Import Sets
         */
        $setImporter = new ImportSets($entityManager);
        $importSets = [];
        foreach ($importEvents as $importEvent) {
            $importSets = array_merge($importSets, $importEvent->sets);
        }
        $setImporter->importSets($importSets);

        $this->importPlacements($eventIds, $entityManager);

        $this->calculateScores($eventIds, $entityManager);

        $playerImporter = new ImportMissingPlayers($entityManager);
        $playerImporter->importPlayers($importSets);

        $response = $importResult ? 'Success' : 'Failure';

        return new Response($response);
    }

    /**
     * @return ImportEvent[]
     */
    private function getEventData(int $eventId): array
    {
        $page = 1;

        $importEvents = [];

        do {
            $query = new EventById($eventId, setPage: $page);

            $response = $this->sendRequest($query);

            if (strpos($response, "errors") !== false) {
                $msg = <<<EOD
                Error querying event: $eventId
                $response
                EOD;
                throw new Exception($msg);
            }

            $importEvent = $query::JsonToImportData($response);

            if ($importEvent->sets !== []) {
                /**
                 * Skip unreported batches
                 */
                $importEvents[] = $importEvent;
            }

            $nextPage = $importEvent->nextPage;
            if ($nextPage === 0) {
                return $importEvents;
            }

            $page = $nextPage;

        } while(1);
    }


    private function importPlacements(
        array $eventIds,
        EntityManagerInterface $entityManager,
    ): bool {

        $importPlacements = [];

        foreach ($eventIds as $eventId) {
            $placementData =  $this->getPlacementData($eventId);
            $importPlacements = array_merge($importPlacements, $placementData);
        }

        $importer = new ImportPlacements($entityManager);

        $importResult = $importer->importPlacements($importPlacements);

        return $importResult;
    }

    /**
     * @return Placement[]
     */
    private function getPlacementData(int $eventId): array
    {
        $page = 1;

        $importPlacements = [];
        do {
            $query = new PlacementsForEvent($eventId, standingPage: $page);

            $response = $this->sendRequest($query);

            if (strpos($response, "errors") !== false) {
                $msg = <<<EOD
                Error querying event: $eventId
                $response
                EOD;
                throw new Exception($msg);
            }

            $importPlacement = $query::JsonToImportData($response);

            $importPlacements[] = $importPlacement;

            $nextPage = $importPlacement->nextPage;
            if ($nextPage === 0) {
                return $importPlacements;
            }

            $page = $nextPage;

        } while(1);
    }


    #[Route('/action/updatePlacements', name: 'app_action_updatePlacings')]
    public function updatePlacements(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $paramSeasonId = 'seasonId';
        $seasonId = $request->request->getInt($paramSeasonId);
        if ($seasonId === 0) {
            $seasonId = $request->query->getInt($paramSeasonId);
        }

        $eventIds = (new FetchEventsBySeason())->getData(
            entityManager: $entityManager,
            seasonId: $seasonId,
        );

        $this->calculateScores(
            eventIds: $eventIds,
            entityManager: $entityManager,
        );

        return $this->redirectToRoute(
            route: 'app_ranking_season_ranking',
            parameters:[
                'seasonId' => $seasonId,
            ],
        );
    }


    private function calculateScores(
        array $eventIds,
        EntityManagerInterface $entityManager,
    ): void {
        $calculator = new CalculateScores($entityManager);
        foreach ($eventIds as $eventId) {
            $calculator->calculate($eventId);
        }
    }

}
