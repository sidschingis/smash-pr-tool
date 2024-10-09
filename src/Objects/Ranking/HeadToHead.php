<?php

namespace App\Objects\Ranking;

class HeadToHead
{
    public function __construct(
        public readonly string $name,
        public readonly int $count,
    ) {
    }
}
