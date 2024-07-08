<?php

namespace App\Queries\Player;

use App\Entity\Set;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\Tournament;
use App\Util\JsonSerializer;
use DateTimeImmutable;
use DateTimeZone;

class SetsForPlayer extends AbstractQuery
{
    private const OPERATION = "Sets";

    /**
     * @return Set[]
     */
    public static function JsonToSets(string $json): array
    {
        $sets = [];

        $data = (new JsonSerializer())->deserialize($json);

        $nodes = $data?->data?->player?->sets?->nodes ?? [];

        $matches = [];
        $winnerRegex = '/(.*) (\\d) - (.*) (\\d)/';
        $timeZone = new DateTimeZone('UTC');

        foreach ($nodes as $rawNode) {
            $newSet =  new Set();

            $displayScore  = $rawNode->displayScore ?? '';
            $startTime = $rawNode?->event?->tournament?->startAt ?? 0;

            $isMatch = preg_match($winnerRegex, $displayScore, $matches);
            if (!$isMatch) {
                /**
                 * DQs
                 */
                continue;
            }
            [$match, $p1Name, $p1Wins, $p2Name, $p2Wins] = $matches;

            $winnerName = ((int) $p1Wins > (int) $p2Wins) ? $p1Name : $p2Name;

            $slots = $rawNode?->slots ?? [];
            foreach ($slots as $slot) {
                $id =  $slot->entrant?->id;
                $name =  $slot->entrant?->name;

                if ($name === $winnerName) {
                    $winnerId = $id;
                } else {
                    $loserId = $id;
                }
            }

            $newSet->setId($rawNode->id ?? 0);
            $newSet->setWinnerId($winnerId);
            $newSet->setLoserId($loserId);
            $newSet->setDisplayScore($displayScore);
            $newSet->setDate((new DateTimeImmutable(timezone: $timeZone))->setTimestamp($startTime));
            $newSet->setEventName($rawNode?->event?->name ?? '');
            $newSet->setTournamentName($rawNode?->event?->tournament?->name ?? '');

            $idEvent = $rawNode?->event?->id ?? 0;

            $sets[$idEvent][] = $newSet;
        }

        return $sets;
    }

    public function __construct(
        protected int $playerId,
        protected int $perPage = 100,
        protected int $page = 0,
        protected array $tournamentIds = [],
        protected int $startTimeStamp = 0,
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
        $startTimeStamp = $this->startTimeStamp;
        $tournamentIds = implode(",", $this->tournamentIds);

        return <<<END
        query Sets{
            player(id: $playerId) {
                id
                sets(perPage: $perPage, page: $page, filters: {
                  isEventOnline: false,
                  showByes: false,
                  tournamentIds: [$tournamentIds]
                  updatedAfter: $startTimeStamp
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
                        startAt
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
