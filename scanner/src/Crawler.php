<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler
{
    protected Client $client;
    protected array $visited = [];
    protected array $results = [];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; SEOMapper/1.0; +https://yourdomain.dev)'
            ]
        ]);
    }

    public function crawl(string $startUrl, int $maxDepth): void
    {
        $this->crawlRecursive($startUrl, $startUrl, 0, $maxDepth);

        if (!empty($this->results)) {
            file_put_contents('output/crawl.json', json_encode($this->results, JSON_PRETTY_PRINT));
        } else {
            file_put_contents('output/crawl.json', json_encode([]));
        }
    }

    protected function crawlRecursive(string $url, string $baseUrl, int $depth, int $maxDepth): void
    {
        if ($depth > $maxDepth || isset($this->visited[$url])) return;

        $this->visited[$url] = true;
        echo "Crawling: {$url}\n";

        try {
            $res = $this->client->get($url);
            $html = (string) $res->getBody();
            $crawler = new DomCrawler($html);

            if ($depth === 0) {
                file_put_contents('output/homepage.html', $html);
            }

            $this->results[] = [
                'url' => $url,
                'title' => $crawler->filter('title')->count() ? $crawler->filter('title')->text() : '',
                'meta_description' => $crawler->filter('meta[name="description"]')->count() ? $crawler->filter('meta[name="description"]')->attr('content') : '',
                'h1' => $crawler->filter('h1')->count() ? $crawler->filter('h1')->first()->text() : '',
                'canonical' => $crawler->filter('link[rel="canonical"]')->count() ? $crawler->filter('link[rel="canonical"]')->attr('href') : '',
            ];

            $links = $crawler->filter('a')->each(function ($node) {
                $href = $node->attr('href');
                return $href && !str_starts_with($href, '#') ? $href : null;
            });

            $links = array_filter($links);

            foreach ($links as $link) {
                if (str_starts_with($link, 'mailto:') || str_starts_with($link, 'tel:')) continue;

                $absolute = $this->normalizeUrl($link, $url);
                if (!$absolute || isset($this->visited[$absolute])) continue;
                if ($this->isSameDomain($absolute, $baseUrl)) {
                    $this->crawlRecursive($absolute, $baseUrl, $depth + 1, $maxDepth);
                }
            }
        } catch (\Exception $e) {
            echo "Error fetching {$url}: " . $e->getMessage() . "\n";
        }
    }

    protected function normalizeUrl(string $link, string $base): string
    {
        if (!$link || str_starts_with($link, '#')) return '';

        if (str_starts_with($link, 'http')) return $link;

        $baseParts = parse_url($base);
        $scheme = $baseParts['scheme'] ?? 'https';
        $host = $baseParts['host'] ?? '';

        return "{$scheme}://{$host}/" . ltrim($link, '/');
    }

    protected function isSameDomain(string $url, string $base): bool
    {
        $urlHost = preg_replace('/^www\./', '', parse_url($url, PHP_URL_HOST) ?? '');
        $baseHost = preg_replace('/^www\./', '', parse_url($base, PHP_URL_HOST) ?? '');

        return $urlHost === $baseHost;
    }
}