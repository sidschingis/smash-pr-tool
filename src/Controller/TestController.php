<?php

namespace App\Controller;

use App\Http\Request;
use App\Queries\Player\Sets;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test/foo', name: 'app_lucky_number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        $token = $this->getToken();

        $request = new Request();
        $query = new Sets(playerId:1135316);

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


    private function getToken(): string
    {
        $projectRoot = __DIR__ . "/../../";

        $path = "{$projectRoot}/.secrets/startGGToken.txt";
        $secret = file_get_contents($path);

        return $secret;
    }
}
