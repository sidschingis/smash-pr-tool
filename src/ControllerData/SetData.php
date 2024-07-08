<?php

namespace App\ControllerData;

use App\Entity\Set;

class SetData
{
    /**
     * @param EventData[] $eventInfos
     */
    public function __construct(
        public string $playerName,
        public array $eventInfos,
    ) {
    }
}
