<?php

namespace App\Actions\Products;

use App\Enums\ProductSource;

class DetermineSourceAction
{
    public function __invoke(string $url): ProductSource
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        return match (true) {
            str_contains($host, 'amazon.') => ProductSource::Amazon,
            str_contains($host, 'jumia.') => ProductSource::Jumia,
            default => throw new \Exception('Unknown product source for URL: '.$url),
        };
    }
}
