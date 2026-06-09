<?php

/**
 * Hypothesis: the 64-char string is client_id + client_secret concatenated.
 * Try every reasonable split and see if any combination authenticates.
 */

$fullToken = 'EmHav07ZiIqrjgFgzWa32S1uFDpalvKCtyrIStoDUgxRrtjngloaGpQHzYDxaQYi';

function exchangeOAuth(string $clientId, string $clientSecret): array
{
    $ch = curl_init('https://ids.apifon.com/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'scope'         => 'imGateway',
        ]),
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => trim($body)];
}

function test(string $label, string $clientId, string $clientSecret): void
{
    echo "\n--- {$label} ---\n";
    echo "  client_id     = " . $clientId . " (len " . strlen($clientId) . ")\n";
    echo "  client_secret = " . $clientSecret . " (len " . strlen($clientSecret) . ")\n";

    $res = exchangeOAuth($clientId, $clientSecret);
    echo "  HTTP {$res['status']}\n";
    echo "  Body: " . substr($res['body'], 0, 200) . "\n";

    if ($res['status'] === 200) {
        echo "  ✅ ✅ ✅  WE GOT A BEARER TOKEN!\n";
    }
}

// Split in half: 32 + 32
$h1 = substr($fullToken, 0, 32);
$h2 = substr($fullToken, 32, 32);

test('1A. First half (32) as client_id, Second half (32) as client_secret', $h1, $h2);
test('1B. Second half (32) as client_id, First half (32) as client_secret', $h2, $h1);

// Split: full as client_id + first half as secret
test('2A. Full (64) as client_id, First half (32) as client_secret', $fullToken, $h1);
test('2B. Full (64) as client_id, Second half (32) as client_secret', $fullToken, $h2);

// Split: 16 + 48 or 48 + 16
$q1 = substr($fullToken, 0, 16);
$q3 = substr($fullToken, 16, 48);
test('3A. First 16 as client_id, Remaining 48 as client_secret', $q1, $q3);
test('3B. First 48 as client_id, Last 16 as client_secret', substr($fullToken, 0, 48), substr($fullToken, 48));

// Reverse the string and use as secret with full as client_id
$reversed = strrev($fullToken);
test('4. Full as client_id, REVERSED full as client_secret', $fullToken, $reversed);

// Same string as both (we've tested but include for completeness)
test('5. Full as BOTH client_id and client_secret', $fullToken, $fullToken);
