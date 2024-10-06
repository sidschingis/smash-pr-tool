<?php

namespace App\Objects;

use App\Entity\Placement;

class ImportPlacement
{

    /**
     * @param Placement[]  $placements
     */
    public function __construct(
        public readonly int $nextPage,
        public readonly array $placements,
    ) {
    }

}