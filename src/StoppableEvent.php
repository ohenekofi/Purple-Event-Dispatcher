<?php
namespace Purpleharmonie\EventSystem;
use Psr\EventDispatcher\StoppableEventInterface;

class StoppableEvent implements StoppableEventInterface
{
    private $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
