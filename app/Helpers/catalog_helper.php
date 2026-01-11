<?php

function catalog_cfg(): \Config\Catalog
{
    return config('Catalog');
}

function job_type_label(string $type): string
{
    $cfg = catalog_cfg();
    return $cfg->jobTypeLabels[$type] ?? $type;
}

function job_status_label(string $status): string
{
    $cfg = catalog_cfg();
    return $cfg->jobStatusLabels[$status] ?? $status;
}

function job_status_explain(string $status): string
{
    $cfg = catalog_cfg();
    return $cfg->jobStatusExplain[$status] ?? 'İş durumu bilinmiyor.';
}

function log_level_label(string $level): string
{
    $cfg = catalog_cfg();
    return $cfg->logLevelLabels[$level] ?? $level;
}

function log_level_hint(string $level): string
{
    $cfg = catalog_cfg();
    return $cfg->logLevelHints[$level] ?? '';
}

function log_channel_label(string $channel): string
{
    $cfg = catalog_cfg();
    return $cfg->logChannelLabels[$channel] ?? $channel;
}

function log_channel_hint(string $channel): string
{
    $cfg = catalog_cfg();
    return $cfg->logChannelHints[$channel] ?? '';
}

function log_event_label(string $event): string
{
    $cfg = catalog_cfg();
    return $cfg->logEventLabels[$event] ?? $event;
}
