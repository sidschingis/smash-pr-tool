<?php

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class Request
{
    public function sendRequest(
        string $token = "",
    ): string {
        $client = new Client(
            [
                'base_uri' => "https://api.start.gg/gql/alpha",
            ]
        );

        $query = '
        query Sets ($playerId:ID!) {
            player(id: $playerId) {
              id
              sets(perPage: 10, page: 0) {
                nodes {
                  id
                  displayScore
                  event {
                    id
                    name
                    tournament {
                      id
                      name
                    }
                  }
                }
              }
            }
          }
        ';

        $variables = [
            'playerId' => 1135316,
        ];

        $operation = "Sets";

        $body = [
            'query' => $query,
            'operationName' => $operation,
            'variables' => $variables,
        ];

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
