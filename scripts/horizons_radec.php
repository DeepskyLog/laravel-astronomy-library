<?php

// Usage: php horizons_radec.php <designation> <YYYY-MM-DD HH:MM> <lon> <lat> <alt_m>
$ephem = $argv[6] ?? null;

if ($argc < 6) {
    echo json_encode(['error' => 'usage: designation datetime lon lat alt_m [EPHEM]']);
    exit(1);
}
$des = $argv[1];
$dt = $argv[2];
$lon = $argv[3];
$lat = $argv[4];
$alt_m = floatval($argv[5]);
$alt_km = $alt_m / 1000.0;
$start = $dt;
$stop = date('Y-m-d H:i', strtotime($dt.' +1 minute'));

function doQuery($command, $site, $start, $stop, $ephem = null)
{
    // Request JSON format when possible for more reliable parsing.
    $post = [
        'format' => 'json',
        'COMMAND' => $command,
        'EPHEM_TYPE' => 'OBSERVER',
        'CENTER' => 'coord@399',
        'SITE_COORD' => $site,
        'START_TIME' => "'{$start}'",
        'STOP_TIME' => "'{$stop}'",
        'STEP_SIZE' => "'1 m'",
        'CSV_FORMAT' => 'YES',
    ];

    // Allow client to request a particular JPL ephemeris name (e.g. DE440).
    if ($ephem !== null && trim($ephem) !== '') {
        $post['EPHEM'] = trim($ephem);
    }
    $ch = curl_init('https://ssd.jpl.nasa.gov/api/horizons.api');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_USERAGENT, 'Deepsky-AstronomyLibrary/1.0');
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    // If the API returned JSON, try to extract a textual result block to keep
    // downstream parsing compatible with existing logic.
    $decoded = @json_decode($resp, true);
    if (is_array($decoded)) {
        // Helper: recursively search for any string that contains $$SOE..$$EOE
        $findBlock = function ($item) use (&$findBlock) {
            if (is_string($item)) {
                if (strpos($item, '$$SOE') !== false) {
                    return $item;
                }

                return null;
            }
            if (is_array($item)) {
                foreach ($item as $v) {
                    $res = $findBlock($v);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }

            return null;
        };

        $block = $findBlock($decoded);
        if ($block !== null) {
            return [$block, $err];
        }

        // Some API responses include a 'result' string or 'data' key containing
        // the textual output — try those as fallbacks.
        if (isset($decoded['result']) && is_string($decoded['result'])) {
            return [$decoded['result'], $err];
        }
        if (isset($decoded['data']) && is_string($decoded['data'])) {
            return [$decoded['data'], $err];
        }

        // Otherwise return the original raw response so existing fallback parsing
        // can still attempt to find numeric ids or text blocks.
    }

    return [$resp, $err];
}

$site = "'{$lon},{$lat},{$alt_km}'";
$command = "'{$des}'";
// Track which command produced the final successful response for debugging.
$used_command = $command;
[$resp, $err] = doQuery($command, $site, $start, $stop, $ephem);
if ($resp === false || empty($resp)) {
    echo json_encode(['error' => 'horizons empty', 'curl' => $err]);
    exit(1);
}

// Save full raw response inside the workspace for debugging
@file_put_contents(__DIR__.'/horizons_raw.txt', $resp);

// If no $$SOE block, attempt to resolve an index-search result and re-query.
if (! preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $resp, $m)) {
    $rec = null;

    // 1) Try to parse DASTCOM/Horizons index table rows first and prefer the
    // most recent epoch-year; attempt each record in descending epoch order
    // until a $$SOE..$$EOE block is returned.
    if (preg_match_all('/^\s*(\d{4,9})\s+(\d{4})\s+/m', $resp, $mdat)) {
        $records = $mdat[1];
        $epochs = $mdat[2];
        $pairs = [];
        for ($i = 0; $i < count($records); $i++) {
            $pairs[] = ['rec' => $records[$i], 'epoch' => intval($epochs[$i])];
        }
        usort($pairs, function ($a, $b) {
            return $b['epoch'] <=> $a['epoch'];
        });
        foreach ($pairs as $p) {
            $tryRec = $p['rec'];
            $tryCmd = "'{$tryRec}'";
            [$resp2, $err2] = doQuery($tryCmd, $site, $start, $stop, $ephem);
            if ($resp2 !== false && ! empty($resp2) && preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $resp2)) {
                $resp = $resp2;
                $rec = $tryRec;
                $used_command = $tryCmd;
                break;
            }
        }
    }

    // 2) If not found, try parsing numbered-choice index lines
    if ($rec === null) {
        if (preg_match_all('/^\s*\d+\)\s*(.+)$/m', $resp, $mlines)) {
            foreach ($mlines[1] as $ln) {
                if (preg_match_all('/\b(\d{1,9})\b/', $ln, $mt)) {
                    foreach ($mt[1] as $tok) {
                        $intval = intval($tok);
                        if ($intval < 1800 || $intval > 2200) {
                            $rec = $tok;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    // 3) Fallback: search all numeric tokens (1-9 digits) and exclude year-like values
    if ($rec === null) {
        if (preg_match_all('/\b(\d{1,9})\b/', $resp, $mall)) {
            foreach ($mall[1] as $tok) {
                $intval = intval($tok);
                if ($intval < 1800 || $intval > 2200) {
                    $rec = $tok;
                    break;
                }
            }
        }
    }

    if ($rec !== null) {
        $tryCmd = "'{$rec}'";
        [$resp2, $err2] = doQuery($tryCmd, $site, $start, $stop, $ephem);
        if ($resp2 === false || empty($resp2)) {
            echo json_encode(['error' => 'requery failed', 'curl' => $err2]);
            exit(1);
        }
        $resp = $resp2;
        $used_command = $tryCmd;
    } else {
        // If no numeric record was found or all re-queries failed, attempt the
        // small-body (SB) integrated solution as a fallback when appropriate.
        $lower = strtolower($resp);
        if (
            strpos($lower, 'spk-based ephemeris') !== false
            || strpos($lower, 'precomputed') !== false
            || strpos($resp, 'DES=') !== false
            || strpos($resp, 'There are two trajectories') !== false
        ) {
            $sbCmd = "'DES={$des}; CAP;'";
            [$respSb, $errSb] = doQuery($sbCmd, $site, $start, $stop, $ephem);
            if ($respSb !== false && ! empty($respSb) && preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $respSb)) {
                $resp = $respSb;
                $used_command = $sbCmd;
            } else {
                echo json_encode(['error' => 'no data block and no record id']);
                exit(1);
            }
        } else {
            echo json_encode(['error' => 'no data block and no record id']);
            exit(1);
        }
    }
}
if (! preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $resp, $mblock)) {
    echo json_encode(['error' => 'no block after requery']);
    exit(1);
}
$block = $mblock[1];

// Save raw block into workspace for quick inspection
@file_put_contents(__DIR__.'/horizons_block.txt', $block);

// Split block into lines and choose the best data line (first non-empty, non-header line)
$lines = preg_split('/\r?\n/', trim($block));
$dataLine = null;
foreach ($lines as $ln) {
    $ln = trim($ln);
    if ($ln === '') {
        continue;
    }
    if (strpos($ln, '***') === 0) {
        continue;
    }
    if (strpos($ln, ',') !== false && preg_match('/\d/', $ln)) {
        $dataLine = $ln;
        break;
    }
}
if ($dataLine === null) {
    echo json_encode(['error' => 'no data line in block']);
    exit(1);
}

// Split fields on commas and trim
$fields = array_map('trim', preg_split('/\s*,\s*/', $dataLine));

// Helper to find index matching regex
function findFieldIndex(array $fields, string $regex)
{
    foreach ($fields as $i => $f) {
        if (preg_match($regex, $f)) {
            return $i;
        }
    }

    return -1;
}

// RA formats: 'HH MM SS.ss' or 'HH:MM:SS'
$raIdx = findFieldIndex($fields, '/^\s*\d{1,2}[:\s]\d{1,2}[:\s]\d{1,2}(\.\d+)?/');
// DEC formats: prefer signed degrees (leading + or -) for unambiguous detection
$decIdx = findFieldIndex($fields, '/^[\+\-]\d{1,3}[:\s]\d{1,2}[:\s]\d{1,2}(\.\d+)?/');

// Fallback to legacy indices if regex search failed
if ($raIdx === -1) {
    $raIdx = isset($fields[5]) ? 5 : (isset($fields[3]) ? 3 : 0);
}
if ($decIdx === -1) {
    $decIdx = isset($fields[6]) ? 6 : (isset($fields[4]) ? 4 : 1);
}

$raStr = $fields[$raIdx] ?? '';
$decStr = $fields[$decIdx] ?? '';

// Prefer the a-app (apparent) RA/Dec pair if present (usually columns 5 and 6)
if (isset($fields[5]) && isset($fields[6])) {
    $maybeRa = $fields[5];
    $maybeDec = $fields[6];
    if (
        preg_match('/^\s*\d{1,2}[:\s]\d{1,2}[:\s]\d{1,2}(\.\d+)?/', $maybeRa)
        && preg_match('/^[\+\-]\d{1,3}[:\s]\d{1,2}[:\s]\d{1,2}(\.\d+)?/', $maybeDec)
    ) {
        $raStr = $maybeRa;
        $decStr = $maybeDec;
    }
}

function hmsToHours($s)
{
    $s = trim($s);
    if (strpos($s, ':') !== false) {
        [$h, $m, $sec] = explode(':', $s) + [0, 0, 0];

        return intval($h) + intval($m) / 60.0 + floatval($sec) / 3600.0;
    }
    $parts = preg_split('/\s+/', $s);
    if (count($parts) >= 3) {
        return intval($parts[0]) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0;
    }

    return floatval($s);
}
function dmsToDeg($s)
{
    $s = trim($s);
    $sign = 1;
    if ($s[0] === '+' || $s[0] === '-') {
        if ($s[0] === '-') {
            $sign = -1;
        }
        $s = substr($s, 1);
    }
    if (strpos($s, ':') !== false) {
        [$d, $m, $sec] = explode(':', $s) + [0, 0, 0];

        return $sign * (abs(intval($d)) + intval($m) / 60.0 + floatval($sec) / 3600.0);
    }
    $parts = preg_split('/\s+/', $s);
    if (count($parts) >= 3) {
        return $sign * (abs(intval($parts[0])) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0);
    }

    return $sign * floatval($s);
}
$raH = hmsToHours($raStr);
$decD = dmsToDeg($decStr);
$out = ['ra_hours' => $raH, 'dec_deg' => $decD, 'raw_ra' => $raStr, 'raw_dec' => $decStr, 'used_command' => ($used_command ?? $command)];
// Save structured JSON output for inspection
@file_put_contents(__DIR__.'/horizons_resp.json', json_encode($out));
echo json_encode($out);
