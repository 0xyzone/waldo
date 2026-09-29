<?php

/**
 * Discord Notification Helper
 * Usage: php scripts/notify_discord.php <success|failure> "Summary text"
 */
$status = strtolower($argv[1] ?? 'success');
$summary = $argv[2] ?? 'Task completed.';

$webhookUrl = getenv('DISCORD_WEBHOOK_URL') ?: 'https://discord.com/api/webhooks/1552940044489203752/o1hrQFuuEPbwND8DL-D005DXsdGcXLslyjYZxlZOMSvMrdHnwOgoou5vBatK_8zMWDY_';
$roleId = getenv('DISCORD_ROLE_ID') ?: '1520659760075243711';

$isSuccess = in_array($status, ['success', 'ok', 'done'], true);
$title = $isSuccess ? 'Task Completed Successfully' : 'Task Failed / Encountered Error';
$color = $isSuccess ? 0x22C55E : 0xEF4444; // Green or Red
$statusLabel = $isSuccess ? 'Success' : 'Failure';

date_default_timezone_set('Asia/Kathmandu');
$nowKathmandu = new DateTime('now', new DateTimeZone('Asia/Kathmandu'));

$payload = [
    'content' => "<@&{$roleId}>",
    'embeds' => [
        [
            'title' => $title,
            'description' => $summary,
            'color' => $color,
            'fields' => [
                [
                    'name' => 'Status',
                    'value' => $statusLabel,
                    'inline' => true,
                ],
                [
                    'name' => 'Timestamp (Kathmandu)',
                    'value' => $nowKathmandu->format('Y-m-d h:i:s A T'),
                    'inline' => true,
                ],
            ],
            'footer' => [
                'text' => 'Timezone: Asia/Kathmandu (UTC+05:45)',
            ],
            'timestamp' => $nowKathmandu->format(DateTime::ATOM),
        ],
    ],
];

$payloadJson = json_encode($payload);

$ch = curl_init($webhookUrl);
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
    fwrite(STDERR, "cURL Error: {$error}\n");
    exit(1);
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo "Notification sent successfully (HTTP {$httpCode}).\n";
    exit(0);
} else {
    fwrite(STDERR, "Discord API error: HTTP {$httpCode} - {$response}\n");
    exit(1);
}
