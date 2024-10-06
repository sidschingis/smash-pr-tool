<?php

namespace App\Queries\Placement;

use App\Entity\Placement;
use App\GraphQL\Query\AbstractQuery;
use App\Objects\ImportPlacement;
use App\Util\JsonSerializer;

class PlacementsForEvent extends AbstractQuery
{
    private const OPERATION = "Placements";

    public static function JsonToImportData(string $json): ImportPlacement
    {
        $data = (new JsonSerializer())->deserialize($json);

        $eventInfo = $data?->data?->event ?? null;
        $eventId = $eventInfo?->id ?? 0;

        $standings = $eventInfo?->standings ?? null;

        $pageInfo = $standings?->pageInfo ?? null;
        $currentPage = $pageInfo?->page ?: 0;
        $totalPages = $pageInfo?->totalPages ?: 0;
        $nextPage =  $currentPage < $totalPages ? $currentPage + 1 : 0;

        $placements = $standings?->nodes ?? [];
        $importEntities = [];

        foreach ($placements as $node) {
            $importEntities[] = new Placement(
                playerId: $node->player->id,
                eventId: $eventId,
                placement: $node->placement,
            );
        }

        $importData =  new ImportPlacement(
            nextPage:$nextPage,
            placements: $importEntities,
        );

        return $importData;
    }

    public function __construct(
        protected int $eventId,
        protected int $standingPage = 1,
    ) {
    }

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        $operation = $this->getOperation();
        return <<<EOD
         query $operation{
            event(id: $this->eventId) {
                id
                standings(
                    query: {
                        page: $this->standingPage
                        perPage: 100,

                    }
                ){
                    pageInfo{
                        totalPages
                        page
                    }
                    nodes{
                        id
                        placement
                        player {
                            id
                            gamerTag
                        }
                    }
                }
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
