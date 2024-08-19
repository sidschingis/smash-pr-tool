<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Set;
use App\Forms\Player\AddPlayerForm;
use App\Forms\Player\EditPlayerForm;
use App\Forms\Player\FilterPlayerForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
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
                'attr' => [
                    'name' => 'foo',
                    'id' => 'foo',
                ]
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

        $editForms = [];
        foreach ($players as $player) {
            $editForm->setData($player);
            $editForms[] = $editForm->createView();
        }

        return $this->render(
            'crud/player/playerCrud.html.twig',
            [
                'debug' => '',
                'filterForm' => $filterForm,
                'addForm' => $addForm,
                'editForms' => $editForms,
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
            $querybuilder->where($like);
            $querybuilder->setParameter('tag', '%' . addcslashes($tag, '%_') . '%');
        }

        $id = $request->query->getString('idFilter');
        if ($id) {
            $querybuilder->where("p.id = :id")
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
        $repository = $entityManager->getRepository(Set::class);

        $debug = '';

        $sets = $this->fetchSets(
            $request,
            $repository,
            $idPlayer,
        );

        $setData = [];
        foreach ($sets as $set) {
            $form =

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
                'debug' => $debug,
                'setData' => $setData,
                'playerName' => 'foo',
                'deleteSetsRoute' => '',
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
        $querybuilder->where($playerFilter);
        $querybuilder->setParameter('playerId', $playerId);

        $id = $request->query->getString('idFilter');
        if ($id) {
            $querybuilder->where('s.id = :id')
                ->setParameter('id', $id);
        }

        $eventName =  $request->query->getString('eventFilter');
        if ($eventName) {
            $querybuilder->where('s.eventName = :eventName')
                ->setParameter('eventName', $eventName);
        }

        $tournamentName =  $request->query->getString('tournamentFilter');
        if ($eventName) {
            $querybuilder->where('s.tournamentName = :tournamentName')
                ->setParameter('tournamentName', $tournamentName);
        }

        $query = $querybuilder
            ->setMaxResults(20)
            ->getQuery();

        $players = $query
            ->getResult(Query::HYDRATE_ARRAY);

        return $players;
    }
}
