<?php
header('Content-Type: text/plain; charset=utf-8');

file_put_contents(__DIR__ . '/meta_cb.log',
    date('c') . " URI=" . ($_SERVER['REQUEST_URI'] ?? '') .
    " QS=" . ($_SERVER['QUERY_STRING'] ?? '') .
    " GET=" . json_encode($_GET, JSON_UNESCAPED_UNICODE) .
    "\n",
    FILE_APPEND
);

echo "OK\n";
echo "GET=" . json_encode($_GET, JSON_UNESCAPED_UNICODE) . "\n";