<?php

namespace App\GraphQL\Query;

abstract class AbstractQuery implements Query
{
    /**
     * Implements Query.
     */
    public function toBodyArray(): array
    {
        $query = $this->getQuery();
        $variables = $this->getVariables();
        $operation = $this->getOperation();

        $body = [
            'query' => $query,
            'operationName' => $operation,
            'variables' => $variables,
        ];

        return $body;
    }

    abstract protected function getQuery(): string;

    /**
     * @return mixed[]
     */
    abstract protected function getVariables(): array;

    abstract protected function getOperation(): string;
}
