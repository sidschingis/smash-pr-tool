<?php

namespace App\Objects\Ranking;

class ResultContainer
{
    /**
     * @param HeadToHead[] $headToHeads
     */
    public function __construct(
        public readonly string $tier,
        public readonly array  $headToHeads,
    ) {
    }

}
