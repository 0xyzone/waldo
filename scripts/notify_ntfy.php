<?php

/**
 * ntfy.sh Push Notification Helper
 * Usage: php scripts/notify_ntfy.php <success|failure> "Summary text"
 */
$status = strtolower($argv[1] ?? 'success');
$summary = $argv[2] ?? 'Task completed.';

// Determine topic and server from environment or fallback
$topic = getenv('NTFY_TOPIC');
$server = getenv('NTFY_SERVER') ?: 'https://ntfy.sh';

if (! $topic && file_exists(__DIR__.'/../.env')) {
    $envLines = file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#')) {
            continue;
        }
        if (str_starts_with($line, 'NTFY_TOPIC=')) {
            $topic = trim(substr($line, 11), " \t\n\r\0\x0B\"'");
        }
        if (str_starts_with($line, 'NTFY_SERVER=')) {
            $server = trim(substr($line, 12), " \t\n\r\0\x0B\"'");
        }
    }
}

$topic = $topic ?: 'waldo_tasks_alerts';
$server = rtrim($server, '/');

$isSuccess = in_array($status, ['success', 'ok', 'done'], true);
$title = $isSuccess ? '✅ Waldo: Task Completed' : '❌ Waldo: Task Failed / Error';
$priority = $isSuccess ? 4 : 5; // 4 = high, 5 = urgent
$tags = $isSuccess ? ['white_check_mark', 'hammer_and_wrench'] : ['x', 'warning'];

date_default_timezone_set('Asia/Kathmandu');
$nowKathmandu = (new DateTime('now', new DateTimeZone('Asia/Kathmandu')))->format('Y-m-d h:i:s A T');

$body = "{$summary}\n\n🕒 Timestamp: {$nowKathmandu}";

$payload = [
    'topic' => $topic,
    'title' => $title,
    'message' => $body,
    'priority' => $priority,
    'tags' => $tags,
    'click' => 'https://waldo/kamkaj',
];

$payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$ch = curl_init("{$server}");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payloadJson,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: '.strlen($payloadJson),
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    fwrite(STDERR, "ntfy cURL Error: {$error}\n");
    exit(1);
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo "ntfy notification sent successfully to topic '{$topic}' (HTTP {$httpCode}).\n";
    exit(0);
} else {
    fwrite(STDERR, "ntfy error: HTTP {$httpCode} - {$response}\n");
    exit(1);
}
