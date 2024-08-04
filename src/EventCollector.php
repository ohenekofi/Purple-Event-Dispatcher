<?php
namespace Purpleharmonie\EventSystem;

class EventCollector
{
    private array $collectedEvents = [];

    public function __invoke(object $event): object
    {
        $this->collectedEvents[] = $event;
        return $event;
    }

    public function getCollectedEvents(): array
    {
        return $this->collectedEvents;
    }

    public function clear(): void
    {
        $this->collectedEvents = [];
    }
}