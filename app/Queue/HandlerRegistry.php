<?php

namespace App\Queue;

use RuntimeException;

class HandlerRegistry
{
    /** @var array<string, class-string<JobHandlerInterface>> */
    private array $map;

    public function __construct(?array $map = null)
    {
        if (is_array($map)) {
            $this->map = $map;
            return;
        }

        // ✅ config('Queue') yerine direkt config instance (daha stabil)
        $cfg = new \Config\Queue();
        $this->map = is_array($cfg->handlers ?? null) ? $cfg->handlers : [];
    }

    public function resolve(string $type): JobHandlerInterface
    {
        if (!isset($this->map[$type])) {
            throw new RuntimeException("No handler registered for job type: {$type}");
        }

        $class = $this->map[$type];

        if (!class_exists($class)) {
            throw new RuntimeException("Handler class does not exist: {$class}");
        }

        $handler = new $class();

        if (!($handler instanceof JobHandlerInterface)) {
            throw new RuntimeException("Handler must implement JobHandlerInterface: {$class}");
        }

        return $handler;
    }

    public function all(): array
    {
        return $this->map;
    }
}
