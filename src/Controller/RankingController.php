<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Season;
use App\Forms\Ranking\AddSeasonForm;
use App\Forms\Ranking\EditSeasonForm;
use App\Http\LinkData;
use App\Queries\Ranking\FetchRankings;
use App\Queries\Ranking\FetchWinsLosses;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    #[Route(
        '/ranking/season',
        name: 'app_ranking_season_crud',
    )]
    public function seasonCrud(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $addForm = $this->createForm(
            type: AddSeasonForm::class,
            options: [
                'action' => '',
            ],
        );

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();
            $season = new Season(...$data);

            $entityManager->persist($season);
            $entityManager->flush();

            return $this->redirectToRoute('app_ranking_season_crud');
        }

        $editForm = $this->createForm(
            type: EditSeasonForm::class,
            options: [
                'action' => '',
                'method' => 'POST',
            ],
        );

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $data = $editForm->getData();

            /** @var Season */
            $Season = $entityManager->find(Season::class, $data['id'] ?? 0);
            if ($Season) {
                /** @var ClickableInterface */
                $deleteButton = $editForm->get('delete');
                if ($deleteButton->isClicked()) {
                    $entityManager->remove($Season);
                } else {
                    $Season->setName($data['name']);
                    $Season->setStartDate($data['startDate']);
                    $Season->setEndDate($data['endDate']);
                    $entityManager->persist($Season);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_ranking_season_crud');
            }
        }

        /** @var SeasonRepository */
        $repository = $entityManager->getRepository(Season::class);
        $seasons = $repository->findLatest();

        $existingData = [];
        foreach ($seasons as $entity) {
            $editForm->setData($entity);

            $links = [
                new LinkData(
                    $this->generateUrl('app_ranking_season_details', [
                        'idSeason' => $entity['id'],
                    ]),
                    'Details',
                ),
                new LinkData(
                    $this->generateUrl('app_ranking_season_ranking', [
                        'idSeason' => $entity['id'],
                    ]),
                    'Ranking',
                ),
            ];

            $seasonData = new class (
                editForm: $editForm->createView(),
                links: $links,
            ) {
                public function __construct(
                    public FormView $editForm,
                    public array $links,
                ) {
                }
            };

            $existingData[] = $seasonData;
        }

        return $this->render(
            'ranking/season_crud.html.twig',
            [
                'addForm' => $addForm,
                'existingData' => $existingData,
            ],
        );
    }


    #[Route(
        '/ranking/season/{idSeason}',
        name: 'app_ranking_season_details',
        requirements: ['idSeason' => '\d+']
    )]
    public function seasonDetails(
        EntityManagerInterface $entityManager,
        int $idSeason,
    ): Response {
        /** @var ?Season */
        $season = $entityManager->find(Season::class, $idSeason);
        if (!$season) {
            return $this->redirectToRoute('app_ranking_season_crud');
        }

        $winLossRoute =   $this->generateUrl('app_ranking_season_winsLosses', [
         'idSeason' => $idSeason,
        ]);

        return $this->render(
            'ranking/season_details.html.twig',
            [
                'seasonName' => $season->getName(),
                'winLossRoute' => $winLossRoute,
            ],
        );
    }

    #[Route(
        '/ranking/season/{idSeason}/winsLosses',
        name: 'app_ranking_season_winsLosses',
        requirements: ['idSeason' => '\d+']
    )]
    public function winsLosses(
        Request $request,
        EntityManagerInterface $entityManager,
        int $idSeason,
    ): Response {
        $idPlayer = $request->query->getInt('idPlayer');

        /** @var ?Player */
        $player = $entityManager->find(Player::class, $idPlayer);
        $playerTag = $player?->getTag() ?: "Unknown($idPlayer)";

        /** @var ?Season */
        $season = $entityManager->find(Season::class, $idSeason);
        if (!$season) {
            return $this->redirectToRoute('app_ranking_season_crud');
        }

        $query = new FetchWinsLosses();
        [$wins, $losses] = $query->getData(
            entityManager: $entityManager,
            idSeason: $idSeason,
            idPlayer: $idPlayer,
        );

        return $this->render(
            'ranking/winsLosses.html.twig',
            [
                'seasonName' => $season->getName(),
                'playerTag' => $playerTag,
                'wins' => $wins,
                'losses' => $losses,
            ],
        );
    }

    #[Route(
        '/ranking/season/{idSeason}/ranking',
        name: 'app_ranking_season_ranking',
        requirements: ['idSeason' => '\d+']
    )]
    public function rankings(
        EntityManagerInterface $entityManager,
        int $idSeason,
    ): Response {
        /** @var ?Season */
        $season = $entityManager->find(Season::class, $idSeason);
        if (!$season) {
            return $this->redirectToRoute('app_ranking_season_crud');
        }

        $query = new FetchRankings();
        $existingRankings = $query->getData($entityManager, $idSeason);

        $rankings = [];
        foreach(range(1, 20) as $rank) {
            $existingData = $existingRankings[$rank] ?? [];
            $rankings[] = new class (
                rank: $rank,
                idPlayer: $existingData['idPlayer'] ?? 0,
                playerTag: $existingData['playerTag'] ?? '',
            ) {
                public function __construct(
                    public int $rank,
                    public int $idPlayer,
                    public string $playerTag,
                ) {
                }
            };
        }

        return $this->render(
            'ranking/season_ranking.html.twig',
            [
                'seasonName' => $season->getName(),
                'updateAction' => $this->generateUrl('app_action_updateRankings'),
                'idSeason' => $idSeason,
                'rankings' => $rankings,
            ],
        );
    }
}
