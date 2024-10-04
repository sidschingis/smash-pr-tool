<?php

namespace App\Action\Event;

use App\Action\Sets\ImportSets;
use App\Entity\Event;
use App\Objects\ImportEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ImportEvents
{
    public function __construct(
        private EntityManagerInterface $eventManager,
        private ImportSets $setImporter,
    ) {
    }

    /**
     * @param ImportEvent[] $importEvents
     */
    public function importEvents(array $importEvents): bool
    {
        $setImporter = $this->setImporter;

        $eventManager = $this->eventManager;

        /**
         * Import Events
         */
        $newEvents = $this->filterEvents($importEvents);
        foreach ($newEvents as $event) {
            $eventManager->persist($event);
        }
        $eventManager->flush();

        /**
         * Import Sets
         */
        $importSets = [];
        foreach ($importEvents as $importEvent) {
            $importSets = array_merge($importSets, $importEvent->sets);
        }
        $setImporter->importSets($importSets);

        return true;
    }


    /**
     * @param ImportEvent[] $importEvents
     * @return Event[]
     */
    private function filterEvents(array $importEvents): array
    {
        $eventManager = $this->eventManager;

        $ids = [];
        foreach ($importEvents as $importEvent) {
            $ids[] = $importEvent->id;
        }

        $repo = $eventManager->getRepository(Event::class);

        /** @var Event[] */
        $existingEntities = $repo->findBy(
            criteria:[
                'id' => $ids,
            ],
        );

        $existingIds = [];
        foreach ($existingEntities as $existing) {
            $id = $existing->getId();
            $existingIds[$id] = $id;
        }

        $new = [];

        foreach ($importEvents as  $importEvent) {
            $id = $importEvent->id;
            if (array_key_exists($id, $existingIds)) {
                continue;
            }

            $date = (new DateTimeImmutable())->setTimestamp($importEvent->startTime);

            $new[$id] = new Event(
                id: $id,
                eventName: $importEvent->eventName,
                tournamentName: $importEvent->tournamentName,
                region: $importEvent->region,
                date: $date,
            );
        }

        return $new;
    }
}
