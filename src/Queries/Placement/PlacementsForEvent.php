<?php

namespace App\Queries\Placement;

use App\GraphQL\Query\AbstractQuery;

class PlacementsForEvent extends AbstractQuery
{
    private const OPERATION = "Placements";

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
