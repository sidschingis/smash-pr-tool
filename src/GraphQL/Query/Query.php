<?php

namespace App\GraphQL\Query;

interface Query
{
    /**
     * @return mixed[]
     */
    public function toBodyArray(): array;
}
