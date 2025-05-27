<?php

namespace App\Objects\Ranking;

class Placement
{
    public function __construct(
        public readonly string $name,
        public readonly int $placement,
        public readonly int $score,
        public readonly string $tier,
    ) {
    }
}
