<?php
namespace Purpleharmonie\EventSystem\Trait;
use Psr\EventDispatcher\EventDispatcherInterface;

trait EventAwareTrait
{
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function dispatch(object $event): object
    {
        if ($this->eventDispatcher === null) {
            throw new \RuntimeException('Event dispatcher is not set.');
        }
        return $this->eventDispatcher->dispatch($event);
    }
}
