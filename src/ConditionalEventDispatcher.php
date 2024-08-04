<?php
namespace Purpleharmonie\EventSystem;
use Psr\EventDispatcher\EventDispatcherInterface;

class ConditionalEventDispatcher implements EventDispatcherInterface
{
    private EventDispatcherInterface $innerDispatcher;
    private array $conditions = [];

    public function __construct(EventDispatcherInterface $innerDispatcher)
    {
        $this->innerDispatcher = $innerDispatcher;
    }

    public function addCondition(string $eventClass, callable $condition): void
    {
        $this->conditions[$eventClass][] = $condition;
    }

    public function dispatch(object $event): object
    {
        $eventClass = get_class($event);
        if (isset($this->conditions[$eventClass])) {
            foreach ($this->conditions[$eventClass] as $condition) {
                if (!$condition($event)) {
                    return $event;
                }
            }
        }
        return $this->innerDispatcher->dispatch($event);
    }
}