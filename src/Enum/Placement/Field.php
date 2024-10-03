<?php

namespace App\Enum\Placement;

enum Field: string
{
    case PLAYER_ID = 'playerId';
    case EVENT_ID = 'eventId';
    case PLACEMENT = 'placement';
    case SCORE = 'score';
}
