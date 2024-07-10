<?php

namespace App\Controller;

use App\Action\ImportSets;
use App\ControllerData\EventData;
use App\Http\ApiRequest;
use App\Queries\Player\SetsForPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $response = <<<EOD
        <pre>
        Sets imported:
        $setData
        </pre>
        EOD;

        return new Response($response);
    }
}
