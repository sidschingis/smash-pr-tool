<?php

namespace App\Enum\Set;

enum Field: string
{
    case ID = 'id';
    case WINNER_ID = 'winnerId';
    case LOSER_ID = 'loserId';
    case DISPLAY_SCORE = 'displayScore';
    case DATE = 'date';
    case EVENT_ID = 'eventId';
}
