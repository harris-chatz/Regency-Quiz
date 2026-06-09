<?php

/**
 * Apifon credential identification harness.
 *
 * Runs multiple authentication attempts against Apifon's API and reports
 * the response code + headers for each. The pattern of responses tells us
 * EXACTLY what kind of credential we have.
 *
 * Run from inside the app container:
 *   docker compose exec app php /var/www/html/scripts/apifon-probe.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$token = 'EmHav07ZiIqrjgFgzWa32S1uFDpalvKCtyrIStoDUgxRrtjngloaGpQHzYDxaQYi';
$dummy = '0000000000000000000000000000000000000000000000000000000000000000';

function callApi(string $label, string $url, string $method, array $headers, string $body = ''): void
{
    echo "\n=== {$label} ===\n";
    echo "URL: {$method} {$url}\n";
    if (!empty($headers)) {
        echo "Headers:\n";
        foreach ($headers as $h) echo "  {$h}\n";
    }
    if ($body !== '') {
        echo "Body: " . substr($body, 0, 120) . (strlen($body) > 120 ? '...' : '') . "\n";
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body,
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "  ERROR: {$error}\n";
        return;
    }

    $responseHeaders = substr($raw, 0, $headerSize);
    $responseBody = substr($raw, $headerSize);

    // Show only the auth/error-relevant headers
    $relevant = [];
    foreach (explode("\n", $responseHeaders) as $line) {
        $line = trim($line);
        if (preg_match('/^(www-authenticate|x-apifon|content-type|status):/i', $line)) {
            $relevant[] = $line;
        }
    }

    echo "  HTTP {$status}\n";
    foreach ($relevant as $h) echo "  → {$h}\n";

    $shortBody = trim($responseBody);
    if ($shortBody !== '') {
        echo "  Body: " . substr($shortBody, 0, 250) . (strlen($shortBody) > 250 ? '...' : '') . "\n";
    } else {
        echo "  Body: (empty)\n";
    }
}

// -----------------------------------------------------------------------------
// Test 1: Token used as Bearer (what we did originally)
// -----------------------------------------------------------------------------
callApi(
    'Test 1: Direct Bearer auth on /im/send',
    'https://ars.apifon.com/services/api/v1/im/send',
    'POST',
    [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}',
);

// -----------------------------------------------------------------------------
// Test 2: OAuth2 exchange — token as client_id, with dummy secret
// -----------------------------------------------------------------------------
callApi(
    'Test 2: OAuth2 exchange — token as client_id + dummy secret',
    'https://ids.apifon.com/oauth2/token',
    'POST',
    ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
    "grant_type=client_credentials&client_id={$token}&client_secret={$dummy}",
);

// -----------------------------------------------------------------------------
// Test 3: OAuth2 exchange — token as client_secret, with dummy client_id
// -----------------------------------------------------------------------------
callApi(
    'Test 3: OAuth2 exchange — dummy client_id + token as client_secret',
    'https://ids.apifon.com/oauth2/token',
    'POST',
    ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
    "grant_type=client_credentials&client_id={$dummy}&client_secret={$token}",
);

// -----------------------------------------------------------------------------
// Test 4: OAuth2 exchange — token as BOTH client_id and client_secret
// -----------------------------------------------------------------------------
callApi(
    'Test 4: OAuth2 exchange — token as BOTH client_id and client_secret',
    'https://ids.apifon.com/oauth2/token',
    'POST',
    ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
    "grant_type=client_credentials&client_id={$token}&client_secret={$token}",
);

// -----------------------------------------------------------------------------
// Test 5: HMAC scheme — token used as the HMAC Token identifier
// -----------------------------------------------------------------------------
$date = gmdate('D, d M Y H:i:s O');
$body5 = '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}';
$stringToSign = "POST\n/services/api/v1/im/send\n{$body5}\n{$date}";
$signature = base64_encode(hash_hmac('sha256', $stringToSign, $token, true));

callApi(
    'Test 5: HMAC auth — token as identifier, token-as-secret signature',
    'https://ars.apifon.com/services/api/v1/im/send',
    'POST',
    [
        "Authorization: ApifonWS {$token}:{$signature}",
        "X-ApifonWS-Date: {$date}",
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    $body5,
);

// -----------------------------------------------------------------------------
// Test 6: Token as raw Authorization (no scheme prefix)
// -----------------------------------------------------------------------------
callApi(
    'Test 6: Raw Authorization header (no Bearer prefix)',
    'https://ars.apifon.com/services/api/v1/im/send',
    'POST',
    [
        "Authorization: {$token}",
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}',
);

echo "\n=== DONE ===\n";
