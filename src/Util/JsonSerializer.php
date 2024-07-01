<?php

namespace App\Util;

class JsonSerializer
{
    public function __construct(
        private int $flags = 0,
    ) {
    }

    public function serialize(mixed $input): string
    {
        return json_encode($input, $this->flags);
    }

    public function deserialize(string $input): ?object
    {
        return json_decode(
            $input,
            flags:$this->flags
        );
    }
}
