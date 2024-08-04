# Purpleharmonie Event System

## Table of Contents
1. [Introduction](#introduction)
2. [Features](#features)
3. [Installation](#installation)
4. [Basic Usage](#basic-usage)
5. [Advanced Concepts](#advanced-concepts)
   - [Event Subscribers](#event-subscribers)
   - [Prioritized Listeners](#prioritized-listeners)
   - [Stoppable Events](#stoppable-events)
   - [Event Validation](#event-validation)
   - [Middleware](#middleware)
   - [Conditional Event Dispatching](#conditional-event-dispatching)
   - [Asynchronous Event Handling](#asynchronous-event-handling)
6. [Integration with Dependency Injection](#integration-with-dependency-injection)
7. [Best Practices](#best-practices)
8. [API Reference](#api-reference)

## Introduction

Purpleharmonie Event System is a flexible and powerful event dispatching library for PHP applications. It provides a robust implementation of the PSR-14 Event Dispatcher standard, along with additional features for advanced use cases.

## Features

- PSR-14 compliant event dispatcher
- Support for event subscribers
- Prioritized event listeners
- Stoppable events
- Event validation
- Middleware support
- Conditional event dispatching
- Asynchronous event handling (hook)
- PSR-3 Logger integration
- Easy integration with dependency injection containers

## Installation

You can install the Purpleharmonie Event System using Composer:

```bash
composer require purpleharmonie/event-system
```

## Basic Usage

Here's a simple example of how to use the event system:

```php
use Purpleharmonie\EventSystem\EventDispatcher;
use Purpleharmonie\EventSystem\ListenerProvider;

// Create the listener provider and event dispatcher
$listenerProvider = new ListenerProvider();
$eventDispatcher = new EventDispatcher($listenerProvider);

// Define an event
class UserRegisteredEvent
{
    public function __construct(public string $username) {}
}

// Add a listener
$listenerProvider->addListener(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    echo "User registered: {$event->username}\n";
});

// Dispatch an event
$event = new UserRegisteredEvent('john_doe');
$eventDispatcher->dispatch($event);
```

## Advanced Concepts

### Event Subscribers

Event subscribers allow you to group multiple event listeners in a single class:

```php
use Purpleharmonie\EventSystem\Interface\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => [
                ['onUserRegistered', 10],
                ['sendWelcomeEmail', 5]
            ]
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        echo "User registered: {$event->username}\n";
    }

    public function sendWelcomeEmail(UserRegisteredEvent $event): void
    {
        echo "Sending welcome email to {$event->username}\n";
    }
}

// Usage
$listenerProvider->addSubscriber(new UserSubscriber());
```

### Prioritized Listeners

You can set priorities for listeners to control their execution order:

```php
$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "High priority listener\n";
}, 10);

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Low priority listener\n";
}, -10);
```

### Stoppable Events

Stoppable events allow you to prevent further event propagation:

```php
use Purpleharmonie\EventSystem\StoppableEvent;

class StoppableExampleEvent extends StoppableEvent
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

$listenerProvider->addListener(StoppableExampleEvent::class, function(StoppableExampleEvent $event) {
    echo "First listener\n";
    $event->stopPropagation();
});

$listenerProvider->addListener(StoppableExampleEvent::class, function(StoppableExampleEvent $event) {
    echo "This won't be called if propagation is stopped\n";
});
```

### Event Validation

You can implement the `ValidatableEventInterface` to add custom validation logic to your events:

```php
use Purpleharmonie\EventSystem\Interface\ValidatableEventInterface;

class UserRegisteredEvent implements ValidatableEventInterface
{
    public function __construct(public string $username, public string $email) {}

    public function isValid(): bool
    {
        return !empty($this->username) && filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }
}

// Add a custom validator
$eventDispatcher->addValidator(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    return strlen($event->username) >= 3;
});
```

### Middleware

Middleware allows you to intercept and modify events before they reach the listeners:

```php
$eventDispatcher->addMiddleware(function($event) {
    if ($event instanceof UserRegisteredEvent) {
        $event->username = strtolower($event->username);
    }
    return $event;
});
```

### Conditional Event Dispatching

The `ConditionalEventDispatcher` allows you to add conditions for event dispatching:

```php
$conditionalDispatcher = new ConditionalEventDispatcher($eventDispatcher);
$conditionalDispatcher->addCondition(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    return strlen($event->username) > 3;
});
```

### Asynchronous Event Handling

You can implement the `AsyncEventDispatcherInterface` to handle events asynchronously:

```php
use Purpleharmonie\EventSystem\Interface\AsyncEventDispatcherInterface;

class ExampleAsyncDispatcher implements AsyncEventDispatcherInterface
{
    public function dispatchAsync(object $event, array $context = [])
    {
        // Queue the event for asynchronous processing
        return uniqid('job_');
    }

    public function getAsyncStatus($jobIdentifier): string
    {
        // Check and return the status of the async job
    }
}

$asyncDispatcher = new ExampleAsyncDispatcher();
$eventDispatcher->setAsyncDispatcher($asyncDispatcher);

// Dispatch asynchronously
$jobId = $eventDispatcher->dispatchAsync($event, ['source' => 'web_signup']);
```

## Integration with Dependency Injection

Here's an example of how to integrate the event system with a dependency injection container:

```php
// services.php
$services->set('example_subscriber', ExampleSubscriber::class)
    ->asGlobal(true)
    ->asShared(true);

$services->set('logger', function ($container) {
    return new Logger('event_dispatcher');
})
->implements(LoggerInterface::class)
->asGlobal(true)
->asShared(true);

$services->set('listener_provider', function ($container) {
    $logger = $container->get('logger');
    return new ListenerProvider($logger);
})
->implements(ListenerProviderInterface::class)
->asGlobal(true)
->asShared(true);

$services->set('event_dispatcher', EventDispatcher::class)
    ->autowire()
    ->asGlobal(true)
    ->asShared(true);
```

## Best Practices

1. Keep events small and focused on a single aspect of your application.
2. Use event subscribers for organizing related listeners.
3. Utilize priorities to ensure proper execution order of listeners.
4. Implement validation for critical events to ensure data integrity.
5. Use middleware for cross-cutting concerns that apply to multiple events.
6. Consider using asynchronous event handling for time-consuming operations.

## API Reference

For a complete API reference, please  generate API documentation using a tool like phpDocumentor.