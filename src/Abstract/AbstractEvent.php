<?php
namespace Purpleharmonie\EventSystem\Abstract;
use Psr\EventDispatcher\StoppableEventInterface;
use Purpleharmonie\EventSystem\Interface\EventInterface;

abstract class AbstractEvent implements EventInterface, StoppableEventInterface
{
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function getName(): string
    {
        return static::class;
    }
}