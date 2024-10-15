<?php

namespace App\Enum\Placement;

enum Filter: string
{
    case PLAYER = 'playerId';
    case EVENT = 'eventId';
}
