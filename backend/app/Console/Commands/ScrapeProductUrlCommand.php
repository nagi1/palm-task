<?php

namespace App\Console\Commands;

use App\Actions\Products\ScrapeProductUrlAction;
use Illuminate\Console\Command;

class ScrapeProductUrlCommand extends Command
{
    protected $signature = 'products:scrape-url {url?} {--file=}';

    protected $description = 'Scrape and store a product by URL or a list from a file';

    public function __construct(private ScrapeProductUrlAction $scrape)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->hasInput()) {
            $this->error('Provide either a {url} argument or --file=path');

            return self::INVALID;
        }

        if (empty($urls = $this->collectUrls())) {
            $this->warn('No URLs to process after filtering.');

            return self::SUCCESS;
        }

        $results = $this->processAll($urls);
        $this->reportSummary($results, count($urls));

        return $results['failures'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function hasInput(): bool
    {
        return (bool) ($this->argument('url') || $this->option('file'));
    }

    private function collectUrls(): array
    {
        $single = $this->argument('url');
        $file = $this->option('file');

        $urls = [];
        $urls = $this->appendIfNotEmpty($urls, $single);
        $urls = $this->mergeFileUrls($urls, $file);

        // Normalize & de-duplicate
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, fn ($u) => $u !== '');

        return array_values(array_unique($urls));
    }

    private function appendIfNotEmpty(array $urls, ?string $candidate): array
    {
        return $candidate ? [...$urls, $candidate] : $urls;
    }

    private function mergeFileUrls(array $urls, ?string $file): array
    {
        if (! $file) {
            return $urls;
        }
        if (! is_file($file)) {
            $this->error('File not found: '.$file);

            return $urls; // do not abort entire command; user may have also provided a single URL
        }
        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $urls[] = $line; // trimming later in collectUrls
        }

        return $urls;
    }

    private function processAll(array $urls): array
    {
        $success = 0;
        $failures = 0;
        foreach ($urls as $url) {
            $this->line("Scraping: <info>{$url}</info>");
            $ok = $this->processSingle($url);
            $ok ? $success++ : $failures++;
            $this->politenessDelay();
        }

        return compact('success', 'failures');
    }

    private function processSingle(string $url): bool
    {
        try {
            $product = ($this->scrape)($url);
            $this->info('Stored product #'.$product->id.' - '.$product->title);

            return true;
        } catch (\Throwable $e) {
            $this->warn('Failed: '.$url.' -> '.$e->getMessage());

            return false;
        }
    }

    private function politenessDelay(): void
    {
        usleep(250_000); // 250ms
    }

    private function reportSummary(array $results, int $total): void
    {
        $this->newLine();
        $this->info(sprintf('Completed. Success: %d / %d | Failures: %d', $results['success'], $total, $results['failures']));
    }
}
