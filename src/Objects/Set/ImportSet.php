<?php

namespace App\Objects\Set;

use App\Entity\Set;
use App\Objects\Tournament;

class ImportSet
{
    public function __construct(
        public readonly Set $set,
        public readonly string $winnerTag,
        public readonly string $loserTag,
    ) {
    }

    public static function AsQuery(): string
    {
        $tournament = Tournament::AsQuery();

        return <<<END
        {
            id
            displayScore
            slots {
                entrant {
                name
                participants {
                    player {
                        id
                    }
                }
                }
            }
            event {
                id
                name
                tournament $tournament
            }
        }
        END;
    }
}
