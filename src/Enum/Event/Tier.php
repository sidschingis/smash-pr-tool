<?php

namespace App\Enum\Event;

enum Tier: string
{
    case S = 'S';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case NONE = '';
}
