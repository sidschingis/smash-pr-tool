<?php

namespace App\Queries\Player;

use App\ControllerData\TournamentData;
use App\Enum\GameId;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\Tournament;
use App\Util\JsonSerializer;

class TournamentsForPlayer extends AbstractQuery
{
    private const OPERATION = "Sets";

    public static function JsonToTournamentData(string $json): TournamentData
    {
        $tournaments = [];

        $data = (new JsonSerializer())->deserialize($json);

        $nodes = $data?->data?->player?->user?->tournaments?->nodes ?? [];

        foreach ($nodes as $rawNode) {
            $tournaments[] = new Tournament(
                id: $rawNode->id ?? 0,
                name: $rawNode->name ?? '',
                startTime: $rawNode->startAt ?? 0,
            );
        }

        return new TournamentData(
            name: $data?->data?->player?->gamerTag ?? '',
            tournaments: $tournaments,
        );
    }

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
        $tournament = Tournament::AsQuery();

        return <<<END
        query Sets {
            player(id: $playerId) {
                id
                gamerTag
                user {
                  tournaments (
                    query: {
                      perPage: $perPage,
                      page: $page,
                      filter: {
                        videogameId: $videogameId,
                        past: true,
                      }
                    }
                  ) {
                    nodes $tournament
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
