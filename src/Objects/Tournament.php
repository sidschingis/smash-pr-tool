<?php

namespace App\Objects;

class Tournament
{
    /**
     * @param EventData[] $events
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $startTime,
        public readonly array $events = [],
    ) {
    }

    public static function AsQuery(): string
    {
        return <<<END
        {
            id
            name
            startAt
            events(limit: 50){
                id
                name
            }
        }
        END;
    }
}
