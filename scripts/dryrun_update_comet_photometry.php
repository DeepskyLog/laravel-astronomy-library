#!/usr/bin/env php
<?php

// Dry-run utility: connect to the database, enumerate comets, and resolve aerith.net pages.
// Usage:
//   export AERITH_VERIFY=false
//   php scripts/dryrun_update_comet_photometry.php
// DB can be overridden via env vars: DRY_DB_HOST, DRY_DB_NAME, DRY_DB_USER, DRY_DB_PASS

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Optionally, accept an input file (tab/CSV) with id,name lines instead of DB connection.
$inputFile = getenv('DRY_INPUT_FILE') ?: null;
if ($inputFile) {
    if (! is_readable($inputFile)) {
        echo "DRY_INPUT_FILE is set but file not readable: {$inputFile}\n";
        exit(2);
    }
    echo "Reading comet list from file: {$inputFile}\n";
    $rows = [];
    $fh = fopen($inputFile, 'r');
    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        // accept tab or comma separated or mysql -N output (id\tname)
        if (strpos($line, "\t") !== false) {
            [$id, $name] = explode("\t", $line, 2);
        } elseif (strpos($line, ',') !== false) {
            [$id, $name] = str_getcsv($line);
        } else {
            // if only name is provided
            $id = null;
            $name = $line;
        }
        $rows[] = ['id' => $id, 'name' => $name];
    }
    fclose($fh);
} else {
    $dbHost = getenv('DRY_DB_HOST') ?: '127.0.0.1';
    $dbName = getenv('DRY_DB_NAME') ?: 'deepskylogLaravel';
    $dbUser = getenv('DRY_DB_USER') ?: 'deepskylog';
    $dbPass = getenv('DRY_DB_PASS') ?: 'pdeepskylog';
    $dbPort = getenv('DRY_DB_PORT') ?: '3306';

    echo "Using DB {$dbUser}@{$dbHost}:{$dbPort}/{$dbName}\n";

    try {
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Exception $e) {
        echo 'Failed to connect to DB: '.$e->getMessage()."\n";
        exit(2);
    }

    // Fetch comet rows
    try {
        $stmt = $pdo->query('SELECT id, name FROM comets_orbital_elements');
        $rows = $stmt->fetchAll();
    } catch (Exception $e) {
        echo 'Failed to query comets_orbital_elements: '.$e->getMessage()."\n";
        exit(3);
    }

    if (empty($rows)) {
        echo "No comets found in table `comets_orbital_elements`.\n";
        exit(0);
    }
}

// Build Guzzle client
$clientOptions = ['timeout' => 10, 'allow_redirects' => true];
$aerithVerify = getenv('AERITH_VERIFY');
if ($aerithVerify !== false && $aerithVerify !== null) {
    if (in_array(strtolower($aerithVerify), ['false', '0'], true)) {
        $clientOptions['verify'] = false;
    } elseif (in_array(strtolower($aerithVerify), ['true', '1'], true)) {
        $clientOptions['verify'] = true;
    } else {
        $clientOptions['verify'] = $aerithVerify;
    }
} else {
    $ca = getenv('AERITH_CA_BUNDLE');
    if ($ca !== false && $ca !== null && $ca !== '') {
        $clientOptions['verify'] = $ca;
    }
}

$client = new Client($clientOptions);
$headers = ['User-Agent' => 'Mozilla/5.0 (compatible; laravel-astronomy-library-dryrun/1.0)'];

function generateCandidates(string $name): array
{
    $base = 'https://www.aerith.net/comet/catalog/';
    $candidates = [];
    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
    if ($slug) {
        $candidates[] = $base.$slug.'.html';
        $candidates[] = $base.$slug.'/';
    }
    if (preg_match('/(\d+)\s*([A-Za-z])/i', $name, $m)) {
        $num = intval($m[1]);
        $type = strtoupper($m[2]);
        $padded = sprintf('%04d%s', $num, $type);
        $plain = $num.$type;
        $candidates[] = $base.$padded.'/';
        $candidates[] = $base.$plain.'/';
        $candidates[] = $base.$padded.'.html';
        $candidates[] = $base.$plain.'.html';
        $candidates[] = $base.$padded.'/index.html';
        $candidates[] = $base.$padded.'/index-j.html';
    }
    $candidates[] = $base;
    // dedupe
    $out = [];
    foreach ($candidates as $c) {
        if ($c && ! in_array($c, $out)) {
            $out[] = $c;
        }
    }

    return $out;
}

echo 'Processing '.count($rows)." comets...\n";
$results = [];
foreach ($rows as $r) {
    $name = $r['name'] ?? '(unnamed)';
    $cands = generateCandidates($name);
    $matched = null;
    foreach ($cands as $url) {
        try {
            $res = $client->get($url, ['headers' => $headers]);
            if ($res->getStatusCode() === 200) {
                $body = (string) $res->getBody();
                // if directory try year pages
                if (substr($url, -1) === '/') {
                    $years = [];
                    $doc = new DOMDocument();
                    @$doc->loadHTML($body);
                    $xp = new DOMXPath($doc);
                    $links = $xp->query('//a/@href');
                    foreach ($links as $lnk) {
                        $href = trim($lnk->nodeValue);
                        if (preg_match('/^(?:\.\/)?(\d{4})\.html$/', $href, $mm)) {
                            $years[] = $mm[1];
                        }
                    }
                    if (! empty($years)) {
                        rsort($years, SORT_NUMERIC);
                        foreach ($years as $yr) {
                            $yearUrl = rtrim($url, '/').'/'.$yr.'.html';
                            try {
                                $ry = $client->get($yearUrl, ['headers' => $headers]);
                                if ($ry->getStatusCode() === 200) {
                                    $matched = $yearUrl;
                                    break;
                                }
                            } catch (RequestException $e) {
                                continue;
                            }
                        }
                        if ($matched) {
                            break;
                        }
                    }
                }
                $matched = $url;
                break;
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $code = $e->getResponse()->getStatusCode();
                if ($code === 404) {
                    continue;
                }
            }
            continue;
        } catch (Exception $e) {
            continue;
        }
    }
    $results[] = ['id' => $r['id'], 'name' => $name, 'matched' => $matched];
    if ($matched) {
        echo "MATCH: {$r['id']} | {$name} -> {$matched}\n";
    } else {
        echo "NO MATCH: {$r['id']} | {$name}\n";
    }
}

$matchedCount = count(array_filter($results, fn ($x) => $x['matched']));
echo "\nDone. Matched {$matchedCount} of ".count($results)." comets.\n";

// Optionally write CSV summary
$out = __DIR__.'/dryrun_aerith_matches.csv';
$fh = fopen($out, 'w');
fputcsv($fh, ['id', 'name', 'matched']);
foreach ($results as $r) {
    fputcsv($fh, [$r['id'], $r['name'], $r['matched']]);
}
fclose($fh);
echo "Summary written to: {$out}\n";

exit(0);
