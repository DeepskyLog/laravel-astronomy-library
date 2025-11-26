<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;

class UpdateCometPhotometry extends Command
{
    protected $signature = 'astronomy:updateCometPhotometry';

    protected $description = 'Fetch comet photometry (H, n, phase) from external catalogs (attempt Seiichi Yoshida).';

    public function handle()
    {
        $this->info('Updating comet photometry from Seiichi Yoshida (aerith.net)');

        // Build Guzzle client options and allow runtime overrides for SSL verification.
        // - `AERITH_VERIFY` env var may be: `true`/`false` (boolean) or a path to a CA bundle file.
        // - `AERITH_CA_BUNDLE` env var may be set to an explicit CA bundle path.
        $clientOptions = ['timeout' => 10];

        $aerithVerify = env('AERITH_VERIFY', null);
        if ($aerithVerify !== null) {
            // If explicit boolean-like values are provided, convert them.
            if ($aerithVerify === false || $aerithVerify === 'false' || $aerithVerify === '0') {
                $clientOptions['verify'] = false;
            } elseif ($aerithVerify === true || $aerithVerify === 'true' || $aerithVerify === '1') {
                $clientOptions['verify'] = true;
            } else {
                // Otherwise treat the value as a path to a CA bundle file.
                $clientOptions['verify'] = $aerithVerify;
            }
        } else {
            $caBundle = env('AERITH_CA_BUNDLE', null);
            if ($caBundle) {
                $clientOptions['verify'] = $caBundle;
            }
        }

        $client = new Client($clientOptions);

        $comets = CometsOrbitalElements::all();
        foreach ($comets as $comet) {
            $name = $comet->name;

            // Build a list of candidate aerith.net URLs to try for this comet.
            $candidates = $this->generateAerithCandidates($name);

            $html = null;
            $usedUrl = null;

            // Use a browser-like User-Agent to avoid simple blocking
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (compatible; laravel-astronomy-library/1.0; +https://github.com/DeepskyLog)'
            ];

            foreach ($candidates as $url) {
                try {
                    $res = $client->get($url, ['headers' => $headers]);
                    if ($res->getStatusCode() === 200) {
                        $body = (string) $res->getBody();

                        // If this is a directory URL (ends with '/') try to find year pages
                        // like '2023.html' linked from the directory listing and attempt
                        // them (prefer newest year first) because photometry is often
                        // placed on year-specific pages.
                        if (substr($url, -1) === '/') {
                            $years = [];
                            $docDir = new DOMDocument();
                            @$docDir->loadHTML($body);
                            $xpathDir = new DOMXPath($docDir);
                            $links = $xpathDir->query('//a/@href');
                            foreach ($links as $lnk) {
                                $href = trim($lnk->nodeValue);
                                if (preg_match('/^(?:\.\/)?(\d{4})\.html$/', $href, $mm)) {
                                    $years[] = $mm[1];
                                }
                            }

                            if (!empty($years)) {
                                rsort($years, SORT_NUMERIC);
                                foreach ($years as $yr) {
                                    $yearUrl = rtrim($url, '/') . '/' . $yr . '.html';
                                    try {
                                        $ry = $client->get($yearUrl, ['headers' => $headers]);
                                        if ($ry->getStatusCode() === 200) {
                                            $html = (string) $ry->getBody();
                                            $usedUrl = $yearUrl;
                                            break 2; // found year page, exit both loops
                                        }
                                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                                        // ignore and try next year
                                        continue;
                                    }
                                }
                            }
                        }

                        // No year page found/usable — accept the directory page itself
                        $html = $body;
                        $usedUrl = $url;
                        break;
                    }
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    // If there's a response and it's a 404, try the next candidate.
                    if ($e->hasResponse()) {
                        $code = $e->getResponse()->getStatusCode();
                        if ($code === 404) {
                            // try next candidate
                            continue;
                        }
                    }
                    // For other errors, log and continue trying other candidates.
                    $this->line("Failed to fetch {$url}: " . $e->getMessage());
                    continue;
                } catch (\Exception $e) {
                    $this->line("Failed to fetch {$url}: " . $e->getMessage());
                    continue;
                }
            }

            if ($html === null) {
                $this->line("No aerith.net page found for {$name} (tried " . count($candidates) . " candidates)");
                continue;
            }

            // Parse the found HTML for photometry
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Look for text like "H = 6.5   n = 4.0"
            $nodes = $xpath->query('//text()');
            $foundH = null;
            $foundN = null;
            $foundPhase = null;
            $foundNpre = null;
            $foundNpost = null;
            foreach ($nodes as $n) {
                $text = trim($n->nodeValue);
                if (preg_match('/H\s*=\s*([0-9]+\.?[0-9]*)/i', $text, $m)) {
                    $foundH = floatval($m[1]);
                }
                if (preg_match('/n\s*=\s*([0-9]+\.?[0-9]*)/i', $text, $m)) {
                    $foundN = floatval($m[1]);
                }
                if (preg_match('/phase.*?([0-9]+\.?[0-9]*)/i', $text, $m)) {
                    $foundPhase = floatval($m[1]);
                }
            }

            if ($foundH !== null || $foundN !== null || $foundPhase !== null) {
                $this->info("Found photometry for {$name} at {$usedUrl}: H={$foundH} n={$foundN} phase={$foundPhase}");
                $comet->H = $foundH;
                $comet->n = $foundN;
                $comet->phase_coeff = $foundPhase;
                $comet->save();
            } else {
                $this->line("No photometry found for {$name} at {$usedUrl}");
            }
        }

        $this->info('Finished updating comet photometry.');
    }

    /**
     * Generate candidate aerith.net URLs for a given comet name.
     * Tries name-based slugs and numeric designation directory patterns (e.g. 0103P/).
     *
     * @param string $name
     * @return array
     */
    private function generateAerithCandidates(string $name): array
    {
        $base = 'https://www.aerith.net/comet/catalog/';
        $candidates = [];

        // Basic name-based slug: strip to alnum lowercase
        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        if ($slug) {
            $candidates[] = $base . $slug . '.html';
            $candidates[] = $base . $slug . '/';
        }

        // Try to extract a numeric designation like "103P" from the name (periodic)
        if (preg_match('/(\d+)\s*([A-Za-z])/i', $name, $m)) {
            $num = intval($m[1]);
            $type = strtoupper($m[2]);
            // zero-pad to 4 digits as used by aerith (e.g. 0103P)
            $padded = sprintf('%04d%s', $num, $type);
            $plain = $num . $type;

            $candidates[] = $base . $padded . '/';
            $candidates[] = $base . $plain . '/';
            $candidates[] = $base . $padded . '.html';
            $candidates[] = $base . $plain . '.html';
            $candidates[] = $base . $padded . '/index.html';
            $candidates[] = $base . $padded . '/index-j.html';
        }

        // Non-periodic designations like 2025A6 or 2020F3 (year + letter + number)
        // Aerith sometimes stores these under a directory with the same designation
        // and a year page named e.g. 2025A6.html
        if (preg_match('/(\d{4}[A-Za-z]\d+)/', $name, $m2)) {
            $desig = strtoupper($m2[1]);
            $candidates[] = $base . $desig . '/';
            $candidates[] = $base . $desig . '/' . $desig . '.html';
            $candidates[] = $base . $desig . '.html';
        }

        // Also try an index-style directory for common cases
        $candidates[] = $base;

        // Deduplicate while preserving order
        $seen = [];
        $out = [];
        foreach ($candidates as $c) {
            if (!$c) continue;
            if (!isset($seen[$c])) {
                $seen[$c] = true;
                $out[] = $c;
            }
        }

        return $out;
    }

    /**
     * Try to retrieve photometry (H, n, phase) from JPL SBDB as a fallback.
     * Returns an associative array with keys 'H','n','phase' when available or null on failure.
     */
    private function sbdbFallback(?string $designation, ?string $name, Client $client): ?array
    {
        $query = null;
        if ($designation && trim($designation) !== '') {
            $query = trim($designation);
        } elseif ($name && trim($name) !== '') {
            $query = trim($name);
        }

        if (!$query) return null;

        $url = 'https://ssd-api.jpl.nasa.gov/sbdb.api?des=' . urlencode($query) . '&phys-par=1';

        try {
            $res = $client->get($url, ['headers' => ['User-Agent' => 'laravel-astronomy-library-sbdb/1.0']]);
            if ($res->getStatusCode() !== 200) return null;

            $json = json_decode((string) $res->getBody(), true);
            if (!is_array($json)) return null;

            // Look for physical parameters. Different endpoints may use 'phys_par' or 'phys_par' nested structure.
            $h = null;
            $g = null;
            if (isset($json['phys_par']) && is_array($json['phys_par'])) {
                if (isset($json['phys_par']['H'])) $h = $json['phys_par']['H'];
                if (isset($json['phys_par']['h'])) $h = $json['phys_par']['h'];
                if (isset($json['phys_par']['G'])) $g = $json['phys_par']['G'];
                if (isset($json['phys_par']['g'])) $g = $json['phys_par']['g'];
            }

            // Some responses embed phys_par under 'object' or other keys — be defensive
            if ($h === null) {
                foreach ($json as $k => $v) {
                    if (is_array($v) && isset($v['H'])) {
                        $h = $v['H'];
                        break;
                    }
                }
            }

            if ($h !== null) {
                // SBDB provides H (absolute magnitude). It doesn't provide cometary n/phase by default.
                return ['H' => floatval($h), 'n' => null, 'phase' => null, 'source' => 'SBDB', 'query' => $query];
            }
        } catch (\Exception $e) {
            // ignore and return null
            return null;
        }

        return null;
    }
}
