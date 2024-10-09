<?php

namespace App\Enum\Season;

enum Field: string
{
    case ID = 'id';
    case NAME = 'name';
    case START_DATE = 'startDate';
    case END_DATE = 'endDate';
}
