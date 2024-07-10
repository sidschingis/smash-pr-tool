<?php

namespace App\Controller;

use App\GraphQL\Query\Query;
use App\Http\ApiRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractApiController extends AbstractController
{
    protected function sendRequest(
        Query $query,
    ): string {
        $request = new ApiRequest();
        $token = $this->getToken();

        $response = $request->sendRequest(
            query: $query,
            token: $token,
        );

        return $response;
    }

    protected function getToken(): string
    {
        $projectRoot = __DIR__ . "/../../";

        $path = "{$projectRoot}/.secrets/startGGToken.txt";
        $secret = file_get_contents($path);

        return $secret;
    }
}
