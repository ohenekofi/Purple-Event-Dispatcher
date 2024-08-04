<?php
namespace Purpleharmonie\EventSystem;

use Psr\EventDispatcher\ListenerProviderInterface;
use Purpleharmonie\EventSystem\Interface\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function addListener(string $eventClass, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }
        $this->listeners[$eventClass][] = ['listener' => $listener, 'priority' => $priority];
        $this->logger->info("Listener added for event: {$eventClass}");
    }

    public function removeListener(string $eventClass, callable $listener): void
    {
        if (isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = array_filter(
                $this->listeners[$eventClass],
                function($item) use ($listener) {
                    return $item['listener'] !== $listener;
                }
            );
            $this->logger->info("Listener removed for event: {$eventClass}");
        }
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber::getSubscribedEvents() as $eventClass => $params) {
            $this->addSubscriberListener($subscriber, $eventClass, $params);
        }
        $this->logger->info("Subscriber added: " . get_class($subscriber));
    }

    private function addSubscriberListener(EventSubscriberInterface $subscriber, string $eventClass, $params): void
    {
        if (is_string($params)) {
            $this->addListener($eventClass, [$subscriber, $params]);
        } elseif (is_array($params)) {
            if (is_string($params[0])) {
                $this->addListener($eventClass, [$subscriber, $params[0]], $params[1] ?? 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventClass, [$subscriber, $listener[0]], $listener[1] ?? 0);
                }
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        $listeners = [];

        foreach ($this->listeners as $class => $classListeners) {
            if ($event instanceof $class) {
                $listeners = array_merge($listeners, $classListeners);
            }
        }

        // Sort listeners by priority
        usort($listeners, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        // Return only the callable listeners
        return array_column($listeners, 'listener');
    }
}
/*
namespace Purpleharmonie\EventSystem;

use Psr\EventDispatcher\ListenerProviderInterface;
use Purpleharmonie\EventSystem\PrioritizedListener;
use Purpleharmonie\EventSystem\Interface\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class ListenerProvider implements ListenerProviderInterface
{
    private array $listeners = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function addListener(string $eventClass, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }
        $this->listeners[$eventClass][] = ['listener' => $listener, 'priority' => $priority];
        $this->logger->info("Listener added for event: {$eventClass}");
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber::getSubscribedEvents() as $eventClass => $params) {
            $this->addSubscriberListener($subscriber, $eventClass, $params);
        }
        $this->logger->info("Subscriber added: " . get_class($subscriber));
    }

    private function addSubscriberListener(EventSubscriberInterface $subscriber, string $eventClass, $params): void
    {
        if (is_string($params)) {
            $this->addListener($eventClass, [$subscriber, $params]);
        } elseif (is_array($params)) {
            if (is_string($params[0])) {
                $this->addListener($eventClass, [$subscriber, $params[0]], $params[1] ?? 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventClass, [$subscriber, $listener[0]], $listener[1] ?? 0);
                }
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        $listeners = [];

        foreach ($this->listeners as $class => $classListeners) {
            if ($event instanceof $class) {
                $listeners = array_merge($listeners, $classListeners);
            }
        }

        // Sort listeners by priority
        usort($listeners, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        // Return only the callable listeners
        return array_column($listeners, 'listener');
    }
}
*/