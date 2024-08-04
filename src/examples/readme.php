<?php
// Example usage
$listenerProvider =  $events->getListener();
$dispatcher = $events->getDispatch();


class ExampleEvent extends StoppableEvent
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 1: " . $event->message . "\n";
});

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 2: " . $event->message . "\n";
    $event->stopPropagation();
});

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 3: " . $event->message . "\n";
});

$event = new ExampleEvent("Hello, PSR-14!");
$dispatcher->dispatch($event);


#with piriority odering ============================================================
class ExampleEvent extends StoppableEvent
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 1 (Priority 0): " . $event->message . "\n";
}, 0);

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 2 (Priority 10): " . $event->message . "\n";
}, 10);

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 3 (Priority 5): " . $event->message . "\n";
}, 5);

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Listener 4 (Priority -5): " . $event->message . "\n";
    $event->stopPropagation();
}, -5);

$event = new ExampleEvent("Hello, Prioritized PSR-14!");
$dispatcher->dispatch($event);

#==============================PSR-3 Logger Integration,Event Subscribers, Event Queuing=========================
// Example usage
$listenerProvider =  $events->getListener();
$dispatcher = $events->getDispatch();


// Example usage:


class ExampleLogger implements LoggerInterface
{
    public function log($level,Stringable|string $message, array $context = array()): void {
        echo "[{$level}] {$message}\n";
    }

    public function emergency(Stringable|string $message, array $context = array()): void { $this->log('emergency', $message, $context); }
    public function alert(Stringable|string $message, array $context = array()): void { $this->log('alert', $message, $context); }
    public function critical(Stringable|string $message, array $context = array()): void { $this->log('critical', $message, $context); }
    public function error(Stringable|string $message, array $context = array()) : void{ $this->log('error', $message, $context); }
    public function warning(Stringable|string $message, array $context = array()) : void{ $this->log('warning', $message, $context); }
    public function notice(Stringable|string $message, array $context = array()) : void{ $this->log('notice', $message, $context); }
    public function info(Stringable|string $message, array $context = array()) : void{ $this->log('info', $message, $context); }
    public function debug(Stringable|string $message, array $context = array()) : void { $this->log('debug', $message, $context); }
}



class ExampleEvent extends StoppableEvent
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

class ExampleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExampleEvent::class => [
                ['onExampleEvent', 5],
                ['onExampleEventAgain', 10],
            ],
        ];
    }

    public function onExampleEvent(ExampleEvent $event)
    {
        echo "Subscriber: First listener\n";
    }

    public function onExampleEventAgain(ExampleEvent $event)
    {
        echo "Subscriber: Second listener\n";
    }
}

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Regular listener: " . $event->message . "\n";
}, 0);

$listenerProvider->addSubscriber(new ExampleSubscriber());

$event1 = new ExampleEvent("Hello, Advanced PSR-14!");
$dispatcher->dispatch($event1);

$event2 = new ExampleEvent("Queued event");
$dispatcher->queueEvent($event2);
$dispatcher->processQueue();


////===============event sub class and imporoved registry of events

// Example usage
$listenerProvider =  $events->getListener();
$dispatcher = $events->getDispatch();



// Example usage:
class ExampleEvent extends AbstractEvent
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

class SpecializedExampleEvent extends ExampleEvent
{
    public int $specialValue;

    public function __construct(string $message, int $specialValue)
    {
        parent::__construct($message);
        $this->specialValue = $specialValue;
    }
}

class ExampleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExampleEvent::class => [
                ['onExampleEvent', 5],
                ['onExampleEventAgain', 10],
            ],
            SpecializedExampleEvent::class => 'onSpecializedEvent',
        ];
    }

    public function onExampleEvent(ExampleEvent $event): void
    {
        echo "Subscriber: First listener for " . get_class($event) . "\n";
        echo "Message: {$event->message}\n";
    }

    public function onExampleEventAgain(ExampleEvent $event): void
    {
        echo "Subscriber: Second listener for " . get_class($event) . "\n";
    }

    public function onSpecializedEvent(SpecializedExampleEvent $event): void
    {
        echo "Subscriber: Specialized listener\n";
        echo "Message: {$event->message}, Special Value: {$event->specialValue}\n";
    }
}

// Testing the enhanced system


$subscriber = new ExampleSubscriber();
$listenerProvider->addSubscriber($subscriber);

$listenerProvider->addListener(ExampleEvent::class, function(ExampleEvent $event) {
    echo "Generic listener: " . $event->message . "\n";
}, 0);

$event1 = new ExampleEvent("Hello, Enhanced PSR-14!");
$dispatcher->dispatch($event1);

$event2 = new SpecializedExampleEvent("Specialized Event", 42);
$dispatcher->dispatch($event2);




#==================================================================================
// Example usage
class UserRegisteredEvent extends AbstractEvent
{
    public function __construct(public string $username) {}
}

class UserService
{
    use EventAwareTrait;

    public function registerUser(string $username): void
    {
        // User registration logic...
        $this->dispatch(new UserRegisteredEvent($username));
    }
}

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

// Usage example
$logger = new NullLogger();
$listenerProvider = new ListenerProvider($logger);
$eventDispatcher = new EventDispatcher($listenerProvider, $logger);

$conditionalDispatcher = new ConditionalEventDispatcher($eventDispatcher);
$conditionalDispatcher->addCondition(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    return strlen($event->username) > 3;
});

$eventCollector = new EventCollector();
$eventDispatcher->addMiddleware($eventCollector);

$userSubscriber = new UserSubscriber();
$listenerProvider->addSubscriber($userSubscriber);

$userService = new UserService();
$userService->setEventDispatcher($conditionalDispatcher);

$userService->registerUser("john");  // This will be dispatched
$userService->registerUser("jo");    // This won't be dispatched due to the condition

print_r($eventCollector->getCollectedEvents());






#====================================================================

use Purpleharmonie\EventSystem\EventDispatcher;
use Purpleharmonie\EventSystem\ListenerProvider;
use Purpleharmonie\EventSystem\Interface\ValidatableEventInterface;

class UserRegisteredEvent implements ValidatableEventInterface
{
    public function __construct(public string $username, public string $email) {}

    public function isValid(): bool
    {
        return !empty($this->username) && filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }
}

$listenerProvider = new ListenerProvider();
$eventDispatcher = new EventDispatcher($listenerProvider);

// Add a validator
$eventDispatcher->addValidator(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    return strlen($event->username) >= 3;
});

// Add a middleware
$eventDispatcher->addMiddleware(function($event) {
    if ($event instanceof UserRegisteredEvent) {
        $event->username = strtolower($event->username);
    }
    return $event;
});

// Add a listener
$listenerProvider->addListener(UserRegisteredEvent::class, function(UserRegisteredEvent $event, array $context) {
    echo "User registered: {$event->username} ({$event->email})\n";
    echo "Additional context: " . json_encode($context) . "\n";
});

// Dispatch an event
$event = new UserRegisteredEvent('John', 'john@example.com');
$eventDispatcher->dispatch($event, ['source' => 'web']);

// Remove a listener
$listenerProvider->removeListener(UserRegisteredEvent::class, function(UserRegisteredEvent $event) {
    // This listener will be removed
});

// Try to dispatch an invalid event
$invalidEvent = new UserRegisteredEvent('', 'invalid-email');
$eventDispatcher->dispatch($invalidEvent);  // This will not be dispatched due to validation failure

/*
These changes introduce the following new features:

Event validation: Events can implement the ValidatableEventInterface to provide custom validation logic. The EventDispatcher also allows adding additional validators.
Exception handling: The EventDispatcher now catches and logs exceptions thrown by listeners, preventing a single listener failure from stopping the entire dispatch process.
Event middleware: The EventDispatcher now supports middleware that can intercept and modify events before they reach listeners.
Listener removal: The ListenerProvider now has a removeListener method to remove specific listeners.
Additional context: The dispatch method now accepts an optional $context array that is passed to all listeners, allowing for additional information beyond the event object itself.
*/




#==============================================================
/*
a hook for asynchronous event handling without directly implementing it is a great approach. This allows users of the library to integrate their preferred asynchronous processing solution while keeping the core event system lean and performant.
*/


use Purpleharmonie\EventSystem\EventDispatcher;
use Purpleharmonie\EventSystem\ListenerProvider;
use Purpleharmonie\EventSystem\Interface\AsyncEventDispatcherInterface;

// Example implementation of AsyncEventDispatcherInterface
class ExampleAsyncDispatcher implements AsyncEventDispatcherInterface
{
    public function dispatchAsync(object $event, array $context = [])
    {
        // In a real implementation, this would queue the event for asynchronous processing
        // For this example, we'll just return a mock job identifier
        return uniqid('job_');
    }

    public function getAsyncStatus($jobIdentifier): string
    {
        // In a real implementation, this would check the status of the job
        // For this example, we'll just return a random status
        $statuses = ['queued', 'processing', 'completed', 'failed'];
        return $statuses[array_rand($statuses)];
    }
}

// Create event dispatcher and set async dispatcher
$listenerProvider = new ListenerProvider();
$eventDispatcher = new EventDispatcher($listenerProvider);
$asyncDispatcher = new ExampleAsyncDispatcher();
$eventDispatcher->setAsyncDispatcher($asyncDispatcher);

// Define an event
class UserRegisteredEvent
{
    public function __construct(public string $username, public string $email) {}
}

// Add a listener
$listenerProvider->addListener(UserRegisteredEvent::class, function(UserRegisteredEvent $event, array $context) {
    echo "User registered: {$event->username} ({$event->email})\n";
    echo "Context: " . json_encode($context) . "\n";
});

// Dispatch event asynchronously
$event = new UserRegisteredEvent('john_doe', 'john@example.com');
$jobId = $eventDispatcher->dispatchAsync($event, ['source' => 'web_signup']);

// Check job status
echo "Job status: " . $asyncDispatcher->getAsyncStatus($jobId) . "\n";



#using this librabry with other dependency containers such purpleharmonie dependancy injector component
//services.php
    //defining event dispatcher subscriber
    $services->set('example_subscriber', ExampleSubscriber::class) 
        ->asGlobal(true)
        ->asShared(true);    

    // Define a service using an inline factory for logger
    $services->set('logger', function ($container) {
        return  new Logger('event_dispatcher');
    })
    ->implements(LoggerInterface::class) 
    ->asGlobal(true)
    ->asShared(true);

// Define a service using an inline factory for listener
$services->set('listener_provider', function ($container) {
        $logger = $container->get('logger');
        return  new ListenerProvider($logger);
    })
    ->implements(ListenerProviderInterface::class) 
    ->asGlobal(true)
    ->asShared(true);

//defining event dispatcher
$services->set('event_dispatcher', EventDispatcher::class) 
    ->autowire()
    ->asGlobal(true)
    ->asShared(true);