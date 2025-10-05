<?php

namespace App\Actions\Products;

use App\Actions\Products\Concerns\ParsesProductCommon;
use Symfony\Component\DomCrawler\Crawler;

class ParseJumiaProductAction
{
    use ParsesProductCommon;

    public function __invoke(string $html): array
    {
        $crawler = new Crawler($html);
        $title = $this->text($crawler, 'h1.-pbxs');
        $priceText = $this->text($crawler, '.-fs24 .-b');
        $price = $this->normalizePrice($priceText);
        $imageUrl = $this->attr($crawler, 'img.-fw', 'data-src');

        return [
            'title' => $title ?: null,
            'price' => $price,
            'image_url' => $imageUrl,
            'asin' => null,
            'currency' => 'EGP',
            'raw_payload' => [
                'price_text' => $priceText,
            ],
        ];
    }
}
