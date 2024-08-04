<?php

namespace Purpleharmonie\EventSystem;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Purpleharmonie\EventSystem\Interface\ValidatableEventInterface;
use Purpleharmonie\EventSystem\Interface\AsyncEventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    private ListenerProviderInterface $listenerProvider;
    private LoggerInterface $logger;
    private array $eventQueue = [];
    private array $eventMiddleware = [];
    private array $validators = [];
    private ?AsyncEventDispatcherInterface $asyncDispatcher = null;

    public function __construct(ListenerProviderInterface $listenerProvider, LoggerInterface $logger = null)
    {
        $this->listenerProvider = $listenerProvider;
        $this->logger = $logger ?? new NullLogger();
    }

    public function setAsyncDispatcher(AsyncEventDispatcherInterface $asyncDispatcher): void
    {
        $this->asyncDispatcher = $asyncDispatcher;
    }

    public function dispatch(object $event, array $context = [])
    {
        $eventClass = get_class($event);
        $this->logger->info("Dispatching event: {$eventClass}");

        if ($event instanceof ValidatableEventInterface) {
            if (!$this->validateEvent($event)) {
                $this->logger->warning("Event validation failed for {$eventClass}");
                return $event;
            }
        }

        $event = $this->applyMiddleware($event);

        if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
            $this->logger->info("Event propagation stopped by middleware for {$eventClass}");
            return $event;
        }

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                $this->logger->info("Event propagation stopped for {$eventClass}");
                break;
            }
            
            try {
                $listener($event, $context);
            } catch (\Throwable $e) {
                $this->logger->error("Error in listener: " . $e->getMessage(), [
                    'exception' => $e,
                    'event' => $eventClass
                ]);
            }
        }

        $this->logger->info("Finished dispatching event: {$eventClass}");
        return $event;
    }

    public function dispatchAsync(object $event, array $context = [])
    {
        if ($this->asyncDispatcher === null) {
            throw new \RuntimeException('Async dispatcher not set. Use setAsyncDispatcher() to set one.');
        }

        $eventClass = get_class($event);
        $this->logger->info("Dispatching event asynchronously: {$eventClass}");

        if ($event instanceof ValidatableEventInterface) {
            if (!$this->validateEvent($event)) {
                $this->logger->warning("Event validation failed for {$eventClass}");
                return null;
            }
        }

        $event = $this->applyMiddleware($event);

        if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
            $this->logger->info("Event propagation stopped by middleware for {$eventClass}");
            return null;
        }

        return $this->asyncDispatcher->dispatchAsync($event, $context);
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->eventMiddleware[] = $middleware;
    }

    private function applyMiddleware(object $event): object
    {
        foreach ($this->eventMiddleware as $middleware) {
            $event = $middleware($event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    public function addValidator(string $eventClass, callable $validator): void
    {
        $this->validators[$eventClass][] = $validator;
    }

    private function validateEvent(ValidatableEventInterface $event): bool
    {
        $eventClass = get_class($event);
        if (isset($this->validators[$eventClass])) {
            foreach ($this->validators[$eventClass] as $validator) {
                if (!$validator($event)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function queueEvent(object $event): void
    {
        $this->eventQueue[] = $event;
        $this->logger->info("Event queued: " . get_class($event));
    }

    public function processQueue(): void
    {
        $this->logger->info("Processing event queue");
        foreach ($this->eventQueue as $event) {
            $this->dispatch($event);
        }
        $this->eventQueue = [];
        $this->logger->info("Event queue processed");
    }
}