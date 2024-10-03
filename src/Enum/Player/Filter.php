<?php

namespace App\Enum\Player;

enum Filter: string
{
    case ID = 'idFilter';
    case TAG = 'tagFilter';
    case REGION = 'regionFilter';
}
