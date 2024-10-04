<?php

namespace App\Queries\Tournament;

use App\ControllerData\EventData;
use App\Enum\GameId;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\Tournament;
use App\Util\JsonSerializer;

class TournamentsForRegion extends AbstractQuery
{
    private const OPERATION = "Tournaments";

    /**
     * @return Tournament[]
     */
    public static function JsonToTournaments(string $json): array
    {
        $tournaments = [];

        $data = (new JsonSerializer())->deserialize($json);
        $nodes = $data?->data?->tournaments?->nodes ?? [];

        foreach ($nodes as $rawNode) {
            $events = [];
            $tournamentName = $rawNode->name ?? '';
            foreach ($rawNode->events ?? [] as $rawEvent) {
                $eventName = $rawEvent->name ?? '';

                /**
                 * skip ladder
                 */
                if ($eventName === 'Ladder') {
                    continue;
                }

                $events[] = new EventData(
                    tournamentName: $tournamentName,
                    eventName: $eventName,
                    eventId: $rawEvent->id ?? 0,
                    sets: [],
                );
            }

            $tournaments[] = new Tournament(
                id: $rawNode->id ?? 0,
                name: $tournamentName,
                startTime: $rawNode->startAt ?? 0,
                events: $events,
            );
        }

        return $tournaments;
    }

    public function __construct(
        protected int $perPage = 50,
        protected int $page = 0,
        protected string $countryCode = 'DE',
        protected string $addrState = 'BE',
        protected int $afterDate = 0,
    ) {
    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $videogameId = GameId::SMASH_ULTIMATE->value;
        $tournament = Tournament::AsQuery();

        return <<<EOD
        query Tournaments{
            tournaments(
                query:{
                    page: $this->page,
                    perPage: $this->perPage,
                    sortBy: "startAt Desc",
                    filter: {
                        past: true,
                        countryCode: "$this->countryCode",
                        addrState: "$this->addrState",
                        afterDate: $this->afterDate,
                        videogameIds: [
                            $videogameId,
                        ],
                    }
                }){
                    nodes $tournament
                }
        }
        EOD;
    }

    /**
     * Implements AbstractQuery
     */
    protected function getVariables(): array
    {
        return [

        ];
    }

    /**
     * Implements AbstractQuery
     */
    protected function getOperation(): string
    {
        return self::OPERATION;
    }
}
