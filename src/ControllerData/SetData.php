<?php

namespace App\ControllerData;

class SetData
{
    /**
     * @param EventData[] $eventInfos
     */
    public function __construct(
        public string $playerName,
        public string $playerId,
        public array $eventInfos,
    ) {
    }
}
