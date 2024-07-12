<?php

namespace App\Controller;

use App\Entity\Player;
use App\Forms\Player\AddPlayerForm;
use App\Forms\Player\EditPlayerForm;
use App\Forms\Player\FilterPlayerForm;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrudController extends AbstractController
{
    #[Route('/crud/players', name: 'app_crud_players')]
    public function importSets(
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

        $editForms = [];
        foreach ($players as $index => $player) {
            $editForm->setData((array) $player);
            $editForms[] = $editForm->createView();
        }

        $playerString = var_export($request->query, true);
        $request = var_export($query->getSQL(), true);
        $debug = <<<EOD
        $playerString
        $request
        EOD;

        return $this->render(
            'crud/player/playerCrud.html.twig',
            [
                'debug' => $debug,
                'filterForm' => $filterForm,
                'addForm' => $addForm,
                'editForms' => $editForms,
            ],
        );
    }
}
