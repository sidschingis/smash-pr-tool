<?php

namespace App\Controller;

use App\Enum\Placement\Filter;
use App\Forms\Placement\FilterPlacementForm;
use App\Queries\Placement\FetchPlacements;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlacementController extends AbstractController
{
    public const PLACEMENTS = 'app_placement';

    #[Route('/placement', name: self::PLACEMENTS)]
    public function placements(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $filterForm = $this->createForm(
            FilterPlacementForm::class,
            options: [
                'attr' => [
                    'class' => 'filter-form',
                ],
            ],
        );
        $filterForm->handleRequest($request);

        $placementData = $this->fetchPlacemements(
            $request,
            $entityManager
        );

        return $this->render(
            'placement/placements.html.twig',
            [
                'filterForm' => $filterForm,
                'placementData' => $placementData,
            ],
        );
    }

    /**
     * @return object[]
     */
    private function fetchPlacemements(
        Request $request,
        EntityManagerInterface $entityManager
    ): array {
        $query = new FetchPlacements();

        $data = $query->getData(
            entityManager: $entityManager,
            playerFilter: $request->query->getInt(Filter::PLAYER->value),
            eventFilter: $request->query->getInt(Filter::EVENT->value),
        );

        return array_map(fn (array $array) => (object) $array, $data);
    }

}
