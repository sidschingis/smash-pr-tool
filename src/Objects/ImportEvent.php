<?php

namespace App\Objects;

use App\Objects\Set\ImportSet;

class ImportEvent
{
    /**
     * @param ImportSet[] $sets
     */
    public function __construct(
        public readonly int $id,
        public readonly string $eventName,
        public readonly string $tournamentName,
        public readonly string $region,
        public readonly int $startTime,
        public readonly int $nextPage,
        public readonly int $numEntrants = 0,
        public readonly array $sets = [],
    ) {
    }

    public static function AsQuery(int $perPage = 50, int $page = 1): string
    {
        $set = ImportSet::AsQuery();
        return <<<EOD
        {
            id
            name
            numEntrants
            tournament{
                name
                addrState
                startAt
            }
            sets (perPage: $perPage, page: $page) {
                pageInfo{
                    totalPages
                    page
                }
                nodes $set
            }
        }
        EOD;
    }
}
