#!/usr/bin/env php
<?php

// Dry-run: connect to MySQL via PDO, read comets_orbital_elements,
// try aerith.net candidate URLs per comet, print matches and non-matches,
// and write CSV to scripts/aerith_matches.csv

require __DIR__.'/../vendor/autoload.php';

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$dbHost = '127.0.0.1';
$dbName = 'deepskylogLaravel';
$dbUser = 'deepskylog';
$dbPass = 'deepskylog';

echo "Dry-run: connecting to MySQL {$dbHost} db={$dbName} user={$dbUser}\n";

try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo 'Failed to connect to DB: '.$e->getMessage()."\n";
    exit(2);
}
// We'll query all rows below after discovering columns
// Discover columns in the table and choose sensible defaults for id/name/designation
$colsStmt = $pdo->query('SHOW COLUMNS FROM comets_orbital_elements');
$cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN);

$idCol = null;
$nameCol = null;
$desigCol = null;

$preferId = ['id', 'ID', 'comet_id', 'pk', 'orbital_element_id', 'orbital_elements_id'];
$preferName = ['name', 'designation', 'object', 'object_name', 'longname'];
$preferDesig = ['designation', 'desig', 'prov_desig', 'prov_designation', 'designation_prov'];

foreach ($preferId as $c) {
    if (in_array($c, $cols, true)) {
        $idCol = $c;
        break;
    }
}
foreach ($preferName as $c) {
    if (in_array($c, $cols, true)) {
        $nameCol = $c;
        break;
    }
}
foreach ($preferDesig as $c) {
    if (in_array($c, $cols, true)) {
        $desigCol = $c;
        break;
    }
}

// Fall back to first columns if nothing matched
if ($idCol === null && count($cols) > 0) {
    $idCol = $cols[0];
}
if ($nameCol === null && count($cols) > 0) {
    $nameCol = $cols[0];
}
if ($desigCol === null && count($cols) > 1) {
    $desigCol = $cols[1];
}

$selectSql = 'SELECT * FROM comets_orbital_elements';
$stmt = $pdo->query($selectSql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo 'Found '.count($rows)." comets in table.\n";

// Build Guzzle client honoring env vars
$clientOptions = ['timeout' => 10, 'allow_redirects' => true];
$aerithVerify = getenv('AERITH_VERIFY');
if ($aerithVerify !== false && $aerithVerify !== null && $aerithVerify !== '') {
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
$headers = [
    'User-Agent' => 'Mozilla/5.0 (compatible; laravel-astronomy-library-dryrun/1.0)',
];

$outCsv = __DIR__.'/aerith_matches.csv';
$fh = fopen($outCsv, 'w');
fputcsv($fh, ['id', 'name', 'designation', 'matched_url']);

$matches = [];
$noMatches = [];

foreach ($rows as $r) {
    $id = $r[$idCol] ?? '';
    $name = $r[$nameCol] ?? '';
    $designation = $r[$desigCol] ?? '';

    echo "Processing [{$id}] {$name} ({$designation})... ";

    $candidates = generateAerithCandidates($name, $designation);
    $matchedUrl = null;

    foreach ($candidates as $url) {
        try {
            $res = $client->get($url, ['headers' => $headers]);
            if ($res->getStatusCode() === 200) {
                $body = (string) $res->getBody();
                // If directory, scan for year pages
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
                                    $matchedUrl = $yearUrl;
                                    break 2;
                                }
                            } catch (RequestException $e) {
                                continue;
                            }
                        }
                    }
                }
                $matchedUrl = $url;
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

    if ($matchedUrl) {
        echo "MATCH -> {$matchedUrl}\n";
        fputcsv($fh, [$id, $name, $designation, $matchedUrl]);
        $matches[] = ['id' => $id, 'name' => $name, 'designation' => $designation, 'url' => $matchedUrl];
    } else {
        // Try SBDB fallback for H value
        $sbdb = sbdbLookup($designation ?: $name, $client);
        if ($sbdb) {
            $matchedUrl = 'SBDB:'.($sbdb['query'] ?? $designation ?: $name);
            echo "SBDB MATCH (H={$sbdb['H']}) -> {$matchedUrl}\n";
            fputcsv($fh, [$id, $name, $designation, $matchedUrl]);
            $matches[] = ['id' => $id, 'name' => $name, 'designation' => $designation, 'url' => $matchedUrl, 'H' => $sbdb['H']];
        } else {
            echo "NO MATCH\n";
            fputcsv($fh, [$id, $name, $designation, '']);
            $noMatches[] = ['id' => $id, 'name' => $name, 'designation' => $designation];
        }
    }
}

fclose($fh);

echo "\nDry-run complete. Matches: ".count($matches).', No matches: '.count($noMatches)."\n";
echo "CSV saved to: {$outCsv}\n";

if (! empty($noMatches)) {
    echo "\nComets without matches:\n";
    foreach ($noMatches as $nm) {
        echo "[{$nm['id']}] {$nm['name']} ({$nm['designation']})\n";
    }
}

exit(0);

function generateAerithCandidates(string $name, string $designation = ''): array
{
    $base = 'https://www.aerith.net/comet/catalog/';
    $candidates = [];

    // If designation provided and matches non-periodic pattern like 2025A6
    if ($designation) {
        $d = strtoupper(trim($designation));
        if (preg_match('/^\d{4}[A-Z]\d+$/', $d)) {
            $candidates[] = $base.$d.'/';
            $candidates[] = $base.$d.'/'.$d.'.html';
            $candidates[] = $base.$d.'.html';
        }
        // periodic like 103P
        if (preg_match('/^(\d+)([A-Za-z])$/i', $d, $mm)) {
            $num = intval($mm[1]);
            $type = strtoupper($mm[2]);
            $padded = sprintf('%04d%s', $num, $type);
            $plain = $num.$type;
            $candidates[] = $base.$padded.'/';
            $candidates[] = $base.$plain.'/';
            $candidates[] = $base.$padded.'.html';
            $candidates[] = $base.$plain.'.html';
        }
    }

    // Name-based slug
    if ($name) {
        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        if ($slug) {
            $candidates[] = $base.$slug.'.html';
            $candidates[] = $base.$slug.'/';
        }
    }

    // Also try using extracted numeric from name
    if (preg_match('/(\d+)\s*([A-Za-z])/i', $name, $m)) {
        $num = intval($m[1]);
        $type = strtoupper($m[2]);
        $padded = sprintf('%04d%s', $num, $type);
        $plain = $num.$type;
        $candidates[] = $base.$padded.'/';
        $candidates[] = $base.$plain.'/';
        $candidates[] = $base.$padded.'.html';
        $candidates[] = $base.$plain.'.html';
    }

    // Default: catalog root
    $candidates[] = $base;

    // Dedup
    $out = [];
    foreach ($candidates as $c) {
        if (! $c) {
            continue;
        }
        if (! in_array($c, $out, true)) {
            $out[] = $c;
        }
    }

    return $out;
}

/**
 * Query JPL SBDB API for a designation or name and return H when available.
 */
function sbdbLookup(string $query, Client $client): ?array
{
    $q = trim($query);
    if ($q === '') {
        return null;
    }
    $url = 'https://ssd-api.jpl.nasa.gov/sbdb.api?des='.urlencode($q).'&phys-par=1';
    try {
        $res = $client->get($url, ['headers' => ['User-Agent' => 'laravel-astronomy-library-dryrun/1.0']]);
        if ($res->getStatusCode() !== 200) {
            return null;
        }
        $json = json_decode((string) $res->getBody(), true);
        if (! is_array($json)) {
            return null;
        }
        if (isset($json['phys_par']) && is_array($json['phys_par'])) {
            if (isset($json['phys_par']['H'])) {
                return ['H' => floatval($json['phys_par']['H']), 'query' => $q];
            }
            if (isset($json['phys_par']['h'])) {
                return ['H' => floatval($json['phys_par']['h']), 'query' => $q];
            }
        }
        // defensive: search nested arrays
        foreach ($json as $v) {
            if (is_array($v) && isset($v['H'])) {
                return ['H' => floatval($v['H']), 'query' => $q];
            }
        }
    } catch (Exception $e) {
        return null;
    }

    return null;
}
