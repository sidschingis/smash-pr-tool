<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Set;
use App\Enum\DateFormat;
use App\Enum\Player\Field as PlayerField;
use App\Enum\Player\Filter as PlayerFilter;
use App\Enum\Event\Field as EventField;
use App\Enum\Placement\Filter;
use App\Forms\Player\AddPlayerForm;
use App\Forms\Player\EditPlayerForm;
use App\Forms\Player\FilterPlayerForm;
use App\Forms\Set\FilterSetsForm;
use App\Http\LinkData;
use App\Repository\EventRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrudController extends AbstractController
{
    #[Route('/crud/players', name: 'app_crud_players')]
    public function playerCrud(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $repository = $entityManager->getRepository(Player::class);

        $filterForm = $this->createForm(
            FilterPlayerForm::class,
            options: [
                'attr' => [
                    'class' => 'filter-form',
                ],
            ],
        );
        $filterForm->handleRequest($request);


        $addForm = $this->createForm(
            type: AddPlayerForm::class,
            options: [
                'action' => '',
            ],
        );

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();
            $player = new Player(...$data);

            $entityManager->persist($player);
            $entityManager->flush();

            return $this->redirectToRoute('app_crud_players');
        }

        $editForm = $this->createForm(
            type: EditPlayerForm::class,
            options: [
                'action' => '',
                'method' => 'POST',
            ],
        );

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $data = $editForm->getData();

            /** @var Player */
            $player = $entityManager->find(Player::class, $data[PlayerField::ID->value] ?? 0);
            if ($player) {
                /** @var ClickableInterface */
                $deleteButton = $editForm->get('delete');
                if ($deleteButton->isClicked()) {
                    $entityManager->remove($player);
                } else {
                    $player->setTwitterTag($data[PlayerField::TWITTER->value]);
                    $player->setTag($data[PlayerField::TAG->value]);
                    $player->setRegion($data[PlayerField::REGION->value]);
                    $entityManager->persist($player);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_crud_players');
            }
        }

        $players = $this->fetchPlayers(
            $request,
            $repository
        );

        $existingPlayers = [];
        foreach ($players as $player) {
            $editForm->setData($player);

            $links = [
                new LinkData($this->generateUrl(
                    route: PlacementController::PLACEMENTS,
                    parameters: [
                        Filter::PLAYER->value => $player[PlayerField::ID->value]
                    ],
                ), 'Placements'),
                new LinkData($this->generateUrl(
                    route: 'app_crud_players_sets',
                    parameters: [
                        'playerId' => $player[PlayerField::ID->value]
                    ],
                ), 'Sets'),
                new LinkData($this->generateUrl(
                    route: 'app_import_player_events',
                    parameters: [
                        'playerId' => $player[PlayerField::ID->value]
                    ],
                ), 'Import'),
            ];

            $playerData = new class (
                editForm: $editForm->createView(),
                links: $links,
            ) {
                public function __construct(
                    public FormView $editForm,
                    public array $links,
                ) {
                }
            };

            $existingPlayers[] = $playerData;
        }

        return $this->render(
            'crud/player/playerCrud.html.twig',
            [
                'filterForm' => $filterForm,
                'addForm' => $addForm,
                'existingData' => $existingPlayers,
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private function fetchPlayers(
        Request $request,
        EntityRepository $repository,
    ): array {
        $querybuilder = $repository->createQueryBuilder('p');

        $tag = $request->query->getString(PlayerFilter::TAG->value);
        if ($tag) {
            $like = $querybuilder->expr()->like('p.' . PlayerField::TAG->value, ':tag');
            $querybuilder->andWhere($like);
            $querybuilder->setParameter('tag', '%' . addcslashes($tag, '%_') . '%');
        }

        $region = $request->query->getString(PlayerFilter::REGION->value);
        if ($region) {
            $like = $querybuilder->expr()->like('p.' . PlayerField::REGION->value, ':region');
            $querybuilder->andWhere($like);
            $querybuilder->setParameter('region', '%' . addcslashes($region, '%_') . '%');
        }

        $id = $request->query->getString(PlayerFilter::ID->value);
        if ($id) {
            $querybuilder->andWhere('p.' . PlayerField::ID->value . ' = :id')
                ->setParameter('id', $id);
        }
        $query = $querybuilder
            ->setMaxResults(20)
            ->getQuery();


        $players = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $players;
    }

    #[Route(
        '/crud/players/{playerId}/sets',
        name: 'app_crud_players_sets',
        requirements: ['playerId' => '\d+']
    )]
    public function setCrud(
        Request $request,
        EntityManagerInterface $entityManager,
        int $playerId,
    ): Response {
        $setRepo = $entityManager->getRepository(Set::class);
        $eventRepo = $entityManager->getRepository(Event::class);

        $filterForm = $this->createForm(
            FilterSetsForm::class,
            options: [
                'attr' => [
                    'class' => 'filter-form',
                ],
            ],
        );
        $filterForm->handleRequest($request);

        /** @var ?Player */
        $player = $entityManager->find(Player::class, $playerId);

        $sets = $this->fetchSets(
            $request,
            $setRepo,
            $playerId,
        );

        $events =  $this->fetchEvents($sets, $eventRepo);#

        $setData = [];
        foreach ($sets as $set) {
            $data = $set;
            $eventId = $set['eventId'];
            $data += $events[$eventId] ?? [];
            $setData[] = new class (
                ...$data,
            ) {
                public string $dateString;

                public function __construct(
                    public int $id,
                    public int $winnerId,
                    public int $loserId,
                    public string $displayScore,
                    public string $eventId,
                    DateTimeInterface $date,
                    public string $eventName = '',
                    public string $tournamentName = '',
                ) {
                    $this->dateString = $date->format(DateFormat::DATE->value);
                }
            };
        }

        return $this->render(
            'crud/player/sets/setCrud.html.twig',
            [
                'filterForm' => $filterForm,
                'setData' => $setData,
                'playerTag' => $player?->getTag() ?? 'unknown',
                'playerId' => $playerId,
                'deleteSetsRoute' => $this->generateUrl('app_action_deleteSets'),
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private function fetchSets(
        Request $request,
        EntityRepository $repository,
        int $playerId
    ): array {
        $querybuilder = $repository->createQueryBuilder('s');

        $playerFilter = <<<EOD
            (
                s.winnerId = :playerId OR
                s.loserId = :playerId
            )
        EOD;
        $querybuilder->andWhere($playerFilter);
        $querybuilder->setParameter('playerId', $playerId);

        $id = $request->query->getString('idFilter');
        if ($id) {
            $querybuilder->andWhere('s.id = :id');
            $querybuilder->setParameter('id', $id);
        }

        $minDate = $request->query->getString('minDate');
        if ($minDate) {
            $querybuilder->andWhere('s.date >= :minDate');
            $querybuilder->setParameter('minDate', $minDate);
        }

        $query = $querybuilder
            ->setMaxResults(100)
            ->getQuery();

        $players = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $players;
    }

    /**
     * @param mixed[][]
     * @return array<int,<string,<string>>
     */
    private function fetchEvents(
        array $sets,
        EventRepository $repository,
    ): array {
        $querybuilder = $repository->createQueryBuilder('e');

        $querybuilder->select(
            'e.'.EventField::ID->value,
            'e.'.EventField::EVENT_NAME->value,
            'e.'.EventField::TOURNAMENT_NAME->value,
        );

        $eventIds = [];
        foreach ($sets as $setData) {
            $eventIds[] = $setData['eventId'];
        }

        $querybuilder->andWhere('e.id in (:id)');
        $querybuilder->setParameter('id', $eventIds);

        $query = $querybuilder
            ->getQuery();

        $events = $query
            ->getResult(Query::HYDRATE_ARRAY);

        $indexed = [];
        foreach($events as $event) {
            $id = $event[EventField::ID->value];
            $indexed[$id] = $event;
        }

        return $indexed;
    }
}
