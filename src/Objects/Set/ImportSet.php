<?php

namespace App\Objects\Set;

use App\Entity\Set;

class ImportSet
{
    public function __construct(
        public readonly Set $set,
        public readonly string $winnerTag,
        public readonly string $loserTag,
    ) {
    }
}
