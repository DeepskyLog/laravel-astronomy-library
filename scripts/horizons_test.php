<?php
$start = '2025-11-18 16:08';
$stop = '2025-11-18 16:09';
$des = "'90000224'";
$site = "'4.84457,49.3447,0.13'"; // altitude in km (0.13 km = 130 m)
$post = [
    'format' => 'text',
    'COMMAND' => $des,
    'EPHEM_TYPE' => 'OBSERVER',
    'CENTER' => 'coord@399',
    'SITE_COORD' => $site,
    'START_TIME' => "'{$start}'",
    'STOP_TIME' => "'{$stop}'",
    'STEP_SIZE' => "'1 m'",
    'CSV_FORMAT' => 'YES'
];
$ch = curl_init('https://ssd.jpl.nasa.gov/api/horizons.api');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($resp === false) {
    echo "Curl failed: $err\n";
    exit(1);
}
file_put_contents('/tmp/horizons_resp.txt', $resp);
echo "Saved response to /tmp/horizons_resp.txt\n";
// Extract between $$SOE and $$EOE
if (!preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $resp, $m)) {
    echo "No data block\n";
    exit(1);
}
$block = $m[1];
$blockFlat = preg_replace('/\r?\n/', ' ', $block);
$cols = preg_split('/\s*,\s*/', $blockFlat);
print_r(array_slice($cols, 0, 12));
// print a sample of the block
echo "--- block sample ---\n" . substr($block, 0, 500) . "\n";
