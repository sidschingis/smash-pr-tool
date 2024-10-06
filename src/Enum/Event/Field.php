<?php

namespace App\Enum\Event;

enum Field: string
{
    case ID = 'id';
    case DATE = 'date';
    case EVENT_NAME = 'eventName';
    case TOURNAMENT_NAME = 'tournamentName';
    case ENTRANTS = 'entrants';
    case NOTABLES = 'notables';
    case TIER = 'tier';
    case REGION = 'region';
}
