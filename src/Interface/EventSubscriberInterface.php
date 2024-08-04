<?php
namespace Purpleharmonie\EventSystem\Interface;

interface EventSubscriberInterface
{
    public static function getSubscribedEvents(): array;
}