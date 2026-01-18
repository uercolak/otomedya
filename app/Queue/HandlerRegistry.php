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

        $this->reloadFromConfig();
    }

    private function reloadFromConfig(): void
    {
        // En sağlam: direkt instance
        $cfg = new \Config\Queue();

        $handlers = $cfg->handlers ?? [];
        $this->map = is_array($handlers) ? $handlers : [];
    }

    private function normalizeType(string $type): string
    {
        // trailing space / case / newline vb farkları öldür
        return strtolower(trim($type));
    }

    public function resolve(string $type): JobHandlerInterface
    {
        $type = $this->normalizeType($type);

        // 1) normal map
        if (!isset($this->map[$type])) {
            // 2) config'i tekrar yükle (bazı CLI contextlerde ilk load boş gelebiliyor)
            $this->reloadFromConfig();
        }

        if (!isset($this->map[$type])) {
            // Debug: hangi key'ler var?
            $keys = array_keys($this->map);
            $keysStr = $keys ? implode(', ', $keys) : '(empty)';
            throw new RuntimeException("No handler registered for job type: {$type}. Registered: {$keysStr}");
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
        $type = $this->normalizeType($type);
        $this->map[$type] = $handlerClass;
    }

    public function all(): array
    {
        return $this->map;
    }
}
