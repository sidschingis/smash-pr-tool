<?php

namespace App\Controller;

use App\Forms\EventForm;
use App\Queries\Tournament\TournamentsForRegion;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractApiController
{
    #[Route('/event', name: 'app_events')]
    public function eventCrud(): Response
    {
        $links = [
        ];

        return $this->render(
            'index.html.twig',
            [
                'links' => $links
            ],
        );
    }

    #[Route('/event/import', name: 'app_events_import')]
    public function eventImport(
        Request $request,
    ): Response {
        $afterDate = $request->query->getInt('afterDate');

        $query = new TournamentsForRegion(
            afterDate: $afterDate
        );

        $response = $this->sendRequest($query);

        $tournaments = $query::JsonToTournaments($response);

        $route = $this->generateUrl('app_action_importEvents');

        foreach ($tournaments as $tournament) {
            $tournamentName = $tournament->name;
            $startAt = (new DateTimeImmutable())->setTimestamp($tournament->startTime);
            $date = $startAt->format('Y-m-d');

            foreach ($tournament->events as $event) {
                $label = <<<EOD
                    ($date) $tournamentName: $event->eventName
                EOD;
                $id = $event->eventId;
                $choices[$label] = $id;
            }
        }

        $form = $this->createForm(
            EventForm::class,
            options: [
                'data' => [
                    'action' => $route,
                    'choices' => $choices,
                ],
            ],
        );

        return $this->render(
            'import/event/eventSelect.html.twig',
            [
                'form' => $form,
            ],
        );
    }
}
