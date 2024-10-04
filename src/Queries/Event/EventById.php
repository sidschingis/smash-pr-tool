<?php

namespace App\Queries\Event;

use App\GraphQL\Query\AbstractQuery;
use App\Objects\ImportEvent;
use App\Queries\Player\SetsForPlayer;
use App\Util\JsonSerializer;

class EventById extends AbstractQuery
{
    private const OPERATION = "EventById";

    public static function JsonToImportData(string $json): ?ImportEvent
    {
        $data = (new JsonSerializer())->deserialize($json);
        $eventNode = $data?->data?->event ?? null;
        $sets = $eventNode?->sets ?? null;
        $nodes = $sets?->nodes ?? [];
        $eventInfos = SetsForPlayer::GetEventInfos($nodes);

        $event = reset($eventInfos);

        $tournament = $eventNode?->tournament ?? null;
        $region = $tournament?->addrState ?? '';
        $startTime = $tournament?->startAt ?? 0;

        $pageInfo = $sets?->pageInfo ?? null;
        $currentPage = $pageInfo?->page ?: 0;
        $totalPages = $pageInfo?->totalPages ?: 0;
        $nextPage =  $currentPage < $totalPages ? $currentPage + 1 : 0;

        $importEvent = new ImportEvent(
            id: $eventNode->id,
            eventName: $eventNode->name,
            tournamentName: $tournament->name,
            region: $region,
            startTime: $startTime,
            nextPage: $nextPage,
            sets: $event === false ? [] : $event->sets,
        );

        return $importEvent;
    }

    public function __construct(
        protected int $eventId,
        protected int $setPage = 1,
    ) {
    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $operation = $this->getOperation();
        $event = ImportEvent::AsQuery(
            page: $this->setPage,
        );
        return <<<EOD
        query $operation{
            event(id: $this->eventId) $event
        }
        EOD;
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
