<?php

/**
 * Discriminating test: do we get DIFFERENT error messages for valid vs
 * invalid client_ids? Or does Apifon return the same generic error for any
 * format-valid client_id?
 */

$ourToken    = 'EmHav07ZiIqrjgFgzWa32S1uFDpalvKCtyrIStoDUgxRrtjngloaGpQHzYDxaQYi';
$randomFake1 = 'AbCdEfGhIjKlMnOpQrStUvWxYz0123456789AbCdEfGhIjKlMnOpQrStUvWxYz12';
$randomFake2 = 'XxYyZzAaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZzAaBbCc';
$dummySecret = 'SOMERANDOMSECRETTHATWILLNOTMATCHANYTHING0000000000000000000000ZZ';

function probe(string $label, string $clientId, string $secret): void
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
        CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id={$clientId}&client_secret={$secret}",
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "\n--- {$label} ---\n";
    echo "client_id: " . substr($clientId, 0, 20) . "...\n";
    echo "HTTP {$status}\n";
    echo "Response: " . trim($body) . "\n";
}

probe('A. ΤΟ ΔΙΚΟ ΜΑΣ TOKEN ως client_id', $ourToken, $dummySecret);
probe('B. ΤΥΧΑΙΟ FAKE #1 ως client_id', $randomFake1, $dummySecret);
probe('C. ΤΥΧΑΙΟ FAKE #2 ως client_id', $randomFake2, $dummySecret);
