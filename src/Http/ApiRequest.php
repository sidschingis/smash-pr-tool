<?php

namespace App\Http;

use App\GraphQL\Query\Query;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class ApiRequest
{
    public function sendRequest(
        Query $query,
        string $token = "",
    ): string {
        $client = new Client(
            [
                'base_uri' => "https://api.start.gg/gql/alpha",
            ]
        );

        $body = $query->toBodyArray();

        $options = [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
            'json' => $body
        ];

        $response = $client->request(Method::POST->value, options: $options);

        $contents = $response->getBody()->getContents();

        $jsonContent = json_encode(
            json_decode($contents),
            flags: JSON_PRETTY_PRINT
        );

        return $jsonContent;
    }
}
