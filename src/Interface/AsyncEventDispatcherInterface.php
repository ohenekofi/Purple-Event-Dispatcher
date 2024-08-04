<?php

namespace Purpleharmonie\EventSystem\Interface;

interface AsyncEventDispatcherInterface
{
    /**
     * Dispatch an event asynchronously.
     *
     * @param object $event The event to dispatch
     * @param array $context Additional context for the event
     * @return mixed A job identifier or any relevant information about the queued job
     */
    public function dispatchAsync(object $event, array $context = []);

    /**
     * Check the status of an asynchronously dispatched event.
     *
     * @param mixed $jobIdentifier The identifier returned by dispatchAsync
     * @return string The status of the job (e.g., 'queued', 'processing', 'completed', 'failed')
     */
    public function getAsyncStatus($jobIdentifier): string;
}