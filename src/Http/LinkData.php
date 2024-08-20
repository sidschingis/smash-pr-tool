<?php

namespace App\Http;

class LinkData
{
    public function __construct(
        public string $url,
        public string $text,
    ) {
    }
}
