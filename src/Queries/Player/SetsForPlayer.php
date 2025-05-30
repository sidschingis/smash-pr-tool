<?php

namespace App\Queries\Player;

use App\ControllerData\EventData;
use App\ControllerData\SetData;
use App\Entity\Set;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\Set\ImportSet;
use App\Util\JsonSerializer;
use DateTimeImmutable;
use DateTimeZone;

class SetsForPlayer extends AbstractQuery
{
    private const OPERATION = "Sets";

    public static function JsonToSetData(string $json): SetData
    {
        $data = (new JsonSerializer())->deserialize($json);

        $nodes = $data?->data?->player?->sets?->nodes ?? [];
        $eventInfos = self::GetEventInfos($nodes);

        return new SetData(
            playerTag: $data?->data?->player?->gamerTag ?? '',
            playerId: $data?->data?->player?->id ?? 0,
            eventInfos: $eventInfos,
        );
    }

    /**
     * @return EventData[]
     */
    public static function GetEventInfos(array $nodes): array
    {
        $matches = [];
        $winnerRegex = '/(.*) (\\d) - (.*) (\\d)/';
        $timeZone = new DateTimeZone('UTC');

        $eventInfos = [];

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

            $eventId = $rawNode?->event?->id ?? 0;
            $eventName = $rawNode?->event?->name ?? '';
            $tournamentName = $rawNode?->event?->tournament?->name ?? '';

            $eventInfos[$eventId] ??= new EventData(
                tournamentName: $tournamentName,
                eventName: $eventName,
                eventId: $eventId,
                sets: [],
            );

            [$match, $p1Name, $p1Wins, $p2Name, $p2Wins] = $matches;

            [$winnerTag, $loserTag] = ((int) $p1Wins > (int) $p2Wins) ? [$p1Name, $p2Name] : [$p2Name, $p1Name] ;


            $slots = $rawNode?->slots ?? [];
            foreach ($slots as $slot) {
                $id =  $slot->entrant?->participants[0]?->player?->id;
                $name =  $slot->entrant?->name;

                if ($name === $winnerTag) {
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
            $newSet->setEventId($eventId);

            /** @var EventData */
            $eventData = $eventInfos[$eventId];
            $eventData->sets[] = new ImportSet(set: $newSet, winnerTag:$winnerTag, loserTag: $loserTag);
        }

        return $eventInfos;
    }

    public function __construct(
        protected int $playerId,
        protected int $perPage = 300,
        protected int $page = 0,
        protected array $tournamentIds = [],
        protected array $eventIds = [],
        protected int $startTimeStamp = 0,
    ) {
    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $set = ImportSet::AsQuery();
        $playerId = $this->playerId;
        $perPage = $this->perPage;
        $page = $this->page;
        $startTimeStamp = $this->startTimeStamp;
        $tournamentIds = implode(",", $this->tournamentIds);
        $eventIds = implode(",", $this->eventIds);

        return <<<END
        query Sets{
            player(id: $playerId) {
                id
                gamerTag
                sets(perPage: $perPage, page: $page, filters: {
                  isEventOnline: false,
                  showByes: false,
                  tournamentIds: [$tournamentIds],
                  eventIds: [$eventIds],
                  updatedAfter: $startTimeStamp,
                }) {
                  nodes $set
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
