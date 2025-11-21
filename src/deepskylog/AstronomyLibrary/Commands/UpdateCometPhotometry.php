<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateCometPhotometry extends Command
{
    protected $signature = 'astronomy:updateCometPhotometry';

    protected $description = 'Fetch comet photometry (H, n, phase) from external catalogs (attempt Seiichi Yoshida).';

    public function handle()
    {
        $this->info('Updating comet photometry from Seiichi Yoshida (aerith.net)');
        $client = new Client(['timeout' => 10]);

        $comets = CometsOrbitalElements::all();
        foreach ($comets as $comet) {
            $name = $comet->name;
            // Try to map name to Yoshida page slug: basic approach: replace spaces with '' and remove punctuation
            $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
            $url = "https://www.aerith.net/comet/catalog/{$slug}.html";

            try {
                $res = $client->get($url);
                $html = (string) $res->getBody();

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
                    $this->info("Found photometry for {$name}: H={$foundH} n={$foundN} phase={$foundPhase}");
                    $comet->H = $foundH;
                    $comet->n = $foundN;
                    $comet->phase_coeff = $foundPhase;
                    $comet->save();
                } else {
                    $this->line("No photometry found for {$name} at {$url}");
                }
            } catch (\Exception $e) {
                $this->line("Failed to fetch {$url}: ".$e->getMessage());
            }
        }

        $this->info('Finished updating comet photometry.');
    }
}
