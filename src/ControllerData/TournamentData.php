<?php

namespace App\ControllerData;

use App\Objects\Tournament;

class TournamentData
{
    /**
     * @param Tournament[] $tournaments
     */
    public function __construct(
        public string $name,
        public array $tournaments,
    ) {
    }
}
