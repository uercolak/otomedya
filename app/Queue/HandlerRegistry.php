<?php

namespace App\Queue;

use RuntimeException;

class HandlerRegistry
{
    /** @var array<string, class-string<JobHandlerInterface>> */
    private array $map = [];

    public function __construct(?array $map = null)
    {
        // Test/override
        if (is_array($map)) {
            $this->map = $map;
            return;
        }

        // ✅ En sağlam: class ile config çek
        $cfg = config(\Config\Queue::class); // => \Config\Queue (bizim dosya)

        $handlers = $cfg->handlers ?? [];
        $this->map = is_array($handlers) ? $handlers : [];
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

    public function register(string $type, string $handlerClass): void
    {
        $this->map[$type] = $handlerClass;
    }

    public function all(): array
    {
        return $this->map;
    }
}
