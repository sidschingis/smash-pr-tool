<?php

namespace App\Queries;

use App\GraphQL\Query\AbstractQuery;

class QueryName extends AbstractQuery
{
    private const OPERATION = "";

    /**
     * Implements AbstractQuery
     */
    protected function getQuery(): string
    {
        return "";
    }

    /**
     * Implements AbstractQuery
     */
    protected function getVariables(): array
    {
        return [

        ];
    }

    /**
     * Implements AbstractQuery
     */
    protected function getOperation(): string
    {
        return self::OPERATION;
    }
}
