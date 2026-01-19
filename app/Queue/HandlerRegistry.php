<?php

namespace App\Queue;

use RuntimeException;

class HandlerRegistry
{
    /** @var array<string, class-string<JobHandlerInterface>> */
    private array $map = [];

    public function __construct(?array $map = null)
    {
        if (is_array($map)) {
            $this->map = $map;
            return;
        }

        $this->map = $this->loadHandlers();
    }

    private function normalizeType(string $type): string
    {
        return strtolower(trim($type));
    }

    /**
     * Config\Queue->handlers yükle (CI context varsa), yoksa dosyadan fallback.
     */
    private function loadHandlers(): array
    {
        // 1) CI config helper ile dene (normalde worker burada)
        try {
            $cfg = config(\Config\Queue::class);
            $handlers = $cfg->handlers ?? [];
            if (is_array($handlers) && !empty($handlers)) {
                return $this->normalizeMap($handlers);
            }
        } catch (\Throwable $e) {
            // ignore -> fallback
        }

        // 2) Fallback: dosyayı doğrudan include et (CI BaseConfig lifecycle'a takılmadan)
        $path = APPPATH . 'Config/Queue.php';
        if (!is_file($path)) {
            return [];
        }

        require_once $path;

        // class var mı?
        if (!class_exists(\Config\Queue::class)) {
            return [];
        }

        // Reflection ile $handlers property değerini al
        try {
            $ref = new \ReflectionClass(\Config\Queue::class);
            $defaults = $ref->getDefaultProperties();
            $handlers = $defaults['handlers'] ?? [];
            if (is_array($handlers)) {
                return $this->normalizeMap($handlers);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return [];
    }

    private function normalizeMap(array $handlers): array
    {
        $out = [];
        foreach ($handlers as $k => $v) {
            $k = $this->normalizeType((string)$k);
            $out[$k] = $v;
        }
        return $out;
    }

    public function resolve(string $type): JobHandlerInterface
    {
        $type = $this->normalizeType($type);

        // 1) yoksa yeniden yükle (cache/boot farkı varsa)
        if (!isset($this->map[$type])) {
            $this->map = $this->loadHandlers();
        }

        if (!isset($this->map[$type])) {
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

    public function all(): array
    {
        return $this->map;
    }
}
