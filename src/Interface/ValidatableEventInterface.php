<?php
namespace Purpleharmonie\EventSystem\Interface;

interface ValidatableEventInterface
{
    public function isValid(): bool;
}