<?php

namespace App\Objects\Ranking;

class PlacementContainer
{
    /**
     * @param Placement[] $placements
     */
    public function __construct(
        public readonly string $tier,
        public readonly array  $placements,
    ) {
    }

}
