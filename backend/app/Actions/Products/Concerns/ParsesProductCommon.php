<?php

namespace App\Actions\Products\Concerns;

use Symfony\Component\DomCrawler\Crawler;

trait ParsesProductCommon
{
    private function text(Crawler $crawler, string $selector, string $default = ''): string
    {
        $node = $this->firstNode($crawler, $selector);

        return $this->nodeExists($node)
            ? $this->trimmedText($node, $default)
            : $default;
    }

    private function attr(Crawler $crawler, string $selector, string $attr): ?string
    {
        $node = $this->firstNode($crawler, $selector);
        $value = $this->nodeExists($node) ? $node->attr($attr) : null;

        return $this->emptyToNull($value);
    }

    private function firstNode(Crawler $crawler, string $selector): Crawler
    {
        return $crawler->filter($selector)->first();
    }

    private function nodeExists(?Crawler $node): bool
    {
        return ($node?->count() ?? 0) > 0;
    }

    private function trimmedText(Crawler $node, string $default = ''): string
    {
        return trim($node->text($default));
    }

    private function emptyToNull(?string $value): ?string
    {
        return ($value === null || $value === '') ? null : $value;
    }

    private function normalizePrice(?string $raw): ?float
    {
        $normalized = $this->prepareNumericString($raw);

        return $this->isNumericString($normalized) ? (float) $normalized : null;
    }

    private function prepareNumericString(?string $raw): ?string
    {
        $stripped = $this->stripNonNumeric($raw);
        $nonEmpty = $this->emptyToNull($stripped);

        return $nonEmpty === null
            ? null
            : $this->normalizeDecimalNotation($nonEmpty);
    }

    private function stripNonNumeric(?string $raw): ?string
    {
        return $raw === null
            ? null
            : preg_replace('/[^0-9.,]/', '', $raw);
    }

    private function normalizeDecimalNotation(string $value): string
    {
        return $this->isCommaDecimal($value)
            ? str_replace(',', '.', $value)
            : str_replace(',', '', $value);
    }

    private function isCommaDecimal(string $value): bool
    {
        return substr_count($value, ',') === 1 && substr_count($value, '.') === 0;
    }

    private function isNumericString(?string $value): bool
    {
        return $value !== null && is_numeric($value);
    }
}
