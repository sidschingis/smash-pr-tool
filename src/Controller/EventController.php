<?php

namespace App\Controller;

use App\Entity\Event;
use App\Forms\Event\ImportEventForm;
use App\Queries\Tournament\TournamentsForRegion;
use App\Enum\Event\Field as EventField;
use App\Enum\Event\Filter as EventFilter;
use App\Forms\Event\AddEventForm;
use App\Forms\Event\EditEventForm;
use App\Forms\Event\FilterEventForm;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractApiController
{
    #[Route('/event', name: 'app_events')]
    public function eventCrud(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $repository = $entityManager->getRepository(Event::class);

        $filterForm = $this->createForm(
            FilterEventForm::class,
            options: [
                'attr' => [
                    'class' => 'filter-form',
                ],
            ],
        );
        $filterForm->handleRequest($request);


        $addForm = $this->createForm(
            type: AddEventForm::class,
            options: [
                'action' => '',
            ],
        );

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();
            $event = new Event(...$data);

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_events');
        }

        $editForm = $this->createForm(
            type: EditEventForm::class,
            options: [
                'action' => '',
                'method' => 'POST',
            ],
        );

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $data = $editForm->getData();

            /** @var Event */
            $event = $entityManager->find(Event::class, $data[EventField::ID->value] ?? 0);
            if ($event) {
                /** @var ClickableInterface */
                $deleteButton = $editForm->get('delete');
                if ($deleteButton->isClicked()) {
                    $entityManager->remove($event);
                } else {
                    $event->setRegion($data[EventField::REGION]);
                    $entityManager->persist($event);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_crud_events');
            }
        }

        $events = $this->fetchEvents(
            $request,
            $repository
        );

        $existingEvents = [];
        foreach ($events as $event) {
            $editForm->setData($event);

            $links = [
            ];

            $eventData = new class (
                editForm: $editForm->createView(),
                links: $links,
            ) {
                public function __construct(
                    public FormView $editForm,
                    public array $links,
                ) {
                }
            };

            $existingEvents[] = $eventData;
        }

        return $this->render(
            'crud/event/eventCrud.html.twig',
            [
                'filterForm' => $filterForm,
                'addForm' => $addForm,
                'existingData' => $existingEvents,
            ],
        );
    }

    /**
    * @return mixed[][]
    */
    private function fetchEvents(
        Request $request,
        EntityRepository $repository,
    ): array {
        $querybuilder = $repository->createQueryBuilder('p');

        $id = $request->query->getString(EventFilter::ID->value);
        if ($id) {
            $querybuilder->andWhere('p.' . EventField::ID->value . ' = :id')
                ->setParameter('id', $id);
        }

        $region = $request->query->getString(EventFilter::REGION->value);
        if ($region) {
            $like = $querybuilder->expr()->like('p' . EventField::REGION->value, ':region');
            $querybuilder->andWhere($like);
            $querybuilder->setParameter('region', '%' . addcslashes($region, '%_') . '%');
        }

        $query = $querybuilder
            ->setMaxResults(20)
            ->getQuery();


        $events = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $events;
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

        $choices = [];
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
            ImportEventForm::class,
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
