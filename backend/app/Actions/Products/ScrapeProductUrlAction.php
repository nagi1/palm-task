<?php

namespace App\Actions\Products;

use App\Enums\ProductSource;
use App\Models\Product;

class ScrapeProductUrlAction
{
    public function __construct(
        private FetchHtmlAction $fetchHtml,
        private DetermineSourceAction $determineSource,
        private ParseAmazonProductAction $parseAmazon,
        private ParseJumiaProductAction $parseJumia,
        private StoreProductAction $storeProduct,
    ) {}

    public function __invoke(string $url): Product
    {
        $source = ($this->determineSource)($url);
        $html = ($this->fetchHtml)($url);

        $parsed = match ($source) {
            ProductSource::Amazon => ($this->parseAmazon)($html),
            ProductSource::Jumia => ($this->parseJumia)($html),
            default => ['title' => 'Unknown', 'price' => null, 'image_url' => null, 'asin' => null, 'currency' => null, 'raw_payload' => null],
        };

        $parsed['source'] = $source->value;
        $parsed['url'] = $url;

        return ($this->storeProduct)($parsed);
    }
}
