<?php

namespace App\Services;

use App\Models\LogModel;

class LogService
{
    public function __construct(private readonly LogModel $logs = new LogModel())
    {
    }

    public function info(string $channel, string $message, array $context = [], ?int $userId = null): void
    {
        $this->write('info', $channel, $message, $context, $userId);
    }

    public function warning(string $channel, string $message, array $context = [], ?int $userId = null): void
    {
        $this->write('warning', $channel, $message, $context, $userId);
    }

    public function error(string $channel, string $message, array $context = [], ?int $userId = null): void
    {
        $this->write('error', $channel, $message, $context, $userId);
    }

    private function write(string $level, string $channel, string $message, array $context, ?int $userId): void
    {
        $ip = null;
        $ua = null;

        // CLI'da ip/ua kesin NULL
        if (!is_cli()) {
            try {
                $request = service('request');
                if ($request) {
                    $ip = $request->getIPAddress() ?: null;

                    $agent = $request->getUserAgent();
                    $ua = $agent ? $agent->getAgentString() : null;
                }
            } catch (\Throwable $e) {}
            } else {
                $ip = null;
                $ua = null;
            }

        $this->logs->insert([
            'level'        => $level,
            'channel'      => $channel,
            'message'      => mb_substr($message, 0, 255),
            'context_json' => $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            'user_id'      => $userId,
            'ip'           => $ip,
            'user_agent'   => $ua,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function event(string $level, string $channel, string $event, array $context = [], ?int $userId = null): void
    {
        $context['_event'] = $event;
        $this->write($level, $channel, $event, $context, $userId);
    }
}
