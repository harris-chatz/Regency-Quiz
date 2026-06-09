<?php

/**
 * Exhaustive HMAC test: maybe our token is an HMAC token identifier
 * (not OAuth client_id). HMAC requires Token + SecretKey, so without
 * the secret we still can't fully authenticate — but we CAN check if
 * Apifon recognizes the token as a valid HMAC token identifier.
 *
 * The trick: if Apifon recognizes the token but the signature is wrong,
 * we'd expect a DIFFERENT 401 error than if the token isn't HMAC at all.
 */

$token = 'EmHav07ZiIqrjgFgzWa32S1uFDpalvKCtyrIStoDUgxRrtjngloaGpQHzYDxaQYi';
$body = '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}';

function tryHmac(string $label, string $token, string $secret): void
{
    $date = gmdate('D, d M Y H:i:s O');
    $stringToSign = "POST\n/services/api/v1/sms/send\n" .
        '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}' .
        "\n{$date}";
    $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));

    $ch = curl_init('https://ars.apifon.com/services/api/v1/sms/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            "Authorization: ApifonWS {$token}:{$signature}",
            "X-ApifonWS-Date: {$date}",
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => '{"message":{"text":"ping","sender_id":"MONT PARNES"},"subscribers":[{"number":"306900000000"}]}',
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headers = substr($raw, 0, $hSize);
    $body = trim(substr($raw, $hSize));

    echo "\n--- {$label} ---\n";
    echo "  HMAC token  : " . substr($token, 0, 20) . "... (len " . strlen($token) . ")\n";
    echo "  Secret guess: " . substr($secret, 0, 20) . "... (len " . strlen($secret) . ")\n";
    echo "  HTTP {$status}\n";

    foreach (explode("\n", $headers) as $line) {
        if (preg_match('/^(www-authenticate|x-apifon|x-error|content-type):/i', trim($line))) {
            echo "  → " . trim($line) . "\n";
        }
    }
    if ($body !== '') {
        echo "  Body: " . substr($body, 0, 250) . "\n";
    }
}

// Multiple secret guesses — if any returns different error than "not HMAC at all"
// then we know it MIGHT be an HMAC token
tryHmac('HMAC-1: token + token-as-secret',        $token, $token);
tryHmac('HMAC-2: token + first half as secret',    $token, substr($token, 0, 32));
tryHmac('HMAC-3: token + second half as secret',   $token, substr($token, 32));
tryHmac('HMAC-4: token + empty secret',            $token, '');
tryHmac('HMAC-5: token + reversed-as-secret',      $token, strrev($token));

// Also test on /im/send endpoint
echo "\n=== Trying /services/api/v1/im/send instead ===\n";
$endpoint = '/services/api/v1/im/send';

function tryHmacEndpoint(string $label, string $token, string $secret, string $endpoint): void
{
    $date = gmdate('D, d M Y H:i:s O');
    $body = '{"im_channels":[{"sender_id":"MONT PARNES","text":"ping"}],"subscribers":[{"number":"306900000000"}]}';
    $stringToSign = "POST\n{$endpoint}\n{$body}\n{$date}";
    $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));

    $ch = curl_init('https://ars.apifon.com' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            "Authorization: ApifonWS {$token}:{$signature}",
            "X-ApifonWS-Date: {$date}",
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $body,
    ]);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headers = substr($raw, 0, $hSize);
    $body = trim(substr($raw, $hSize));

    echo "\n--- {$label} ---\n";
    echo "  HTTP {$status}\n";
    foreach (explode("\n", $headers) as $line) {
        if (preg_match('/^(www-authenticate|x-apifon|x-error):/i', trim($line))) {
            echo "  → " . trim($line) . "\n";
        }
    }
    if ($body !== '') {
        echo "  Body: " . substr($body, 0, 250) . "\n";
    }
}

tryHmacEndpoint('HMAC-6: /im/send + token-as-secret', $token, $token, '/services/api/v1/im/send');
