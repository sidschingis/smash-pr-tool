<?php

namespace App\Controller;

use App\Forms\SetForm;
use App\Forms\TournamentForm;
use App\Http\Request;
use App\Queries\Player\Sets;
use App\Queries\Player\SetsForPlayer;
use App\Queries\Player\TournamentsForPlayer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    public const ROUTE_PLAYER_SETS = 'player_sets';

    #[Route('/test/foo', name: 'app_lucky_number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        $token = $this->getToken();

        $request = new Request();
        $query = new SetsForPlayer(playerId: 1135316);

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        return new Response(
            '<html>
            <body>
            <pre>' . $response . '
            </pre>
            </body>
            </html>'
        );
    }

    #[Route('/player/{idPlayer}/sets', name: TestController::ROUTE_PLAYER_SETS, requirements: ['idPlayer' => '\d+'])]
    public function playerSets(
        int $idPlayer
    ): Response {
        $httpRequest = HttpRequest::createFromGlobals();

        $queryParams =  $httpRequest->query;

        $tournamentIds = $queryParams->all()['tournamentIds'] ?? [];
        $startTime = $queryParams->getInt('startTime');

        $token = $this->getToken();

        $request = new Request();
        $query = new SetsForPlayer(
            playerId: $idPlayer,
            tournamentIds: $tournamentIds,
            startTimeStamp: $startTime,
        );

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        $sets = SetsForPlayer::JsonToSetData($response);
        $debug = var_export($sets, true);

        return new Response(
            <<<EOD
            <html>
            <body>
            <pre> $debug
            </pre>
            <pre> $response
            </pre>
            </body>
            </html>
            EOD
        );
    }

    #[Route('/player/{idPlayer}/tournaments', name: 'player_events', requirements: ['idPlayer' => '\d+'])]
    public function playerTournaments(int $idPlayer): Response
    {
        $token = $this->getToken();

        $request = new Request();
        $query = new TournamentsForPlayer(playerId: $idPlayer);

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        $tournamentData = TournamentsForPlayer::JsonToTournamentData($response);
        $tournaments = $tournamentData->tournaments;
        $debug = var_export($tournaments, true);

        $route = $this->generateUrl(
            TestController::ROUTE_PLAYER_SETS,
            parameters: [
                'idPlayer' => $idPlayer,
            ],
        );

        $choices = [];
        foreach ($tournaments as $tournament) {
            $id = $tournament->id;
            $label = "$tournament->name";

            $choices[$label] = $id;
        }

        $form = $this->createForm(
            TournamentForm::class,
            options: [
                'data' => [
                    'action' => $route,
                    'choices' => $choices,
                ],
            ],
        );

        return $this->render(
            'player/tournaments/formContainer.html.twig',
            [
                'debug' => $debug,
                'playerName' => $tournamentData->name,
                'tournaments' => $tournaments,
                'form' => $form,
                'route' => $route,
            ],
        );

        return new Response(
            <<<EOD
            <html>
            <body>
            <pre> $debug
            </pre>
            <pre> $response
            </pre>
            </body>
            </html>
            EOD
        );
    }


    private function getToken(): string
    {
        $projectRoot = __DIR__ . "/../../";

        $path = "{$projectRoot}/.secrets/startGGToken.txt";
        $secret = file_get_contents($path);

        return $secret;
    }
}
