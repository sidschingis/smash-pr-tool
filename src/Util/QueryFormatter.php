<?php

namespace App\Util;

class QueryFormatter
{
    public function formatField(string $input): string
    {
        return preg_replace('/[A-Z]/', '_$0', $input);
    }
}
