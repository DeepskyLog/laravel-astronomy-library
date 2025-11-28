<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;

class UpdateCometPhotometry extends Command
{
    protected $signature = 'astronomy:updateCometPhotometry {--target= : Name or designation to process only}';

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

        $target = $this->option('target');
        if ($target && trim($target) !== '') {
            $this->info("Processing single target: {$target}");
            $t = trim($target);
            // Try exact match first, then a LIKE fallback
            $comets = CometsOrbitalElements::where('name', $t)
                ->orWhere('name', 'like', "%{$t}%")
                ->limit(1)
                ->get();

            if ($comets->isEmpty()) {
                $this->line("No comet found matching '{$target}' in comets_orbital_elements.");
                return 0;
            }
        } else {
            $comets = CometsOrbitalElements::all();
        }

        // If a single target was requested, provide a shortcut to try SBDB
        // aggressively and persist any photometry found. This avoids the
        // full aerith scanning loop when the host app needs a quick update.
        if ($target && $comets->count() === 1) {
            $single = $comets->first();
            $this->line("Target mode: performing focused SBDB lookup for {$single->name}...");

            // Print DB connection info (host, database, user) to help confirm
            // the artisan command is connected to the same DB used by the
            // dryrun script. Do not print passwords.
            // DB connection debug logging removed to reduce verbosity.

            $sb = $this->sbdbFallback($single->designation ?? null, $single->name, $client);
            if (is_array($sb)) {
                if ($sb['H'] !== null || $sb['n'] !== null || $sb['phase'] !== null) {
                    $this->info("Updating {$single->name} from SBDB: H={$sb['H']} n={$sb['n']} phase={$sb['phase']}");
                    $single->H = $sb['H'] !== null ? floatval($sb['H']) : null;
                    $single->n = $sb['n'] !== null ? floatval($sb['n']) : null;
                    $single->phase_coeff = $sb['phase'] !== null ? floatval($sb['phase']) : null;
                    $single->save();
                    $this->info("Saved photometry for {$single->name}");
                } else {
                    $this->line("SBDB lookup found object but no photometry for {$single->name}.");
                }
            } else {
                $this->line("Focused SBDB lookup did not find a match for {$single->name}.");
            }

            $this->info('Finished updating comet photometry.');
            return 0;
        }
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
                        // Print a short stripped excerpt for debugging why photometry
                        // may not be detected on this page.
                        // (content excerpt logging removed)

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
                            $foundLinks = [];
                            foreach ($links as $lnk) {
                                $href = trim($lnk->nodeValue);
                                $foundLinks[] = $href;

                                // Try several patterns to capture year-specific pages
                                // Examples encountered on aerith: '2022.html', './2022.html',
                                // '../0100P/2022.html', '2022/', '2022/index.html', '2002C1.html'
                                // 1) explicit HTML page ending with year+designation
                                if (preg_match('/(\d{4}(?:[A-Za-z]\d*)?)\.html$/i', $href, $mm)) {
                                    $years[] = $mm[1];
                                    continue;
                                }

                                // 2) trailing year directory like '2022/' or './2022/'
                                if (preg_match('/(\d{4}(?:[A-Za-z]\d*)?)\/?$/i', $href, $mm2)) {
                                    // Ensure the match is just a year or year+designation token
                                    // and not other path fragments containing digits.
                                    // Accept only if the href ends with the matched token optionally followed by '/'.
                                    $token = $mm2[1];
                                    if (preg_match('/' . preg_quote($token, '/') . '\/?$/i', $href)) {
                                        $years[] = $token;
                                        continue;
                                    }
                                }

                                // 3) index.html under a year directory: '2022/index.html' or './2022/index.html'
                                if (preg_match('/(\d{4}(?:[A-Za-z]\d*)?)\/index\.html$/i', $href, $mm3)) {
                                    $years[] = $mm3[1];
                                    continue;
                                }
                            }
                            // directory links discovered (not logged to reduce verbosity)

                            $foundYearPage = false;
                            if (!empty($years)) {
                                // Group matches by their 4-digit year prefix and attempt
                                // pages starting from the most recent year, trying more
                                // specific designation filenames first (e.g. '2002C1').
                                $byYear = [];
                                foreach ($years as $y) {
                                    if (preg_match('/^(\d{4})/', $y, $ym)) {
                                        $yrOnly = intval($ym[1]);
                                        $byYear[$yrOnly][] = $y;
                                    }
                                }

                                if (!empty($byYear)) {
                                    krsort($byYear, SORT_NUMERIC);
                                    foreach ($byYear as $yr => $cands) {
                                        // Prefer longer/more specific names first (e.g. '2002C1')
                                        usort($cands, function ($a, $b) {
                                            return strlen($b) - strlen($a);
                                        });

                                        foreach ($cands as $candName) {
                                            $yearUrl = rtrim($url, '/') . '/' . $candName . '.html';
                                            try {
                                                $ry = $client->get($yearUrl, ['headers' => $headers]);
                                                if ($ry->getStatusCode() === 200) {
                                                    $bodyYear = (string) $ry->getBody();
                                                    // (year page excerpt logging removed)

                                                    // Quick check: accept this year page only if
                                                    // it appears to contain photometry (green curve,
                                                    // m1 formula, or H/G). If not, continue trying
                                                    // other year candidates.
                                                    $hasPhot = false;
                                                    if (preg_match('/Green\s*curve.*?m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)/is', $bodyYear)) {
                                                        $hasPhot = true;
                                                    } elseif (preg_match('/m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)[^\n\r]*?5\s*log\s*d[^\n\r]*?([+-]?[0-9]+\.?[0-9]*)\s*log\s*r/i', $bodyYear)) {
                                                        $hasPhot = true;
                                                    } else {
                                                        $plainYear = trim(strip_tags($bodyYear));
                                                        if (preg_match('/\bH\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plainYear) && preg_match('/\bG\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plainYear)) {
                                                            $hasPhot = true;
                                                        }
                                                    }

                                                    if ($hasPhot) {
                                                        $html = $bodyYear;
                                                        $usedUrl = $yearUrl;
                                                        $foundYearPage = true;
                                                        break; // found usable year page
                                                    }
                                                    // otherwise continue to next candidate
                                                }
                                            } catch (\GuzzleHttp\Exception\RequestException $e) {
                                                // ignore and try next candidate (silent)
                                                continue;
                                            }
                                        }
                                    }
                                }
                                // If no year pages contained photometry, do not accept the
                                // directory page itself here; continue trying other candidates.
                                // (The outer loop will continue.)
                                continue;
                            } else {
                                // No explicit year links found in the directory listing.
                                // As a fallback, try plausible year pages (current year
                                // down to 30 years ago) such as '2022.html' which are
                                // sometimes present even when not linked.
                                $current = intval(date('Y'));
                                $foundYearPage = false;
                                for ($y = $current; $y >= $current - 30; $y--) {
                                    $yearUrl = rtrim($url, '/') . '/' . $y . '.html';
                                    try {
                                        $ry = $client->get($yearUrl, ['headers' => $headers]);
                                        if ($ry->getStatusCode() === 200) {
                                            $bodyYear = (string) $ry->getBody();
                                            // (fallback year page excerpt logging removed)
                                            // Quick photometry check as above
                                            $hasPhot = false;
                                            if (preg_match('/Green\s*curve.*?m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)/is', $bodyYear)) {
                                                $hasPhot = true;
                                            } elseif (preg_match('/m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)[^\n\r]*?5\s*log\s*d[^\n\r]*?([+-]?[0-9]+\.?[0-9]*)\s*log\s*r/i', $bodyYear)) {
                                                $hasPhot = true;
                                            } else {
                                                $plainYear = trim(strip_tags($bodyYear));
                                                if (preg_match('/\bH\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plainYear) && preg_match('/\bG\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plainYear)) {
                                                    $hasPhot = true;
                                                }
                                            }

                                            if ($hasPhot) {
                                                $html = $bodyYear;
                                                $usedUrl = $yearUrl;
                                                $foundYearPage = true;
                                                break; // found usable fallback year page
                                            }
                                        }
                                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                                        continue;
                                    }
                                }
                                if ($foundYearPage) {
                                    // we found and accepted a year page, proceed
                                } else {
                                    // No year pages with photometry found; accept the directory page itself
                                    $html = $body;
                                    $usedUrl = $url;
                                    break;
                                }
                            }
                        }

                        // If we found a year page above, stop trying further candidates
                        if (!empty($foundYearPage)) {
                            break; // exit the candidates loop and proceed to parsing
                        }
                    }
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    // Silent on RequestException; continue to next candidate.
                    continue;
                } catch (\Exception $e) {
                    $this->line("Failed to fetch {$url}: " . $e->getMessage());
                    continue;
                }
            }

            if ($html === null) {
                $this->line("No aerith.net page found for {$name} (tried " . count($candidates) . " candidates)");

                // Try JPL SBDB as a fallback when no aerith page is available.
                $this->line("Attempting SBDB fallback for {$name} (no aerith page)...");
                $sb = $this->sbdbFallback($comet->designation ?? null, $name, $client);
                if (is_array($sb)) {
                    $this->info("Found photometry for {$name} via SBDB: H={$sb['H']} n={$sb['n']} phase={$sb['phase']}");
                    $comet->H = $sb['H'] !== null ? floatval($sb['H']) : null;
                    $comet->n = $sb['n'] !== null ? floatval($sb['n']) : null;
                    $comet->phase_coeff = $sb['phase'] !== null ? floatval($sb['phase']) : null;
                    $comet->save();
                    continue;
                }

                continue;
            }

            // Parse the found HTML for photometry
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Prefer an explicit "Green curve" m1 formula when available
            // Example: "Green curve is:  m1 = 6.8 + 5 log d + 9.0 log r"
            $foundH = null;
            $foundN = null;
            $foundPhase = null;
            $foundNpre = null;
            $foundNpost = null;

            // Search the raw HTML first for a green-curve m1 formula (most useful)
            if (preg_match('/Green\s*curve.*?m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)[^\n\r]*?5\s*log\s*d[^\n\r]*?([+-]?[0-9]+\.?[0-9]*)\s*log\s*r/is', $html, $gm)) {
                $foundH = floatval($gm[1]);
                $foundN = floatval($gm[2]);
            } else {
                // Fallback: look for the first generic m1 formula if green curve not found
                if (preg_match('/m1\s*=\s*([+-]?[0-9]+\.?[0-9]*)[^\n\r]*?5\s*log\s*d[^\n\r]*?([+-]?[0-9]+\.?[0-9]*)\s*log\s*r/i', $html, $gm2)) {
                    $foundH = floatval($gm2[1]);
                    $foundN = floatval($gm2[2]);
                }

                // If neither formula matched, fall back to scanning text nodes for H/n/phase
                if ($foundH === null && $foundN === null) {
                    $nodes = $xpath->query('//text()');
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

                // Try SBDB fallback when aerith page exists but contains no photometry
                $this->line("Attempting SBDB fallback for {$name} (aerith page had no photometry)...");
                $sb = $this->sbdbFallback($comet->designation ?? null, $name, $client);
                if (is_array($sb)) {
                    $this->info("Found photometry for {$name} via SBDB: H={$sb['H']} n={$sb['n']} phase={$sb['phase']}");
                    $comet->H = $sb['H'] !== null ? floatval($sb['H']) : null;
                    $comet->n = $sb['n'] !== null ? floatval($sb['n']) : null;
                    $comet->phase_coeff = $sb['phase'] !== null ? floatval($sb['phase']) : null;
                    $comet->save();
                } else {
                    // No SBDB result; try extracting H and G from the aerith "Magnitudes" graph
                    // Example appearances: "H = 9.0 and G = 0.15", "H = 9.0, G = 0.15", or "H = 9.0  G = 0.15"
                    if ($foundH === null) {
                        $plain = trim(strip_tags($html));

                        // Try a combined H...G pattern first on stripped text
                        if (preg_match('/H\s*=\s*([+-]?\d+(?:\.\d+)?)[,\s;:\)]*\s*(?:and\s*)?G\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plain, $hg)) {
                            $hval = floatval($hg[1]);
                            $gval = floatval($hg[2]);
                            $this->info("Punting H/G for {$name} from aerith magnitudes: H={$hval} G={$gval}");
                            $comet->H = $hval;
                            $comet->phase_coeff = $gval;
                            $comet->save();
                        } else {
                            // Try to find H and G separately (handles cases where markup separates them)
                            $hval = null;
                            $gval = null;
                            if (preg_match('/\bH\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plain, $mH)) {
                                $hval = floatval($mH[1]);
                            }
                            if (preg_match('/\bG\s*=\s*([+-]?\d+(?:\.\d+)?)/i', $plain, $mG)) {
                                $gval = floatval($mG[1]);
                            }

                            if ($hval !== null) {
                                $this->info("Punting H for {$name} from aerith magnitudes: H={$hval}" . ($gval !== null ? " G={$gval}" : ""));
                                $comet->H = $hval;
                                if ($gval !== null) $comet->phase_coeff = $gval;
                                $comet->save();
                            }
                        }
                    }
                }
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
        // Prefer a compact numeric periodic designation (e.g. "104P") when possible.
        // If a periodic designation is available in the `designation` field
        // or embedded in the `name` (e.g. "153P/Ikeya-Zhang"), extract it
        // and use only that short form in the SBDB `des=` parameter.
        $query = null;

        // Helper: extract a compact periodic designation like '104P' or '153P'
        $extractPeriodic = function (?string $s) {
            if (!$s) return null;
            // Match things like '104P', '153P', or year-based like '1972 E1'/'1972E1'.
            // Capture optional trailing digits after the letter (e.g. 'E1').
            if (preg_match('/(\d{1,4}\s*[A-Za-z]\d*)/i', $s, $m)) {
                // Normalize: remove internal whitespace and uppercase the letter part
                $p = preg_replace('/\s+/', '', $m[1]);
                return strtoupper($p);
            }
            // Also match patterns like '104P/' or '1972E1' at the start
            if (preg_match('/^(\d{1,4}[A-Za-z]\d*)\b/', $s, $m2)) {
                return strtoupper($m2[1]);
            }
            return null;
        };

        // If explicit designation looks like a periodic numeric code, use it
        $periodic = $extractPeriodic($designation);
        if ($periodic !== null) {
            $query = $periodic;
        } else {
            // Otherwise try to extract from the name
            $periodic = $extractPeriodic($name);
            if ($periodic !== null) {
                $query = $periodic;
            }
        }

        // If we still don't have a compact periodic code, fall back to using
        // the provided designation or name verbatim (if any)
        if ($query === null) {
            if ($designation && trim($designation) !== '') {
                $query = trim($designation);
            } elseif ($name && trim($name) !== '') {
                $query = trim($name);
            }
        }

        if (!$query) return null;

        // Build a list of candidate query variants to increase chances of matching
        // objects with non-standard or parenthetical suffixes (e.g. "C/1997 U9 (SOHO)").
        $variants = [];
        $orig = trim($query);
        if ($orig !== '') $variants[] = $orig;

        // Also include the provided name as a candidate if different
        if ($name && trim($name) !== '' && trim($name) !== $orig) {
            $variants[] = trim($name);
        }

        // Strip parenthetical suffixes: "Name (SOHO)" -> "Name"
        $noParen = preg_replace('/\s*\(.*\)\s*/', '', $orig);
        if ($noParen !== '' && $noParen !== $orig) $variants[] = $noParen;

        // Normalize spacing: '1997 U9' and '1997U9'
        $variants[] = preg_replace('/\s+/', ' ', $noParen);
        $variants[] = str_replace(' ', '', $noParen);

        // Add common comet prefixes if absent: 'C/' and 'P/' variants
        if (!preg_match('#^[CP]/#i', $noParen)) {
            $variants[] = 'C/' . $noParen;
            $variants[] = 'P/' . $noParen;
        }

        // If the human-readable name contains parts (e.g. "25D/Neujmin 2"), add tokens
        if ($name) {
            $nm = trim($name);
            // Replace multiple whitespace and normalize slashes/spaces
            $nmNorm = preg_replace('/[\s\/]+/', ' ', $nm);
            $variants[] = $nmNorm;
            $variants[] = str_replace(' ', '', $nmNorm);

            // Add individual meaningful tokens (e.g. 'Neujmin', 'Neujmin 2')
            $parts = preg_split('/[\s\/]+/', $nm);
            if (count($parts) > 1) {
                // try combinations of tail tokens (skip numeric-only tokens)
                $tail = [];
                foreach ($parts as $p) {
                    if (preg_match('/[A-Za-z]/', $p)) {
                        $tail[] = $p;
                    }
                }
                if (!empty($tail)) {
                    $variants[] = implode(' ', $tail);
                    $variants[] = implode('', $tail);
                }
                // Also add individual cleaned tokens (e.g. 'Bradfield') which
                // are useful when the object is indexed under discoverer/surname.
                foreach ($parts as $p) {
                    // strip parentheses and non-alnum characters
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $p);
                    if ($clean === '') continue;
                    // skip purely numeric tokens
                    if (preg_match('/^\d+$/', $clean)) continue;
                    $variants[] = $clean;
                }
            }
        }

        // De-duplicate while preserving order
        $seen = [];
        $cands = [];
        // Ensure the cleaned no-paren designation (e.g. "C/1972 E1") is tried first
        $priority = [];
        $cleanNoParen = trim(preg_replace('/\s*\(.*\)\s*/', '', $orig));
        if ($cleanNoParen !== '' && $cleanNoParen !== $orig) {
            $priority[] = $cleanNoParen;
        }
        foreach ($variants as $v) {
            $v = trim($v);
            if ($v === '') continue;
            if (!empty($priority) && in_array($v, $priority, true)) continue;
            if (!isset($seen[$v])) {
                $seen[$v] = true;
                $cands[] = $v;
            }
        }
        // Prepend priority items
        if (!empty($priority)) {
            $cands = array_merge($priority, $cands);
        }

        // Try 'des=' queries first for each candidate, then fall back to 'sstr=' search if needed.
        $debug = env('AERITH_DEBUG_SBDB', false);
        foreach ($cands as $cq) {
            // Use rawurlencode to encode spaces as %20 (matches browser-style encoding)
            $enc = rawurlencode($cq);
            $url = 'https://ssd-api.jpl.nasa.gov/sbdb.api?des=' . $enc . '&phys-par=1';
            try {
                if ($debug) $this->line("SBDB DES try: {$cq} -> {$url}");
                // Retry transient 5xx (502 etc.) a few times with small backoff
                $res = null;
                $attempt = 0;
                $maxAttempts = 3;
                $body = null;
                while ($attempt < $maxAttempts) {
                    $attempt++;
                    try {
                        $res = $client->get($url, ['headers' => ['User-Agent' => 'laravel-astronomy-library-sbdb/1.0']]);
                        if ($res->getStatusCode() !== 200) {
                            if ($debug) $this->line("SBDB DES {$cq} returned status {$res->getStatusCode()}");
                            // For 5xx, retry; for 4xx, break and treat as permanent
                            $status = $res->getStatusCode();
                            if ($status >= 500 && $status < 600 && $attempt < $maxAttempts) {
                                usleep(300000); // 0.3s
                                continue;
                            }
                            break;
                        }
                        $body = (string) $res->getBody();
                        break;
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $resp = $e->getResponse();
                        $code = $resp ? $resp->getStatusCode() : null;
                        if ($debug) $this->line("SBDB DES {$cq} exception (attempt {$attempt}): " . $e->getMessage());
                        if ($code !== null && $code >= 500 && $attempt < $maxAttempts) {
                            usleep(300000);
                            continue;
                        }
                        // Non-retriable or out of attempts
                        $body = $resp ? (string)$resp->getBody() : null;
                        break;
                    } catch (\Exception $e) {
                        if ($debug) $this->line("SBDB DES {$cq} unexpected exception: " . $e->getMessage());
                        break;
                    }
                }
                if ($body === null) {
                    if ($debug) $this->line("SBDB DES {$cq} produced no body after retries");
                    continue;
                }
                $json = json_decode($body, true);
                if (!is_array($json)) {
                    if ($debug) $this->line("SBDB DES {$cq} returned non-JSON response");
                    continue;
                }

                // Extract physical parameters robustly (handles arrays of phys_par entries)
                $parsed = $this->parseSBDBPhysPar($json);
                $h = $parsed['H'];
                $g = $parsed['G'];
                $nFromSBDB = $parsed['n'];

                if ($debug) {
                    $this->line("SBDB DES {$cq} parsed: H=" . var_export($h, true) . " G=" . var_export($g, true) . " n=" . var_export($nFromSBDB, true));
                }

                if ($h !== null || $nFromSBDB !== null) {
                    // If phys_par didn't supply a slope-like 'n', try orbit.elements
                    if ($nFromSBDB === null && isset($json['orbit']['elements']) && is_array($json['orbit']['elements'])) {
                        foreach ($json['orbit']['elements'] as $el) {
                            if (!is_array($el)) continue;
                            $ename = $el['name'] ?? ($el['label'] ?? null);
                            if ($ename === 'n' || strtolower($ename) === 'n') {
                                $nFromSBDB = $el['value'] ?? $el['val'] ?? $nFromSBDB;
                                break;
                            }
                        }
                    }

                    return ['H' => $h !== null ? floatval($h) : null, 'n' => $nFromSBDB !== null ? floatval($nFromSBDB) : null, 'phase' => $g !== null ? floatval($g) : null, 'source' => 'SBDB', 'query' => $cq];
                }
                // If the SBDB response contains an object/orbit but no phys_par,
                // treat this as a successful lookup (no photometry available).
                if ((isset($json['object']) || isset($json['orbit'])) && $h === null && $nFromSBDB === null) {
                    if ($debug) $this->line("SBDB DES {$cq} found object but no photometry");
                    return ['H' => null, 'n' => null, 'phase' => null, 'source' => 'SBDB', 'query' => $cq];
                }
            } catch (\Exception $e) {
                if ($debug) $this->line("SBDB DES {$cq} exception: " . $e->getMessage());
                // continue to next candidate
                continue;
            }
        }

        // If none of the 'des=' queries matched, try the SBDB search parameter 'sstr' for broader matching.
        foreach ($cands as $cq) {
            // Use rawurlencode for sstr as well
            $enc = rawurlencode($cq);
            $url = 'https://ssd-api.jpl.nasa.gov/sbdb.api?sstr=' . $enc . '&phys-par=1';
            try {
                if ($debug) $this->line("SBDB SSTR try: {$cq} -> {$url}");
                // Retry loop for sstr as well
                if ($debug) $this->line("SBDB SSTR try: {$cq} -> {$url}");
                $res = null;
                $attempt = 0;
                $maxAttempts = 3;
                $body = null;
                while ($attempt < $maxAttempts) {
                    $attempt++;
                    try {
                        $res = $client->get($url, ['headers' => ['User-Agent' => 'laravel-astronomy-library-sbdb/1.0']]);
                        if ($res->getStatusCode() !== 200) {
                            if ($debug) $this->line("SBDB SSTR {$cq} returned status {$res->getStatusCode()}");
                            $status = $res->getStatusCode();
                            if ($status >= 500 && $status < 600 && $attempt < $maxAttempts) {
                                usleep(300000);
                                continue;
                            }
                            break;
                        }
                        $body = (string) $res->getBody();
                        break;
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $resp = $e->getResponse();
                        $code = $resp ? $resp->getStatusCode() : null;
                        if ($debug) $this->line("SBDB SSTR {$cq} exception (attempt {$attempt}): " . $e->getMessage());
                        if ($code !== null && $code >= 500 && $attempt < $maxAttempts) {
                            usleep(300000);
                            continue;
                        }
                        $body = $resp ? (string)$resp->getBody() : null;
                        break;
                    } catch (\Exception $e) {
                        if ($debug) $this->line("SBDB SSTR {$cq} unexpected exception: " . $e->getMessage());
                        break;
                    }
                }
                if ($body === null) {
                    if ($debug) $this->line("SBDB SSTR {$cq} produced no body after retries");
                    continue;
                }
                $json = json_decode($body, true);
                if (!is_array($json)) {
                    if ($debug) $this->line("SBDB SSTR {$cq} returned non-JSON response");
                    continue;
                }

                // Extract physical parameters robustly (handles arrays of phys_par entries)
                $parsed = $this->parseSBDBPhysPar($json);
                $h = $parsed['H'];
                $g = $parsed['G'];
                $nFromSBDB = $parsed['n'];

                if ($debug) {
                    $this->line("SBDB SSTR {$cq} parsed: H=" . var_export($h, true) . " G=" . var_export($g, true) . " n=" . var_export($nFromSBDB, true));
                }

                if ($h !== null || $nFromSBDB !== null) {
                    if ($nFromSBDB === null && isset($json['orbit']['elements']) && is_array($json['orbit']['elements'])) {
                        foreach ($json['orbit']['elements'] as $el) {
                            if (!is_array($el)) continue;
                            $ename = $el['name'] ?? ($el['label'] ?? null);
                            if ($ename === 'n' || strtolower($ename) === 'n') {
                                $nFromSBDB = $el['value'] ?? $el['val'] ?? $nFromSBDB;
                                break;
                            }
                        }
                    }

                    return ['H' => $h !== null ? floatval($h) : null, 'n' => $nFromSBDB !== null ? floatval($nFromSBDB) : null, 'phase' => $g !== null ? floatval($g) : null, 'source' => 'SBDB', 'query' => $cq];
                }
                if ((isset($json['object']) || isset($json['orbit'])) && $h === null && $nFromSBDB === null) {
                    if ($debug) $this->line("SBDB SSTR {$cq} found object but no photometry");
                    return ['H' => null, 'n' => null, 'phase' => null, 'source' => 'SBDB', 'query' => $cq];
                }
            } catch (\Exception $e) {
                if ($debug) $this->line("SBDB SSTR {$cq} exception: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Recursively search decoded SBDB JSON for an H and G value.
     * Returns ['H'=>float,'G'=>float|null] or null when not found.
     */
    private function findHAndG($data)
    {
        if (is_array($data)) {
            // If this level contains direct phys_par keys
            if (isset($data['H']) || isset($data['h']) || isset($data['G']) || isset($data['g'])) {
                $h = $data['H'] ?? ($data['h'] ?? null);
                $g = $data['G'] ?? ($data['g'] ?? null);
                if ($h !== null) return ['H' => $h, 'G' => $g ?? null];
            }

            // Otherwise recurse into children
            foreach ($data as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $found = $this->findHAndG($v);
                    if ($found !== null) return $found;
                }
            }
        } elseif (is_object($data)) {
            // Convert object to array and recurse
            return $this->findHAndG((array) $data);
        }

        return null;
    }

    /**
     * Parse SBDB phys_par structures for common comet/asteroid entries.
     * Handles both associative phys_par maps and arrays of entries with 'name'/'value'.
     * Returns ['H'=>string|null,'G'=>string|null,'n'=>string|null].
     */
    private function parseSBDBPhysPar(array $json): array
    {
        $h = null;
        $g = null;
        $n = null;

        if (isset($json['phys_par'])) {
            $pp = $json['phys_par'];

            // If phys_par is an associative map with keys like H/G
            if (is_array($pp) && array_keys($pp) !== range(0, count($pp) - 1)) {
                if (isset($pp['H'])) $h = $pp['H'];
                if (isset($pp['h'])) $h = $pp['h'];
                if (isset($pp['G'])) $g = $pp['G'];
                if (isset($pp['g'])) $g = $pp['g'];
            }

            // If phys_par is a numeric array of entries like {name, value, title}
            if (is_array($pp) && isset($pp[0]) && is_array($pp[0])) {
                foreach ($pp as $entry) {
                    if (!is_array($entry)) continue;
                    $ename = isset($entry['name']) ? strtoupper(trim((string)$entry['name'])) : null;
                    $title = isset($entry['title']) ? strtolower((string)$entry['title']) : '';
                    $val = $entry['value'] ?? null;

                    if ($ename === 'H' || $ename === 'M1' || stripos($title, 'absolute magnitude') !== false || stripos($title, 'comet total magnitude') !== false) {
                        if ($h === null && $val !== null) $h = $val;
                    }
                    if ($ename === 'G') {
                        if ($g === null && $val !== null) $g = $val;
                    }
                    // K1/K2 in SBDB often represent comet slope parameters (map to `n` as a best-effort)
                    if ($ename === 'K1' || $ename === 'K2' || stripos($title, 'slope') !== false) {
                        if ($n === null && $val !== null) $n = $val;
                    }
                    // Some entries use M2 for nuclear magnitude; prefer M1 for total magnitude
                    if ($ename === 'M2' && $h === null && $val !== null) {
                        $h = $val;
                    }
                }
            }
        }

        // If still nothing, try to find via recursive scan
        if ($h === null) {
            $found = $this->findHAndG($json);
            if ($found !== null) {
                $h = $found['H'];
                if ($g === null) $g = $found['G'];
            }
        }

        return ['H' => $h !== null ? $h : null, 'G' => $g !== null ? $g : null, 'n' => $n !== null ? $n : null];
    }
}
