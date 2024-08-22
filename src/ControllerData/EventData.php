<?php

namespace App\ControllerData;

use App\Objects\Set\ImportSet;

class EventData
{
    /**
     * @param ImportSet[] $sets
     */
    public function __construct(
        public string $tournamentName,
        public string $eventName,
        public int $eventId,
        public array $sets,
    ) {
    }
}
