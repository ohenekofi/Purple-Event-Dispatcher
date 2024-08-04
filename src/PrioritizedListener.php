<?php
namespace Purpleharmonie\EventSystem;

class PrioritizedListener
{
    public $listener;
    public $priority;

    public function __construct(callable $listener, int $priority)
    {
        $this->listener = $listener;
        $this->priority = $priority;
    }
}