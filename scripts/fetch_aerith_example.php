#!/usr/bin/env php
<?php
// Simple CLI helper to fetch an aerith.net comet page and show debug info.
// Usage:
//   php scripts/fetch_aerith_example.php [url]
// Env vars:
//   AERITH_VERIFY=true|false|/path/to/ca-bundle
//   AERITH_CA_BUNDLE=/path/to/ca-bundle

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$defaultUrl = 'https://www.aerith.net/comet/catalog/103artley2.html';
$url = $argv[1] ?? $defaultUrl;

// Build Guzzle client options and honor env overrides for debugging SSL issues
$clientOptions = ['timeout' => 20, 'allow_redirects' => true];

$aerithVerify = getenv('AERITH_VERIFY');
if ($aerithVerify !== false && $aerithVerify !== null) {
    if (in_array(strtolower($aerithVerify), ['false', '0'], true)) {
        $clientOptions['verify'] = false;
    } elseif (in_array(strtolower($aerithVerify), ['true', '1'], true)) {
        $clientOptions['verify'] = true;
    } else {
        // treat as path to CA bundle
        $clientOptions['verify'] = $aerithVerify;
    }
} else {
    $caBundle = getenv('AERITH_CA_BUNDLE');
    if ($caBundle !== false && $caBundle !== null && $caBundle !== '') {
        $clientOptions['verify'] = $caBundle;
    }
}

$client = new Client($clientOptions);

echo "Fetching: {$url}\n";

try {
    $res = $client->request('GET', $url, [
        'headers' => [
            'User-Agent' => 'laravel-astronomy-library-fetcher/1.0 (+https://github.com/DeepskyLog)'
        ],
    ]);

    $status = $res->getStatusCode();
    echo "Status: {$status} " . $res->getReasonPhrase() . "\n";
    echo "Headers:\n";
    foreach ($res->getHeaders() as $k => $vals) {
        echo "  {$k}: " . implode(', ', $vals) . "\n";
    }

    $body = (string) $res->getBody();
    $len = strlen($body);
    echo "Body length: {$len} bytes\n";
    echo "--- Body preview (first 800 chars) ---\n";
    echo substr($body, 0, 800) . "\n";

    // Save the full body to a file for inspection
    $out = __DIR__ . '/fetch_output_' . preg_replace('/[^a-z0-9]+/i', '_', parse_url($url, PHP_URL_PATH)) . '.html';
    file_put_contents($out, $body);
    echo "Saved full response to: {$out}\n";
    exit($status >= 400 ? 2 : 0);

} catch (RequestException $e) {
    echo "Request failed: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        $r = $e->getResponse();
        echo "Response status: " . $r->getStatusCode() . " " . $r->getReasonPhrase() . "\n";
        $body = (string) $r->getBody();
        echo "Body preview: " . substr($body, 0, 800) . "\n";
        $out = __DIR__ . '/fetch_output_error_' . preg_replace('/[^a-z0-9]+/i', '_', parse_url($url, PHP_URL_PATH)) . '.html';
        file_put_contents($out, $body);
        echo "Saved error response to: {$out}\n";
    } else {
        // Try to extract handler context for low-level cURL errors
        $ctx = $e->getHandlerContext();
        if (!empty($ctx)) {
            echo "Handler context:\n";
            foreach ($ctx as $k => $v) {
                echo "  {$k}: ";
                if (is_array($v)) echo json_encode($v);
                else echo $v;
                echo "\n";
            }
        }
    }
    exit(3);
} catch (\Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    exit(4);
}
