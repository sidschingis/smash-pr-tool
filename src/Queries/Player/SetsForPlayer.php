<?php

namespace App\Queries\Player;

use App\GraphQL\Query\AbstractQuery;
use App\Objects\Tournament;

class SetsForPlayer extends AbstractQuery
{
    private const OPERATION = "Sets";

    public function __construct(
        protected int $playerId,
        protected int $perPage = 100,
        protected int $page = 0,
        protected array $tournamentIds = [],
    ) {

    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $tournament = Tournament::AsQuery();
        $playerId = $this->playerId;
        $perPage = $this->perPage;
        $page = $this->page;
        $tournamentIds = implode(",", $this->tournamentIds);

        return <<<END
        query Sets{
            player(id: $playerId) {
                id
                sets(perPage: $perPage, page: $page, filters: {
                  isEventOnline: false,
                  showByes: false,
                  tournamentIds: [$tournamentIds],
                }) {
                  nodes {
                    id
                    displayScore
                    slots {
                      entrant {
                        id
                        name
                      }
                    }
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
