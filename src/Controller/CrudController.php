<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Set;
use App\Forms\Player\AddPlayerForm;
use App\Forms\Player\EditPlayerForm;
use App\Forms\Player\FilterPlayerForm;
use App\Forms\Set\FilterSetsForm;
use DateTime;
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
            $player = $entityManager->find(Player::class, $data['id'] ?? 0);
            if ($player) {
                /** @var ClickableInterface */
                $deleteButton = $editForm->get('delete');
                if ($deleteButton->isClicked()) {
                    $entityManager->remove($player);
                } else {
                    $player->setTwitterTag($data['twitterTag']);
                    $player->setTag($data['tag']);
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

            $setUrl = $this->generateUrl(
                route: 'app_crud_players_sets',
                parameters: [
                    'idPlayer' => $player['id']
                ],
            );

            $playerData = new class (
                editForm: $editForm->createView(),
                setUrl: $setUrl
            ) {
                public function __construct(
                    public FormView $editForm,
                    public string $setUrl,
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
                'existingPlayers' => $existingPlayers,
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

        $tag = $request->query->getString('tagFilter');
        if ($tag) {
            $like = $querybuilder->expr()->like('p.tag', ':tag');
            $querybuilder->andWhere($like);
            $querybuilder->setParameter('tag', '%' . addcslashes($tag, '%_') . '%');
        }

        $id = $request->query->getString('idFilter');
        if ($id) {
            $querybuilder->andWhere("p.id = :id")
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
        '/crud/players/{idPlayer}/sets',
        name: 'app_crud_players_sets',
        requirements: ['idPlayer' => '\d+']
    )]
    public function setCrud(
        Request $request,
        EntityManagerInterface $entityManager,
        int $idPlayer,
    ): Response {
        $setRepo = $entityManager->getRepository(Set::class);

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
        $player = $entityManager->find(Player::class, $idPlayer);

        $sets = $this->fetchSets(
            $request,
            $setRepo,
            $idPlayer,
        );

        $setData = [];
        foreach ($sets as $set) {
            $setData[] = new class (
                ...$set,
            ) {
                public string $dateString;

                public function __construct(
                    public int $id,
                    public int $winnerId,
                    public int $loserId,
                    public string $displayScore,
                    public string $eventName,
                    public string $tournamentName,
                    DateTime $date,
                ) {
                    $this->dateString = $date->format('Y-m-d');
                }
            };

        }

        return $this->render(
            'crud/player/sets/setCrud.html.twig',
            [
                'filterForm' => $filterForm,
                'setData' => $setData,
                'playerName' => $player?->getTag() ?? 'unknown',
                'idPlayer' => $idPlayer,
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
}
