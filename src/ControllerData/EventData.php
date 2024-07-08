<?php

namespace App\ControllerData;

use App\Entity\Set;

class EventData
{
    /**
     * @param Set[] $sets
     */
    public function __construct(
        public string $tournamentName,
        public string $eventName,
        public int $eventId,
        public array $sets,
    ) {
    }
}
