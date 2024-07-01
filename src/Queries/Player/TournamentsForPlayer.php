<?php

namespace App\Queries\Player;

use App\Enum\GameId;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\Tournament;

class TournamentsForPlayer extends AbstractQuery
{
    private const OPERATION = "Sets";

    public function __construct(
        protected int $playerId,
        protected int $perPage = 50,
        protected int $page = 0,
    ) {

    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $playerId = $this->playerId;
        $perPage = $this->perPage;
        $page = $this->page;
        $videogameId = GameId::SMASH_ULTIMATE->value;

        return <<<END
        query Sets {
            player(id: $playerId) {
                id
                user {
                  tournaments (
                    query: {
                      perPage: $perPage,
                      page: $page,
                      filter: {
                        videogameId: $videogameId,
                        past: true
                      }
                    }
                  ) {
                    nodes {
                      name
                      id
                      startAt
                    }
                  }
                }
            }
        }
        END;
    }

    /**
     * Implements AbstractQuery
     */
    protected function getVariables(): array
    {
        return [];
    }

    /**
     * Implements AbstractQuery
     */
    protected function getOperation(): string
    {
        return self::OPERATION;
    }
}
