<?php

namespace App\Objects;

use App\GraphQL\Query\AbstractQuery;
use DateTime;

class Tournament
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $startTime,
    ) {
    }

    public static function AsQuery(): string
    {
        return <<<END
        {
            id
            name
            startAt
        }
        END;
    }
}
