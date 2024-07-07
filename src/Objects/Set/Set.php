<?php

namespace App\Objects\Set;

class Set
{
    public function __construct(
        public readonly int $id,
        public readonly int $winnerId,
        public readonly int $loserId,
        public readonly int $winnerScore,
        public readonly int $loserScore,
        public readonly int $eventId,
        public readonly int $tournamentId,
    ) {
    }
}
